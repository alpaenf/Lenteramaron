<?php

namespace App\Imports;

use App\Models\Book;
use App\Models\BookCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BooksImport implements ToModel, WithHeadingRow, WithLimit, WithChunkReading
{
    public function limit(): int
    {
        return 500; // Limit to 500 rows per upload batch for instant execution & zero timeout
    }

    public function chunkSize(): int
    {
        return 100;
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

        // 2. Flexible ISBN Detection
        $isbn = $cleanRow['isbn'] ?? $cleanRow['isbn13'] ?? $cleanRow['isbn10'] ?? $cleanRow['book_isbn'] ?? $cleanRow['bookisbn'] ?? null;

        // 3. Flexible Author Detection
        $author = $cleanRow['pengarang'] ?? $cleanRow['author'] ?? $cleanRow['authors'] ?? $cleanRow['book_author'] ?? $cleanRow['bookauthor'] ?? 'Penulis Referensi';

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

        $category = BookCategory::firstOrCreate(
            ['name' => $categoryName],
            ['code' => (string) rand(100, 999), 'description' => 'Kategori Referensi Import']
        );

        $shelf = $cleanRow['rak'] ?? $cleanRow['shelf'] ?? 'Rak Referensi';
        $stock = (int) ($cleanRow['stok'] ?? $cleanRow['stock'] ?? 5);

        // 7. Flexible Description & Cover Image Detection
        $description = $cleanRow['deskripsi'] ?? $cleanRow['description'] ?? $cleanRow['abstract'] ?? $cleanRow['summary'] ?? null;
        $cover = $cleanRow['cover'] ?? $cleanRow['image_url_l'] ?? $cleanRow['image_url_m'] ?? $cleanRow['image_url_s'] ?? $cleanRow['thumbnail'] ?? $cleanRow['image'] ?? null;

        $code = $cleanRow['kode_buku'] ?? $cleanRow['book_code'] ?? ('BK-' . sprintf('%04d', rand(1000, 9999)));

        return new Book([
            'book_code'   => trim((string)$code),
            'isbn'        => $isbn ? trim((string)$isbn) : null,
            'title'       => trim((string)$title),
            'author'      => trim((string)$author),
            'publisher'   => trim((string)$publisher),
            'year'        => $year,
            'category_id' => $category->id,
            'shelf'       => trim((string)$shelf),
            'stock'       => max(1, $stock),
            'cover'       => $cover ? trim((string)$cover) : null,
            'description' => $description ? trim((string)$description) : null,
        ]);
    }
}
