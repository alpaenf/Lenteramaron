<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('litera.llm.api_key');
        $this->baseUrl = config('litera.llm.base_url', 'https://generativelanguage.googleapis.com/v1beta/openai/');
        $this->model = config('litera.llm.model', 'gemini-1.5-flash');
        $this->timeout = config('litera.llm.timeout', 20);
    }

    protected function getEndpointUrl(): string
    {
        return rtrim($this->baseUrl, '/') . '/chat/completions';
    }

    /**
     * Helper to clean JSON string from LLM responses (stripping markdown code blocks if any).
     */
    protected function parseJsonFromContent(string $content): ?array
    {
        $content = trim($content);
        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
        }
        $decoded = json_decode(trim($content), true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Expand query into search terms and topic concepts.
     */
    public function expandQuery(string $query): array
    {
        if (empty($this->apiKey)) {
            return $this->fallbackExpandQuery($query);
        }

        try {
            $prompt = "System: Anda adalah asisten akademik perpustakaan LITERA. Analisis pencarian pengguna dan berikan hasil JSON murni tanpa markdown.\n"
                . "Query Pengguna: \"{$query}\"\n"
                . "Format JSON:\n"
                . "{\n"
                . "  \"keywords\": [\"kata kunci 1\", \"kata kunci 2\"],\n"
                . "  \"english_terms\": [\"term 1\", \"term 2\"],\n"
                . "  \"main_topic\": \"Topik Utama\",\n"
                . "  \"intent\": \"Penjelasan singkat maksud pencarian\"\n"
                . "}";

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post($this->getEndpointUrl(), [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.2,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content') ?? '';
                $decoded = $this->parseJsonFromContent($content);
                if (is_array($decoded)) {
                    return array_merge([
                        'keywords' => [$query],
                        'english_terms' => [$query],
                        'main_topic' => $query,
                        'intent' => 'Pencarian penelitian topik ' . $query
                    ], $decoded);
                }
            } else {
                Log::warning("LLM API Expand Query Error Response: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::warning("LLM API Expand Query Warning: " . $e->getMessage());
        }

        return $this->fallbackExpandQuery($query);
    }

    /**
     * Explain why a specific source is relevant to the user query.
     */
    public function explainRelevance(string $query, array $item): string
    {
        $title = $item['title'] ?? 'Tanpa Judul';
        $abstract = $item['abstract'] ?? ($item['description'] ?? '');
        $type = $item['source_type'] ?? 'buku/paper';

        if (empty($this->apiKey)) {
            return $this->fallbackExplainRelevance($query, $title, $type);
        }

        try {
            $prompt = "Berikan 2 kalimat penjelasan dalam bahasa Indonesia mengapa sumber berikut relevan dengan pencarian pengguna.\n"
                . "Pencarian: \"{$query}\"\n"
                . "Judul Sumber: \"{$title}\"\n"
                . "Ringkasan/Deskripsi: \"{$abstract}\"\n"
                . "Tipe Sumber: {$type}\n"
                . "Aturan: Jangan mengarang fakta. Fokus pada hubungan topik.";

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post($this->getEndpointUrl(), [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.3,
                ]);

            if ($response->successful()) {
                $explanation = trim($response->json('choices.0.message.content') ?? '');
                if (!empty($explanation)) {
                    return $explanation;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("LLM API Explain Relevance Warning: " . $e->getMessage());
        }

        return $this->fallbackExplainRelevance($query, $title, $type);
    }

    /**
     * Generate 5-step Research Path / Exploration Progression.
     */
    public function generateResearchPath(string $query, array $sources): array
    {
        if (empty($this->apiKey) || count($sources) === 0) {
            return $this->fallbackResearchPath($query, $sources);
        }

        try {
            $sourcesSummary = [];
            foreach (array_slice($sources, 0, 8) as $idx => $s) {
                $sourcesSummary[] = [
                    'id' => $idx + 1,
                    'title' => $s['title'],
                    'type' => $s['source_type'] === 'local' ? 'Buku Koleksi Perpustakaan' : 'Jurnal/Paper Eksternal',
                    'year' => $s['publication_year'] ?? 'N/A',
                ];
            }

            $prompt = "Buatkan Research Path (5 langkah eksplorasi) untuk topik pencarian: \"{$query}\".\n"
                . "Gunakan daftar sumber yang tersedia berikut:\n" . json_encode($sourcesSummary, JSON_UNESCAPED_UNICODE) . "\n\n"
                . "Kembalikan JSON murni dalam format:\n"
                . "{\n"
                . "  \"steps\": [\n"
                . "    {\n"
                . "      \"step\": 1,\n"
                . "      \"title\": \"Judul Tahap (cth: 01 — Dasar Konsep)\",\n"
                . "      \"description\": \"Penjelasan singkat fokus tahap ini\",\n"
                . "      \"recommended_source_title\": \"Judul sumber terbaik dari daftar di atas atau rekomendasi bacaan\"\n"
                . "    }\n"
                . "  ]\n"
                . "}";

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post($this->getEndpointUrl(), [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.3,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content') ?? '';
                $decoded = $this->parseJsonFromContent($content);
                if (isset($decoded['steps']) && is_array($decoded['steps'])) {
                    return $decoded['steps'];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("LLM API Research Path Warning: " . $e->getMessage());
        }

        return $this->fallbackResearchPath($query, $sources);
    }

    /* Fallback functions */

    protected function fallbackExpandQuery(string $query): array
    {
        $words = array_filter(explode(' ', strtolower($query)));
        return [
            'keywords' => array_values($words),
            'english_terms' => [$query],
            'main_topic' => ucfirst($query),
            'intent' => "Eksplorasi sumber literatur mengenai " . $query,
        ];
    }

    protected function fallbackExplainRelevance(string $query, string $title, string $type): string
    {
        if ($type === 'local') {
            return "Sumber ini relevan karena merupakan koleksi buku perpustakaan lokal yang menyediakan dasar teori terkait kata kunci \"{$query}\".";
        }
        return "Sumber ilmiah eksternal ini relevan karena membahas studi terbaru yang beririsan dengan topik \"{$query}\".";
    }

    protected function fallbackResearchPath(string $query, array $sources): array
    {
        $localSources = array_filter($sources, fn($s) => ($s['source_type'] ?? '') === 'local');
        $externalSources = array_filter($sources, fn($s) => ($s['source_type'] ?? '') === 'external');

        $localTitle = count($localSources) > 0 ? reset($localSources)['title'] : 'Buku dasar literatur terkait';
        $externalTitle = count($externalSources) > 0 ? reset($externalSources)['title'] : 'Paper penelitian terbaru';

        return [
            [
                'step' => 1,
                'title' => '01 — Dasar Konsep',
                'description' => 'Memahami fondasi awal dan terminologi dasar mengenai ' . $query,
                'recommended_source_title' => $localTitle,
            ],
            [
                'step' => 2,
                'title' => '02 — Penelitian Terdahulu',
                'description' => 'Mempelajari kerangka pemikiran dan studi terdahulu yang relevan.',
                'recommended_source_title' => 'Koleksi Buku Referensi Perpustakaan',
            ],
            [
                'step' => 3,
                'title' => '03 — Penelitian Terbaru',
                'description' => 'Mengamati perkembangan artikel ilmiah dan jurnal mutakhir.',
                'recommended_source_title' => $externalTitle,
            ],
            [
                'step' => 4,
                'title' => '04 — Topik Spesifik',
                'description' => 'Menganalisis hasil temuan spesifik dan metode penelitian.',
                'recommended_source_title' => 'Artikel Ilmiah Terkait',
            ],
            [
                'step' => 5,
                'title' => '05 — Research Gap & Arah Lanjutan',
                'description' => 'Menentukan celah penelitian serta implikasi praktis untuk kajian mendatang.',
                'recommended_source_title' => 'Sintesis Literatur LITERA',
            ],
        ];
    }
}
