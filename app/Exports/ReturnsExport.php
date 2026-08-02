<?php

namespace App\Exports;

use App\Models\ReturnBook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReturnsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return ReturnBook::with(['member', 'book', 'borrowing'])->latest('return_date')->get();
    }

    public function headings(): array
    {
        return [
            'No Pengembalian',
            'No Transaksi Pinjam',
            'Nama Anggota',
            'Judul Buku',
            'Tanggal Kembali',
            'Kondisi Buku',
            'Terlambat (Hari)',
            'Catatan',
        ];
    }

    public function map($r): array
    {
        return [
            $r->return_no,
            $r->borrowing?->transaction_no,
            $r->member?->name,
            $r->book?->title,
            $r->return_date->format('Y-m-d'),
            $r->condition,
            $r->late_days,
            $r->note ?? '-',
        ];
    }
}
