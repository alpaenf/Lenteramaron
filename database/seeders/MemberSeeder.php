<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'nis' => '2024001',
                'name' => 'Aditya Pratama',
                'class_name' => 'Kelas 5A',
                'gender' => 'L',
                'address' => 'Jl. Merdeka No. 12, Maron',
                'parent_name' => 'Suryadi Pratama',
                'parent_phone' => '081234567801',
                'status' => 'Aktif',
            ],
            [
                'nis' => '2024002',
                'name' => 'Anisa Rahmawati',
                'class_name' => 'Kelas 5A',
                'gender' => 'P',
                'address' => 'Desa Maron Kidul RT 02/01',
                'parent_name' => 'Tri Rahmadi',
                'parent_phone' => '081234567802',
                'status' => 'Aktif',
            ],
            [
                'nis' => '2024003',
                'name' => 'Dimas Anggara',
                'class_name' => 'Kelas 4B',
                'gender' => 'L',
                'address' => 'Dusun Krajan No. 45, Maron',
                'parent_name' => 'Heru Anggara',
                'parent_phone' => '081234567803',
                'status' => 'Aktif',
            ],
            [
                'nis' => '2024004',
                'name' => 'Siti Nurhaliza',
                'class_name' => 'Kelas 6A',
                'gender' => 'P',
                'address' => 'Jl. Stasiun Maron No. 8',
                'parent_name' => 'H. Ahmad',
                'parent_phone' => '081234567804',
                'status' => 'Aktif',
            ],
            [
                'nis' => '2024005',
                'name' => 'Fajar Ramadhan',
                'class_name' => 'Kelas 3B',
                'gender' => 'L',
                'address' => 'Desa Maron Wetan',
                'parent_name' => 'Agus Ramadhan',
                'parent_phone' => '081234567805',
                'status' => 'Aktif',
            ],
        ];

        foreach ($members as $m) {
            Member::updateOrCreate(['nis' => $m['nis']], $m);
        }
    }
}
