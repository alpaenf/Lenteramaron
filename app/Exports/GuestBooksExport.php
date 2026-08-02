<?php

namespace App\Exports;

use App\Models\GuestBook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuestBooksExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return GuestBook::latest('date')->get();
    }

    public function headings(): array
    {
        return [
            'NO PENGUNJUNG',
            'NAMA PENGUNJUNG',
            'INSTANSI / KELAS',
            'KEPERLUAN',
            'KESAN & PESAN',
            'TANGGAL',
            'JAM',
        ];
    }

    public function map($guest): array
    {
        return [
            $guest->visitor_no,
            $guest->name,
            $guest->institution,
            $guest->purpose,
            $guest->feedback ?: '-',
            $guest->date ? $guest->date->format('d/m/Y') : '-',
            $guest->time,
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
