<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $galleries = [
            [
                'title' => 'Pekan Gerakan Literasi Sekolah 2026',
                'category' => 'Literasi',
                'image_path' => 'galleries/literasi.jpg',
                'description' => 'Kegiatan membaca bersama 15 menit sebelum pelajaran dimulai di halaman SDN 02 Maron.',
            ],
            [
                'title' => 'Sudut Baca Ramah Anak Perpustakaan Lentera Maron',
                'category' => 'Perpustakaan',
                'image_path' => 'galleries/perpustakaan.jpg',
                'description' => 'Fasilitas karpet empuk dan bean bag nyaman untuk membaca siswa.',
            ],
            [
                'title' => 'Outing Class Wisata Literasi ke Perpustakaan Daerah',
                'category' => 'Outing Class',
                'image_path' => 'galleries/outing.jpg',
                'description' => 'Kunjungan edukatif kelas 5 dan 6 mengenal koleksi arsip bersejarah.',
            ],
            [
                'title' => 'Juara 1 Lomba Bercerita Dongeng Nusantara',
                'category' => 'Lomba',
                'image_path' => 'galleries/lomba.jpg',
                'description' => 'Prestasi membanggakan ananda Anisa Rahmawati tingkat kecamatan Maron.',
            ],
        ];

        foreach ($galleries as $gal) {
            Gallery::updateOrCreate(['title' => $gal['title']], $gal);
        }
    }
}
