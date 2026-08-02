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
        $category = BookCategory::firstOrCreate(
            ['name' => $row['kategori'] ?? 'Karya Umum'],
            ['code' => rand(100, 999)]
        );

        return new Book([
            'book_code' => $row['kode_buku'] ?? ('BK-' . rand(1000, 9999)),
            'isbn' => $row['isbn'] ?? null,
            'title' => $row['judul_buku'] ?? $row['judul'] ?? 'Judul Tanpa Nama',
            'author' => $row['pengarang'] ?? 'Anonim',
            'publisher' => $row['penerbit'] ?? 'Penerbit Umum',
            'year' => (int) ($row['tahun'] ?? date('Y')),
            'category_id' => $category->id,
            'shelf' => $row['rak'] ?? 'Rak Utama',
            'stock' => (int) ($row['stok'] ?? 1),
            'description' => $row['deskripsi'] ?? null,
        ]);
    }
}
