<?php

namespace App\Exports;

use App\Models\Borrowing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BorrowingsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Borrowing::with(['member', 'book'])->latest('borrow_date')->get();
    }

    public function headings(): array
    {
        return [
            'NO TRANSAKSI',
            'NIS',
            'NAMA ANGGOTA',
            'KODE BUKU',
            'JUDUL BUKU',
            'TANGGAL PINJAM',
            'JATUH TEMPO',
            'STATUS',
        ];
    }

    public function map($b): array
    {
        return [
            $b->transaction_no,
            $b->member?->nis,
            $b->member?->name,
            $b->book?->book_code,
            $b->book?->title,
            $b->borrow_date ? $b->borrow_date->format('d/m/Y') : '-',
            $b->due_date ? $b->due_date->format('d/m/Y') : '-',
            $b->status,
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
