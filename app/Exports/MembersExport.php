<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MembersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Member::all();
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama Lengkap',
            'Kelas',
            'L/P',
            'Alamat',
            'Nama Orang Tua',
            'No. HP Orang Tua',
            'Status',
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
}
