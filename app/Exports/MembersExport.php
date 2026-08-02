<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MembersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Member::all();
    }

    public function headings(): array
    {
        return [
            'NIS',
            'NAMA LENGKAP',
            'KELAS',
            'L/P',
            'ALAMAT',
            'NAMA ORANG TUA',
            'NO. HP ORANG TUA',
            'STATUS',
        ];
    }

    public function map($member): array
    {
        return [
            $member->nis,
            $member->name,
            $member->class_name,
            $member->gender,
            $member->address,
            $member->parent_name,
            $member->parent_phone,
            $member->status,
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
