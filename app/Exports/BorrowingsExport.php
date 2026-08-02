<?php

namespace App\Exports;

use App\Models\Borrowing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BorrowingsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Borrowing::with(['member', 'book'])->latest('borrow_date')->get();
    }

    public function headings(): array
    {
        return [
            'No Transaksi',
            'NIS / Anggota',
            'Nama Anggota',
            'Kode Buku',
            'Judul Buku',
            'Tanggal Pinjam',
            'Jatuh Tempo',
            'Status',
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
            $b->borrow_date->format('Y-m-d'),
            $b->due_date->format('Y-m-d'),
            $b->status,
        ];
    }
}
