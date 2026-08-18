<?php

namespace App\Imports;

use App\Models\Book;
use App\Models\BookCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BooksImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Flexible Title Detection
        $title = $row['judul_buku'] ?? $row['judul'] ?? $row['title'] ?? $row['book_title'] ?? $row['book_title_clean'] ?? null;
        if (empty($title)) {
            return null; // Skip empty rows
        }

        // 2. Flexible ISBN Detection
        $isbn = $row['isbn'] ?? $row['isbn13'] ?? $row['isbn10'] ?? $row['book_isbn'] ?? null;

        // 3. Flexible Author Detection
        $author = $row['pengarang'] ?? $row['author'] ?? $row['authors'] ?? $row['book_author'] ?? 'Penulis Referensi';

        // 4. Flexible Publisher Detection
        $publisher = $row['penerbit'] ?? $row['publisher'] ?? 'Penerbit Umum';

        // 5. Flexible Year Detection
        $yearRaw = $row['tahun'] ?? $row['year'] ?? $row['published_year'] ?? $row['year_of_publication'] ?? date('Y');
        $year = (int) preg_replace('/[^0-9]/', '', (string)$yearRaw);
        if ($year < 1900 || $year > ((int)date('Y') + 2)) {
            $year = (int) date('Y');
        }

        // 6. Flexible Category & Shelf Detection
        $rawCat = $row['kategori'] ?? $row['category'] ?? $row['categories'] ?? 'Karya Umum & Komputer';
        $categoryName = trim(explode(',', (string)$rawCat)[0]);
        if (empty($categoryName)) {
            $categoryName = 'Karya Umum & Komputer';
        }

        $category = BookCategory::firstOrCreate(
            ['name' => $categoryName],
            ['code' => (string) rand(100, 999), 'description' => 'Kategori Referensi Import']
        );

        $shelf = $row['rak'] ?? $row['shelf'] ?? 'Rak Referensi';
        $stock = (int) ($row['stok'] ?? $row['stock'] ?? 5);

        // 7. Flexible Description & Cover Image Detection
        $description = $row['deskripsi'] ?? $row['description'] ?? $row['abstract'] ?? $row['summary'] ?? null;
        $cover = $row['cover'] ?? $row['image_url_l'] ?? $row['image_url_m'] ?? $row['thumbnail'] ?? $row['image'] ?? null;

        $code = $row['kode_buku'] ?? $row['book_code'] ?? ('BK-' . sprintf('%04d', rand(1000, 9999)));

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
