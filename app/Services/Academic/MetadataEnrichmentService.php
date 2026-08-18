<?php

namespace App\Services\Academic;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetadataEnrichmentService
{
    /**
     * Fetch metadata by ISBN from Google Books API or Open Library API.
     */
    public function enrichByIsbn(string $isbn): ?array
    {
        $cleanIsbn = preg_replace('/[^0-9X]/i', '', $isbn);

        if (empty($cleanIsbn)) {
            return null;
        }

        // Try Google Books API first
        $googleData = $this->fetchFromGoogleBooks($cleanIsbn);
        if ($googleData) {
            return $googleData;
        }

        // Fallback to Open Library API
        return $this->fetchFromOpenLibrary($cleanIsbn);
    }

    protected function fetchFromGoogleBooks(string $isbn): ?array
    {
        try {
            $baseUrl = config('litera.apis.google_books.base_url', 'https://www.googleapis.com/books/v1');
            $response = Http::timeout(8)->get("{$baseUrl}/volumes", [
                'q' => 'isbn:' . $isbn
            ]);

            if ($response->successful() && ($items = $response->json('items')) && count($items) > 0) {
                $info = $items[0]['volumeInfo'] ?? [];
                
                $title = $info['title'] ?? null;
                $authors = isset($info['authors']) ? implode(', ', $info['authors']) : null;
                $publisher = $info['publisher'] ?? null;
                $year = isset($info['publishedDate']) ? (int) substr($info['publishedDate'], 0, 4) : null;
                $description = $info['description'] ?? null;
                $coverUrl = $info['imageLinks']['thumbnail'] ?? ($info['imageLinks']['smallThumbnail'] ?? null);

                if ($title) {
                    return [
                        'isbn'             => $isbn,
                        'title'            => $title,
                        'author'           => $authors,
                        'publisher'        => $publisher,
                        'publication_year' => $year,
                        'description'      => $description,
                        'cover_url'        => $coverUrl,
                        'source'           => 'Google Books',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Google Books Enrichment Error: " . $e->getMessage());
        }

        return null;
    }

    protected function fetchFromOpenLibrary(string $isbn): ?array
    {
        try {
            $baseUrl = config('litera.apis.open_library.base_url', 'https://openlibrary.org');
            $response = Http::timeout(8)->get("{$baseUrl}/api/books", [
                'bibkeys' => 'ISBN:' . $isbn,
                'format'  => 'json',
                'jscmd'   => 'data'
            ]);

            if ($response->successful()) {
                $data = $response->json('ISBN:' . $isbn);
                if ($data) {
                    $authors = [];
                    if (isset($data['authors'])) {
                        foreach ($data['authors'] as $auth) {
                            $authors[] = $auth['name'] ?? '';
                        }
                    }

                    $publishers = [];
                    if (isset($data['publishers'])) {
                        foreach ($data['publishers'] as $pub) {
                            $publishers[] = $pub['name'] ?? '';
                        }
                    }

                    $year = isset($data['publish_date']) ? (int) preg_replace('/[^0-9]/', '', $data['publish_date']) : null;
                    $coverUrl = $data['cover']['medium'] ?? ($data['cover']['small'] ?? null);

                    return [
                        'isbn'             => $isbn,
                        'title'            => $data['title'] ?? null,
                        'author'           => implode(', ', array_filter($authors)),
                        'publisher'        => implode(', ', array_filter($publishers)),
                        'publication_year' => $year ? (int) substr((string)$year, 0, 4) : null,
                        'description'      => is_string($data['notes'] ?? null) ? $data['notes'] : null,
                        'cover_url'        => $coverUrl,
                        'source'           => 'Open Library',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Open Library Enrichment Error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Live search books on the web by title/keyword via Google Books with Open Library fallback.
     */
    public function searchWebBooks(string $query, int $limit = 10): array
    {
        $results = [];

        // 1. Try Google Books API
        try {
            $baseUrl = config('litera.apis.google_books.base_url', 'https://www.googleapis.com/books/v1');
            $response = Http::timeout(8)->get("{$baseUrl}/volumes", [
                'q'          => $query,
                'maxResults' => min($limit, 10),
            ]);

            if ($response->successful() && ($items = $response->json('items'))) {
                foreach ($items as $item) {
                    $info = $item['volumeInfo'] ?? [];
                    $title = $info['title'] ?? null;
                    if (!$title) continue;

                    $authors = isset($info['authors']) ? implode(', ', $info['authors']) : 'Tanpa Pengarang';
                    $publisher = $info['publisher'] ?? '-';
                    $year = isset($info['publishedDate']) ? (int) substr($info['publishedDate'], 0, 4) : null;
                    $description = $info['description'] ?? '';

                    $coverUrl = $info['imageLinks']['thumbnail'] ?? ($info['imageLinks']['smallThumbnail'] ?? null);
                    if ($coverUrl && str_starts_with($coverUrl, 'http://')) {
                        $coverUrl = str_replace('http://', 'https://', $coverUrl);
                    }

                    $isbn = null;
                    if (isset($info['industryIdentifiers']) && is_array($info['industryIdentifiers'])) {
                        foreach ($info['industryIdentifiers'] as $id) {
                            if (in_array($id['type'] ?? '', ['ISBN_13', 'ISBN_10'])) {
                                $isbn = $id['identifier'];
                                break;
                            }
                        }
                    }

                    $results[] = [
                        'title'            => $title,
                        'author'           => $authors,
                        'publisher'        => $publisher,
                        'publication_year' => $year,
                        'isbn'             => $isbn,
                        'description'      => $description,
                        'cover_url'        => $coverUrl,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Google Books Search Error: " . $e->getMessage());
        }

        // 2. If results empty, fallback to Open Library Search API
        if (empty($results)) {
            try {
                $response = Http::timeout(8)->get("https://openlibrary.org/search.json", [
                    'q'     => $query,
                    'limit' => $limit,
                ]);

                if ($response->successful() && ($docs = $response->json('docs'))) {
                    foreach ($docs as $doc) {
                        $title = $doc['title'] ?? null;
                        if (!$title) continue;

                        $authors = isset($doc['author_name']) ? implode(', ', array_slice($doc['author_name'], 0, 3)) : 'Tanpa Pengarang';
                        $publisher = isset($doc['publisher']) ? implode(', ', array_slice($doc['publisher'], 0, 2)) : '-';
                        $year = $doc['first_publish_year'] ?? null;
                        $coverId = $doc['cover_i'] ?? null;
                        $coverUrl = $coverId ? "https://covers.openlibrary.org/b/id/{$coverId}-M.jpg" : null;
                        $isbn = isset($doc['isbn']) && is_array($doc['isbn']) ? $doc['isbn'][0] : null;

                        $results[] = [
                            'title'            => $title,
                            'author'           => $authors,
                            'publisher'        => $publisher,
                            'publication_year' => $year,
                            'isbn'             => $isbn,
                            'description'      => '',
                            'cover_url'        => $coverUrl,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Open Library Search Error: " . $e->getMessage());
            }
        }

        return $results;
    }
}
