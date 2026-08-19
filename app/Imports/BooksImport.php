<?php

namespace App\Imports;

use App\Models\Book;
use App\Models\BookCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BooksImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function chunkSize(): int
    {
        return 500;
    }

    public function model(array $row)
    {
        // Normalize array keys to lowercase stripped of special characters
        $cleanRow = [];
        foreach ($row as $k => $v) {
            $cleanKey = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(['-', ' '], '_', (string)$k)));
            $cleanRow[$cleanKey] = $v;
        }

        // 1. Flexible Title Detection
        $title = $cleanRow['judul_buku'] ?? $cleanRow['judul'] ?? $cleanRow['title'] ?? $cleanRow['book_title'] ?? $cleanRow['booktitle'] ?? null;
        if (empty($title)) {
            return null; // Skip empty or title-less rows
        }

        $titleClean = mb_substr(trim((string)$title), 0, 255);

        // 2. Flexible ISBN Detection
        $isbnRaw = $cleanRow['isbn'] ?? $cleanRow['isbn13'] ?? $cleanRow['isbn10'] ?? $cleanRow['book_isbn'] ?? $cleanRow['bookisbn'] ?? null;
        $isbnClean = $isbnRaw ? mb_substr(trim((string)$isbnRaw), 0, 30) : null;

        // 3. Flexible Author Detection
        $authorRaw = $cleanRow['pengarang'] ?? $cleanRow['author'] ?? $cleanRow['authors'] ?? $cleanRow['book_author'] ?? $cleanRow['bookauthor'] ?? 'Penulis Referensi';
        $authorClean = mb_substr(trim((string)$authorRaw), 0, 255);

        // Deduplication Check: Skip if book with same ISBN or same Title+Author already exists
        $existing = null;
        if (!empty($isbnClean)) {
            $existing = Book::where('isbn', $isbnClean)->first();
        }
        if (!$existing) {
            $existing = Book::where('title', $titleClean)->where('author', $authorClean)->first();
        }

        if ($existing) {
            // Already uploaded in previous batch, skip to prevent duplicates
            return null;
        }

        // 4. Flexible Publisher Detection
        $publisher = $cleanRow['penerbit'] ?? $cleanRow['publisher'] ?? 'Penerbit Umum';

        // 5. Flexible Year Detection
        $yearRaw = $cleanRow['tahun'] ?? $cleanRow['year'] ?? $cleanRow['published_year'] ?? $cleanRow['publishedyear'] ?? $cleanRow['year_of_publication'] ?? date('Y');
        $year = (int) preg_replace('/[^0-9]/', '', (string)$yearRaw);
        if ($year < 1900 || $year > ((int)date('Y') + 2)) {
            $year = (int) date('Y');
        }

        // 6. Flexible Category & Shelf Detection
        $rawCat = $cleanRow['kategori'] ?? $cleanRow['category'] ?? $cleanRow['categories'] ?? 'Karya Umum & Komputer';
        $categoryName = trim(explode(',', (string)$rawCat)[0]);
        if (empty($categoryName)) {
            $categoryName = 'Karya Umum & Komputer';
        }
        $categoryName = mb_substr($categoryName, 0, 100);

        try {
            $category = BookCategory::firstOrCreate(
                ['name' => $categoryName],
                ['code' => (string) rand(100, 999), 'description' => 'Kategori Referensi Import']
            );
        } catch (\Throwable $e) {
            $category = BookCategory::first();
            if (!$category) return null;
        }

        $shelf = $cleanRow['rak'] ?? $cleanRow['shelf'] ?? 'Rak Referensi';
        $stock = (int) ($cleanRow['stok'] ?? $cleanRow['stock'] ?? 5);

        // 7. Flexible Description & Cover Image Detection
        $description = $cleanRow['deskripsi'] ?? $cleanRow['description'] ?? $cleanRow['abstract'] ?? $cleanRow['summary'] ?? null;
        $cover = $cleanRow['cover'] ?? $cleanRow['image_url_l'] ?? $cleanRow['image_url_m'] ?? $cleanRow['image_url_s'] ?? $cleanRow['thumbnail'] ?? $cleanRow['image'] ?? null;

        $existingCode = $cleanRow['kode_buku'] ?? $cleanRow['book_code'] ?? null;
        $code = $existingCode
            ? mb_substr(trim((string)$existingCode), 0, 50)
            : ('BK-' . strtoupper(base_convert((int)(microtime(true) * 1000) + rand(1, 9999), 10, 36)));

        if (Book::where('book_code', $code)->exists()) {
            $code = 'BK-' . strtoupper(base_convert((int)(microtime(true) * 10000) + rand(1, 9999), 10, 36));
        }

        return new Book([
            'book_code'   => $code,
            'isbn'        => $isbnClean,
            'title'       => $titleClean,
            'author'      => $authorClean,
            'publisher'   => mb_substr(trim((string)$publisher), 0, 255),
            'year'        => $year,
            'category_id' => $category->id,
            'shelf'       => mb_substr(trim((string)$shelf), 0, 50),
            'stock'       => max(1, $stock),
            'cover'       => $cover ? mb_substr(trim((string)$cover), 0, 500) : null,
            'description' => $description ? mb_substr(trim((string)$description), 0, 3000) : null,
        ]);
    }
}
