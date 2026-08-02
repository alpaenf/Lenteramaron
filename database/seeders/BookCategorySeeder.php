<?php

namespace Database\Seeders;

use App\Models\BookCategory;
use Illuminate\Database\Seeder;

class BookCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => '000', 'name' => 'Karya Umum & Komputer', 'description' => 'Ensiklopedi, kamus, & ilmu umum'],
            ['code' => '100', 'name' => 'Filsafat & Psikologi', 'description' => 'Pembentukan karakter & psikologi anak'],
            ['code' => '200', 'name' => 'Agama Islam', 'description' => 'Pendidikan agama Islam & kisah nabi'],
            ['code' => '300', 'name' => 'Ilmu Sosial & Kewarganegaraan', 'description' => 'Sosiologi, tata negara, & IPS SD'],
            ['code' => '400', 'name' => 'Bahasa & Sastra', 'description' => 'Bahasa Indonesia & Bahasa Inggris SD'],
            ['code' => '500', 'name' => 'Sains & Matematika', 'description' => 'IPA, Matematika, & Biologi dasar'],
            ['code' => '600', 'name' => 'Teknologi & Keterampilan', 'description' => 'Prakarya, pertanian, & kerajinan'],
            ['code' => '700', 'name' => 'Seni & Olahraga', 'description' => 'Musik, gambar, & kebugaran jasmani'],
            ['code' => '800', 'name' => 'Kesusastraan & Dongeng', 'description' => 'Cerita rakyat, cerpen, & fiksi anak'],
            ['code' => '900', 'name' => 'Sejarah & Geografi', 'description' => 'Pahlawan nasional & peta nusantara'],
        ];

        foreach ($categories as $cat) {
            BookCategory::updateOrCreate(['code' => $cat['code']], $cat);
        }
    }
}
