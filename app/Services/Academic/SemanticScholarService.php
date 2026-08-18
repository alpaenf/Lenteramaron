<?php

namespace App\Services\Academic;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemanticScholarService
{
    protected string $baseUrl;
    protected int $timeout;
    protected int $limit;

    public function __construct()
    {
        $this->baseUrl = config('litera.apis.semantic_scholar.base_url', 'https://api.semanticscholar.org/graph/v1');
        $this->timeout = config('litera.apis.semantic_scholar.timeout', 10);
        $this->limit = config('litera.apis.semantic_scholar.limit', 10);
    }

    /**
     * Search papers via Semantic Scholar Graph API.
     */
    public function search(string $query, int $limit = 10): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => 'LITERA-LibraryNavigator/1.0 (mailto:admin@sdn02maron.sch.id)',
                    'Accept'     => 'application/json',
                ])
                ->get("{$this->baseUrl}/paper/search", [
                    'query'  => $query,
                    'limit'  => min($limit, 20),
                    'fields' => 'paperId,title,abstract,authors,year,venue,citationCount,isOpenAccess,openAccessPdf,url,externalIds',
                ]);

            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                return array_map([$this, 'formatPaper'], $data);
            } else {
                Log::warning("SemanticScholar HTTP Error: Status " . $response->status() . " Body: " . substr($response->body(), 0, 200));
            }
        } catch (\Throwable $e) {
            Log::warning("SemanticScholar Service Error: " . $e->getMessage());
        }

        return [];
    }

    protected function formatPaper(array $paper): array
    {
        $authors = [];
        if (isset($paper['authors']) && is_array($paper['authors'])) {
            foreach ($paper['authors'] as $auth) {
                if (isset($auth['name'])) {
                    $authors[] = $auth['name'];
                }
            }
        }

        $doi = $paper['externalIds']['DOI'] ?? null;
        $pdfUrl = $paper['openAccessPdf']['url'] ?? null;

        return [
            'external_id'          => $paper['paperId'] ?? uniqid('s2_'),
            'source_provider'      => 'semantic_scholar',
            'title'                => $paper['title'] ?? 'Tanpa Judul',
            'authors'              => $authors,
            'publication_year'     => $paper['year'] ?? null,
            'publisher_or_journal' => $paper['venue'] ?? null,
            'doi'                  => $doi,
            'url'                  => $paper['url'] ?? ($doi ? "https://doi.org/{$doi}" : null),
            'pdf_url'              => $pdfUrl,
            'abstract'             => $paper['abstract'] ?? null,
            'citation_count'       => $paper['citationCount'] ?? 0,
            'open_access'          => $paper['isOpenAccess'] ?? false,
        ];
    }
}
