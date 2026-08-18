<?php

namespace App\Services\Academic;

use App\Models\ExternalSource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AcademicAggregatorService
{
    protected OpenAlexService $openAlex;
    protected SemanticScholarService $semanticScholar;

    public function __construct(OpenAlexService $openAlex, SemanticScholarService $semanticScholar)
    {
        $this->openAlex = $openAlex;
        $this->semanticScholar = $semanticScholar;
    }

    /**
     * Search academic papers with cache and graceful fallback.
     */
    public function search(string $query, int $limit = 10): array
    {
        $cacheKey = 'litera_academic_' . md5(strtolower(trim($query)));
        $ttlHours = config('litera.cache.search_ttl_hours', 24);

        return Cache::remember($cacheKey, now()->addHours($ttlHours), function () use ($query, $limit) {
            $results = [];

            // 1. Fetch from OpenAlex
            $openAlexResults = $this->openAlex->search($query, $limit);
            foreach ($openAlexResults as $item) {
                $results[] = $this->persistExternalSource($item);
            }

            // 2. Fetch from Semantic Scholar if needed
            if (count($results) < $limit) {
                $s2Results = $this->semanticScholar->search($query, $limit - count($results));
                foreach ($s2Results as $item) {
                    $results[] = $this->persistExternalSource($item);
                }
            }

            return $results;
        });
    }

    /**
     * Persist or update item in `external_sources` table and return formatted array.
     */
    protected function persistExternalSource(array $data): array
    {
        try {
            $record = ExternalSource::updateOrCreate(
                [
                    'external_id'     => $data['external_id'],
                    'source_provider' => $data['source_provider'],
                ],
                [
                    'title'                => $data['title'],
                    'authors'              => $data['authors'] ?? [],
                    'publication_year'     => $data['publication_year'],
                    'publisher_or_journal' => $data['publisher_or_journal'],
                    'doi'                  => $data['doi'],
                    'url'                  => $data['url'],
                    'pdf_url'              => $data['pdf_url'],
                    'abstract'             => $data['abstract'],
                    'citation_count'       => $data['citation_count'] ?? 0,
                    'open_access'          => $data['open_access'] ?? false,
                ]
            );

            return array_merge($data, ['db_id' => $record->id]);
        } catch (\Throwable $e) {
            Log::warning("Persist External Source Exception: " . $e->getMessage());
            return array_merge($data, ['db_id' => null]);
        }
    }
}
