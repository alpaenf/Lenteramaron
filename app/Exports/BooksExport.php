<?php

namespace App\Exports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BooksExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Book::with('category')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Buku',
            'ISBN',
            'Judul Buku',
            'Pengarang',
            'Penerbit',
            'Tahun',
            'Kategori',
            'Rak',
            'Stok',
        ];
    }

    public function map($book): array
    {
        return [
            $book->book_code,
            $book->isbn ?? '-',
            $book->title,
            $book->author,
            $book->publisher,
            $book->year,
            $book->category?->name ?? '-',
            $book->shelf,
            $book->stock,
        ];
    }
}
