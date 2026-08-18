<?php

namespace App\Services\Academic;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAlexService
{
    protected string $baseUrl;
    protected int $timeout;
    protected int $perPage;

    public function __construct()
    {
        $this->baseUrl = config('litera.apis.openalex.base_url', 'https://api.openalex.org');
        $this->timeout = config('litera.apis.openalex.timeout', 10);
        $this->perPage = config('litera.apis.openalex.per_page', 10);
    }

    /**
     * Search papers via OpenAlex REST API.
     */
    public function search(string $query, int $limit = 10): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => 'LITERA-LibraryNavigator/1.0 (mailto:admin@sdn02maron.sch.id)',
                ])
                ->get("{$this->baseUrl}/works", [
                    'search'   => $query,
                    'per_page' => min($limit, $this->perPage),
                    'select'   => 'id,doi,title,display_name,publication_year,cited_by_count,primary_location,authorships,abstract_inverted_index,open_access',
                ]);

            if ($response->successful()) {
                $results = $response->json('results') ?? [];
                return array_map([$this, 'formatWork'], $results);
            }
        } catch (\Throwable $e) {
            Log::warning("OpenAlex Service Error: " . $e->getMessage());
        }

        return [];
    }

    protected function formatWork(array $work): array
    {
        $authors = [];
        if (isset($work['authorships']) && is_array($work['authorships'])) {
            foreach (array_slice($work['authorships'], 0, 5) as $auth) {
                if (isset($auth['author']['display_name'])) {
                    $authors[] = $auth['author']['display_name'];
                }
            }
        }

        // Reconstruct abstract from inverted index if present
        $abstract = null;
        if (isset($work['abstract_inverted_index']) && is_array($work['abstract_inverted_index'])) {
            $words = [];
            foreach ($work['abstract_inverted_index'] as $word => $positions) {
                foreach ($positions as $pos) {
                    $words[$pos] = $word;
                }
            }
            ksort($words);
            $abstract = implode(' ', array_slice($words, 0, 100)) . (count($words) > 100 ? '...' : '');
        }

        $pdfUrl = $work['primary_location']['pdf_url'] ?? null;
        $landingUrl = $work['primary_location']['landing_page_url'] ?? ($work['doi'] ?? null);

        return [
            'external_id'          => $work['id'] ?? uniqid('oa_'),
            'source_provider'      => 'openalex',
            'title'                => $work['display_name'] ?? ($work['title'] ?? 'Tanpa Judul'),
            'authors'              => $authors,
            'publication_year'     => $work['publication_year'] ?? null,
            'publisher_or_journal' => $work['primary_location']['source']['display_name'] ?? null,
            'doi'                  => $work['doi'] ?? null,
            'url'                  => $landingUrl,
            'pdf_url'              => $pdfUrl,
            'abstract'             => $abstract,
            'citation_count'       => $work['cited_by_count'] ?? 0,
            'open_access'          => $work['open_access']['is_oa'] ?? false,
        ];
    }
}
