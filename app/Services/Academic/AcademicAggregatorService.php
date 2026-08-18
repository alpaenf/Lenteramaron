<?php

namespace App\Services\Academic;

use App\Models\ExternalSource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AcademicAggregatorService
{
    protected OpenAlexService $openAlex;
    protected SemanticScholarService $semanticScholar;
    protected SerpApiScholarService $serpApiScholar;

    public function __construct(
        OpenAlexService $openAlex,
        SemanticScholarService $semanticScholar,
        SerpApiScholarService $serpApiScholar
    ) {
        $this->openAlex = $openAlex;
        $this->semanticScholar = $semanticScholar;
        $this->serpApiScholar = $serpApiScholar;
    }

    /**
     * Search academic papers with cache and graceful fallback.
     */
    public function search(string $query, int $limit = 20): array
    {
        $cacheKey = 'litera_academic_' . md5(strtolower(trim($query)));
        $ttl = now()->addHours(2);

        $cached = Cache::get($cacheKey);
        if ($cached !== null && count($cached) > 0) {
            return $cached;
        }

        $eachLimit = max(4, (int) ceil($limit / 3));

        // 1. Fetch from SerpApi Google Scholar (if API key available)
        $serpRaw = $this->serpApiScholar->search($query, $eachLimit);

        // 2. Fetch from OpenAlex
        $openAlexRaw = $this->openAlex->search($query, $eachLimit);
        
        // 3. Fetch from Semantic Scholar
        $s2Raw = $this->semanticScholar->search($query, $eachLimit);

        // 4. Persist and format
        $serpResults = [];
        foreach ($serpRaw as $item) {
            $serpResults[] = $this->persistExternalSource($item);
        }

        $openAlexResults = [];
        foreach ($openAlexRaw as $item) {
            $openAlexResults[] = $this->persistExternalSource($item);
        }

        $s2Results = [];
        foreach ($s2Raw as $item) {
            $s2Results[] = $this->persistExternalSource($item);
        }

        // 5. Interleave results across Google Scholar, OpenAlex, Semantic Scholar
        $results = [];
        $maxCount = max(count($serpResults), count($openAlexResults), count($s2Results));
        for ($i = 0; $i < $maxCount; $i++) {
            if (isset($serpResults[$i])) {
                $results[] = $serpResults[$i];
            }
            if (isset($openAlexResults[$i])) {
                $results[] = $openAlexResults[$i];
            }
            if (isset($s2Results[$i])) {
                $results[] = $s2Results[$i];
            }
        }

        if (count($results) > 0) {
            Cache::put($cacheKey, $results, $ttl);
        }

        return $results;
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
