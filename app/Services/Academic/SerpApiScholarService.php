<?php

namespace App\Services\Academic;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SerpApiScholarService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.serpapi.base_url', 'https://serpapi.com/search');
        $this->apiKey  = config('services.serpapi.api_key', env('SERPAPI_KEY', env('SERP_API_KEY')));
        $this->timeout = config('services.serpapi.timeout', 12);
    }

    /**
     * Search Google Scholar papers via SerpApi.
     */
    public function search(string $query, int $limit = 10): array
    {
        if (empty($this->apiKey)) {
            Log::info("SerpApiScholarService: SERPAPI_KEY is not configured in .env, skipping SerpApi Google Scholar call.");
            return [];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl, [
                    'engine'  => 'google_scholar',
                    'q'       => $query,
                    'api_key' => $this->apiKey,
                    'num'     => min($limit, 20),
                    'hl'      => 'id',
                ]);

            if ($response->successful()) {
                $organic = $response->json('organic_results') ?? [];
                return array_map([$this, 'formatPaper'], $organic);
            } else {
                Log::warning("SerpApi Google Scholar HTTP Error: Status " . $response->status() . " Body: " . substr($response->body(), 0, 200));
            }
        } catch (\Throwable $e) {
            Log::warning("SerpApi Google Scholar Exception: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Format SerpApi organic_result into LITERA standard paper array.
     */
    protected function formatPaper(array $paper): array
    {
        // Extract authors & publisher/year info
        $authors = [];
        $publisherOrJournal = null;
        $year = null;

        if (isset($paper['publication_info']['authors']) && is_array($paper['publication_info']['authors'])) {
            foreach ($paper['publication_info']['authors'] as $auth) {
                if (isset($auth['name'])) {
                    $authors[] = $auth['name'];
                }
            }
        }

        if (isset($paper['publication_info']['summary'])) {
            $summary = $paper['publication_info']['summary'];
            
            // Extract year if available (4 digits like 2021)
            if (preg_match('/\b(19\d\d|20\d\d)\b/', $summary, $matches)) {
                $year = (int) $matches[1];
            }

            // Clean summary for journal name
            $parts = explode('-', $summary);
            if (count($parts) >= 2) {
                $publisherOrJournal = trim(end($parts));
            } else {
                $publisherOrJournal = trim($summary);
            }
        }

        // Extract PDF URL if available
        $pdfUrl = null;
        if (isset($paper['resources']) && is_array($paper['resources'])) {
            foreach ($paper['resources'] as $res) {
                if (isset($res['link']) && (str_contains(strtolower($res['link']), '.pdf') || str_contains(strtolower($res['title'] ?? ''), 'pdf'))) {
                    $pdfUrl = $res['link'];
                    break;
                }
            }
        }

        // Citation count
        $citationCount = $paper['inline_links']['cited_by']['total'] ?? 0;

        return [
            'external_id'          => $paper['result_id'] ?? ('serp_' . uniqid()),
            'source_provider'      => 'google_scholar',
            'title'                => $paper['title'] ?? 'Tanpa Judul',
            'authors'              => $authors,
            'publication_year'     => $year,
            'publisher_or_journal' => $publisherOrJournal,
            'doi'                  => null,
            'url'                  => $paper['link'] ?? null,
            'pdf_url'              => $pdfUrl,
            'abstract'             => $paper['snippet'] ?? null,
            'citation_count'       => (int) $citationCount,
            'open_access'          => !empty($pdfUrl),
        ];
    }
}
