<?php

namespace App\Exports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BooksExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Book::with('category')->get();
    }

    public function headings(): array
    {
        return [
            'KODE BUKU',
            'ISBN',
            'JUDUL BUKU',
            'PENGARANG',
            'PENERBIT',
            'TAHUN',
            'KATEGORI',
            'RAK',
            'STOK',
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

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F1F5F9']
                ]
            ],
        ];
    }
}
