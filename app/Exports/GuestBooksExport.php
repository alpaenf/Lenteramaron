<?php

namespace App\Exports;

use App\Models\GuestBook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuestBooksExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return GuestBook::latest('date')->get();
    }

    public function headings(): array
    {
        return [
            'No. Pengunjung',
            'Nama Pengunjung',
            'Instansi / Kelas',
            'Keperluan',
            'Kesan & Pesan',
            'Tanggal',
            'Jam',
        ];
    }

    public function map($guest): array
    {
        return [
            $guest->visitor_no,
            $guest->name,
            $guest->institution,
            $guest->purpose,
            $guest->feedback,
            $guest->date->format('Y-m-d'),
            $guest->time,
        ];
    }
}
