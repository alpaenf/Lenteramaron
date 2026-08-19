<?php

namespace App\Services\Search;

use App\Models\Book;
use App\Models\SearchQuery;
use App\Services\Academic\AcademicAggregatorService;
use App\Services\GroqService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HybridSearchService
{
    protected GroqService $groqService;
    protected AcademicAggregatorService $academicAggregator;

    public function __construct(GroqService $groqService, AcademicAggregatorService $academicAggregator)
    {
        $this->groqService = $groqService;
        $this->academicAggregator = $academicAggregator;
    }

    /**
     * Perform Hybrid Search across local books & external papers.
     */
    public function search(string $query, array $filters = []): array
    {
        $query = trim($query);
        if (empty($query)) {
            return [
                'query' => '',
                'intent' => null,
                'total_results' => 0,
                'items' => [],
            ];
        }

        $cacheKey = 'litera_search_' . md5(strtolower($query) . '_' . json_encode($filters));

        $result = Cache::remember($cacheKey, now()->addHours(24), function () use ($query, $filters) {
            // 1. Groq Query Expansion & Understanding
            $expanded = $this->groqService->expandQuery($query);

            // 2. Fetch Local Library Books
            $localItems = $this->searchLocalBooks($query, $expanded);

            // 3. Fetch External Academic Sources
            $externalItems = $this->searchExternalSources($query, $expanded);

            // 4. Merge, Score, and Rank All Items
            $allScoredItems = $this->scoreAndRankItems($query, $expanded, $localItems, $externalItems);

            // Filter by source_type filter if specified
            if (!empty($filters['source_type'])) {
                if ($filters['source_type'] === 'local') {
                    $allScoredItems = array_values(array_filter($allScoredItems, fn($i) => $i['source_type'] === 'local'));
                } elseif ($filters['source_type'] === 'external') {
                    $allScoredItems = array_values(array_filter($allScoredItems, fn($i) => $i['source_type'] === 'external'));
                }
            }

            // Filter by year_range filter if specified
            if (!empty($filters['year_range']) && $filters['year_range'] !== 'all') {
                $currentYear = (int) date('Y');
                $minYear = match ($filters['year_range']) {
                    '3_years'  => $currentYear - 3,
                    '5_years'  => $currentYear - 5,
                    '10_years' => $currentYear - 10,
                    default    => 0,
                };

                if ($minYear > 0) {
                    $allScoredItems = array_values(array_filter($allScoredItems, function ($item) use ($minYear) {
                        $year = (int) ($item['publication_year'] ?? 0);
                        return $year === 0 || $year >= $minYear;
                    }));
                }
            }

            return [
                'query'         => $query,
                'intent'        => $expanded['intent'] ?? null,
                'main_topic'    => $expanded['main_topic'] ?? $query,
                'total_results' => count($allScoredItems),
                'items'         => $allScoredItems,
            ];
        });

        // Log search query for analytics
        try {
            SearchQuery::create([
                'user_id'          => Auth::id(),
                'query_text'       => $query,
                'normalized_query' => strtolower($query),
                'filters'          => $filters,
                'results_count'    => $result['total_results'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            // Ignore log error
        }

        return $result;
    }

    protected function searchLocalBooks(string $query, array $expanded): array
    {
        $keywords = array_unique(array_merge([$query], $expanded['keywords'] ?? []));

        $booksQuery = Book::with(['category']);

        $booksQuery->where(function ($q) use ($keywords, $query) {
            $q->where('title', 'LIKE', "%{$query}%")
              ->orWhere('author', 'LIKE', "%{$query}%")
              ->orWhere('description', 'LIKE', "%{$query}%")
              ->orWhere('isbn', 'LIKE', "%{$query}%");

            foreach ($keywords as $kw) {
                if (strlen($kw) > 2) {
                    $q->orWhere('title', 'LIKE', "%{$kw}%")
                      ->orWhere('author', 'LIKE', "%{$kw}%")
                      ->orWhere('description', 'LIKE', "%{$kw}%");
                }
            }
        });

        $books = $booksQuery->take(15)->get();

        $items = [];
        foreach ($books as $book) {
            $items[] = [
                'id'                   => 'local_' . $book->id,
                'db_id'                => $book->id,
                'source_type'          => 'local',
                'title'                => $book->title,
                'author'               => $book->author,
                'authors'              => array_filter(array_map('trim', explode(',', $book->author ?? ''))),
                'publisher_or_journal' => $book->publisher,
                'publication_year'     => $book->year ?? $book->publication_year,
                'isbn'                 => $book->isbn,
                'category_name'        => $book->category ? $book->category->name : 'Umum',
                'rack_name'            => $book->shelf ?? '-',
                'stock'                => $book->stock,
                'cover_image'          => $book->cover,
                'abstract'             => $book->description,
                'url'                  => null,
                'pdf_url'              => null,
                'citation_count'       => 0,
                'open_access'          => true,
            ];
        }

        return $items;
    }

    protected function searchExternalSources(string $query, array $expanded): array
    {
        $englishTerms   = $expanded['english_terms'] ?? [];
        $expandedKeywords = $expanded['keywords'] ?? [];

        // Build a priority list of search terms to try
        $searchTerms = [];

        // 1. Best english translation first
        if (!empty($englishTerms[0])) {
            $searchTerms[] = $englishTerms[0];
        }

        // 2. Original query
        $searchTerms[] = $query;

        // 3. Second english term if different
        if (!empty($englishTerms[1]) && $englishTerms[1] !== ($englishTerms[0] ?? '')) {
            $searchTerms[] = $englishTerms[1];
        }

        // 4. Top expanded keywords joined
        if (!empty($expandedKeywords)) {
            $kwPhrase = implode(' ', array_slice($expandedKeywords, 0, 3));
            if (!in_array($kwPhrase, $searchTerms)) {
                $searchTerms[] = $kwPhrase;
            }
        }

        // Deduplicate and clean: max 10 words per term
        $seen = [];
        $cleanTerms = [];
        foreach ($searchTerms as $term) {
            $term = trim($term);
            $slug = strtolower($term);
            if (empty($term) || isset($seen[$slug])) continue;
            $seen[$slug] = true;
            $words = explode(' ', $term);
            $cleanTerms[] = count($words) > 10 ? implode(' ', array_slice($words, 0, 10)) : $term;
        }

        $results = [];
        foreach ($cleanTerms as $searchTerm) {
            if (count($results) >= 15) break;

            $fetched = $this->academicAggregator->search($searchTerm, 20);
            foreach ($fetched as $item) {
                // Avoid duplicate titles
                $titleKey = strtolower(trim($item['title'] ?? ''));
                if (!isset($seen['title_' . $titleKey])) {
                    $seen['title_' . $titleKey] = true;
                    $results[] = $item;
                }
            }
        }

        $items = [];
        foreach ($results as $res) {
            $items[] = [
                'id'                   => 'ext_' . ($res['db_id'] ?? uniqid()),
                'db_id'                => $res['db_id'] ?? null,
                'external_id'          => $res['external_id'],
                'source_type'          => 'external',
                'source_provider'      => $res['source_provider'] ?? 'openalex',
                'title'                => $res['title'],
                'author'               => implode(', ', $res['authors'] ?? []),
                'authors'              => $res['authors'] ?? [],
                'publisher_or_journal' => $res['publisher_or_journal'],
                'publication_year'     => $res['publication_year'],
                'doi'                  => $res['doi'],
                'url'                  => $res['url'],
                'pdf_url'              => $res['pdf_url'],
                'abstract'             => $res['abstract'],
                'citation_count'       => $res['citation_count'] ?? 0,
                'open_access'          => $res['open_access'] ?? false,
            ];
        }

        return $items;
    }

    protected function scoreAndRankItems(string $query, array $expanded, array $localItems, array $externalItems): array
    {
        $all = array_merge($localItems, $externalItems);
        $w = config('litera.search_weights', [
            'semantic' => 0.40,
            'keyword'  => 0.30,
            'recency'  => 0.15,
            'citation' => 0.15,
        ]);

        $queryWords = array_map('strtolower', array_filter(explode(' ', $query)));
        $currentYear = (int) date('Y');

        foreach ($all as &$item) {
            $text = strtolower(($item['title'] ?? '') . ' ' . ($item['abstract'] ?? '') . ' ' . ($item['author'] ?? ''));
            
            // 1. Keyword score (0 to 1)
            $matchedWords = 0;
            foreach ($queryWords as $qw) {
                if (strlen($qw) > 2 && str_contains($text, $qw)) {
                    $matchedWords++;
                }
            }
            $keywordScore = count($queryWords) > 0 ? ($matchedWords / count($queryWords)) : 0.5;

            // 2. Semantic score heuristic (0 to 1)
            $expandedKeywords = $expanded['keywords'] ?? [];
            $semMatches = 0;
            foreach ($expandedKeywords as $ek) {
                if (!empty($ek) && str_contains($text, strtolower($ek))) {
                    $semMatches++;
                }
            }
            $semScore = count($expandedKeywords) > 0 ? min(1.0, $semMatches / count($expandedKeywords)) : $keywordScore;

            // 3. Recency score (0 to 1)
            $year = $item['publication_year'] ?? 2015;
            $age = max(0, $currentYear - (int)$year);
            $recencyScore = max(0.2, 1.0 - ($age * 0.05));

            // 4. Citation score (0 to 1)
            $citations = $item['citation_count'] ?? 0;
            $citationScore = min(1.0, log10($citations + 1) / 3.0);
            if ($item['source_type'] === 'local') {
                $citationScore = 0.8; // Give local books a solid foundation boost
            }

            // Total Score
            $totalScore = ($semScore * $w['semantic'])
                        + ($keywordScore * $w['keyword'])
                        + ($recencyScore * $w['recency'])
                        + ($citationScore * $w['citation']);

            // Percentage (formatted 0-100)
            $relevancePercent = (int) round(min(99, max(45, $totalScore * 100)));

            $item['relevance_score'] = round($totalScore, 3);
            $item['relevance_percent'] = $relevancePercent;
        }
        unset($item);

        // Sort descending by relevance_score
        usort($all, fn($a, $b) => $b['relevance_score'] <=> $a['relevance_score']);

        // Generate relevance explanations for top items
        foreach ($all as $idx => &$item) {
            if ($idx < 5) {
                $item['why_relevant'] = $this->groqService->explainRelevance($query, $item);
            } else {
                $item['why_relevant'] = $item['source_type'] === 'local'
                    ? "Koleksi perpustakaan ini relevan dengan topik \"{$query}\"."
                    : "Jurnal ilmiah ini relevan dengan topik \"{$query}\".";
            }
        }

        return $all;
    }
}
