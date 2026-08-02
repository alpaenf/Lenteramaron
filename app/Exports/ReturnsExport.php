<?php

namespace App\Exports;

use App\Models\ReturnBook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReturnsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return ReturnBook::with(['member', 'book', 'borrowing'])->latest('return_date')->get();
    }

    public function headings(): array
    {
        return [
            'NO PENGEMBALIAN',
            'NO TRANSAKSI PINJAM',
            'NAMA ANGGOTA',
            'JUDUL BUKU',
            'TANGGAL KEMBALI',
            'KONDISI BUKU',
            'TERLAMBAT (HARI)',
            'CATATAN',
        ];
    }

    public function map($r): array
    {
        return [
            $r->return_no,
            $r->borrowing?->transaction_no,
            $r->member?->name,
            $r->book?->title,
            $r->return_date ? $r->return_date->format('d/m/Y') : '-',
            $r->condition,
            $r->late_days,
            $r->note ?? '-',
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
