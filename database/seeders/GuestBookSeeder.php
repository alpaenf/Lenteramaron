<?php

namespace Database\Seeders;

use App\Models\GuestBook;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class GuestBookSeeder extends Seeder
{
    public function run(): void
    {
        $guests = [
            [
                'visitor_no' => 'VIS-20260801-001',
                'name' => 'Aditya Pratama',
                'institution' => 'Siswa Kelas 5A',
                'purpose' => 'Membaca Ensiklopedi Sains',
                'feedback' => 'Buku sainsnya sangat seru dan gambarnya bagus.',
                'note' => 'Kunjungan jam istirahat pertama',
                'date' => Carbon::now()->toDateString(),
                'time' => '09:30:00',
            ],
            [
                'visitor_no' => 'VIS-20260801-002',
                'name' => 'Dra. Endang Rahayu',
                'institution' => 'Pengawas Dinas Pendidikan Maron',
                'purpose' => 'Monev Pelayanan Perpustakaan Sekolah',
                'feedback' => 'Penataan buku sangat rapi dan suasana perpustakaan ramah anak.',
                'note' => 'Supervisi rutin triwulan',
                'date' => Carbon::now()->toDateString(),
                'time' => '10:15:00',
            ],
        ];

        foreach ($guests as $g) {
            GuestBook::updateOrCreate(['visitor_no' => $g['visitor_no']], $g);
        }
    }
}
