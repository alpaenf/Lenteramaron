<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\Academic\MetadataEnrichmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MetadataEnrichmentController extends Controller
{
    protected MetadataEnrichmentService $enrichmentService;

    public function __construct(MetadataEnrichmentService $enrichmentService)
    {
        $this->enrichmentService = $enrichmentService;
    }

    /**
     * Endpoint to fetch metadata by ISBN for library admin book form.
     */
    public function enrichByIsbn(Request $request): JsonResponse
    {
        $request->validate([
            'isbn' => 'required|string|min:5|max:30',
        ]);

        $data = $this->enrichmentService->enrichByIsbn($request->input('isbn'));

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Metadata buku tidak ditemukan untuk ISBN tersebut.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Endpoint to search web books by title/keyword live.
     */
    public function searchWeb(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:200',
        ]);

        $results = $this->enrichmentService->searchWebBooks($request->input('q'));

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Endpoint to batch import books by a list of ISBNs.
     */
    public function batchImportIsbn(Request $request): JsonResponse
    {
        $request->validate([
            'isbns' => 'required|string',
        ]);

        $rawIsbns = array_filter(array_map('trim', explode("\n", $request->input('isbns'))));
        $importedCount = 0;
        $failedIsbns = [];

        foreach (array_slice($rawIsbns, 0, 20) as $isbn) {
            $data = $this->enrichmentService->enrichByIsbn($isbn);
            if ($data && !empty($data['title'])) {
                // Generate book code
                $bookCode = 'BK-' . strtoupper(Str::random(6));

                Book::create([
                    'book_code'        => $bookCode,
                    'isbn'             => $data['isbn'] ?? $isbn,
                    'title'            => $data['title'],
                    'author'           => $data['author'] ?? 'Tanpa Pengarang',
                    'publisher'        => $data['publisher'] ?? '-',
                    'year'             => $data['publication_year'] ?? date('Y'),
                    'stock'            => 1,
                    'cover'            => $data['cover_url'] ?? null,
                    'description'      => $data['description'] ?? '',
                ]);
                $importedCount++;
            } else {
                $failedIsbns[] = $isbn;
            }
        }

        return response()->json([
            'success'        => true,
            'imported_count' => $importedCount,
            'failed_isbns'   => $failedIsbns,
            'message'        => "Berhasil mengimpor {$importedCount} buku dari web.",
        ]);
    }
}
