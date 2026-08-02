<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'school_name' => 'SD Negeri 02 Maron',
            'library_name' => 'LENTERA MARON',
            'school_address' => 'Jl. Raya Maron No. 45, Kecamatan Maron, Kabupaten Probolinggo, Jawa Timur 67276',
            'school_email' => 'sdn02maron@gmail.com',
            'school_phone' => '(0335) 771234 / 0812-3456-7890',
            'headmaster_name' => 'Drs. H. Mulyono, M.Pd',
            'librarian_name' => 'Siti Pustakawan, S.IP',
            'vision' => 'Terwujudnya Perpustakaan Sekolah yang Unggul, Inovatif, Ramah Anak, serta Menjadi Pusat Sumber Belajar dan Budaya Literasi Siswa.',
            'mission' => "1. Menyeleggarakan pelayanan perpustakaan yang ramah, efisien, dan berbasis teknologi modern.\n2. Menyediakan koleksi buku bacaan berkualitas dan edukatif.\n3. Menumbuhkan minat dan budaya baca siswa melalui program literasi kreatif.\n4. Menyediakan sarana ruang baca yang aman, nyaman, dan menyenangkan.",
            'gmaps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15809.123456789!2d113.3150000!3d-7.8500000!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd70123456789%3A0x123456789!2sSD%20Negeri%2002%20Maron!5e0!3m2!1sid!2sid!4v1700000000000',
            'spreadsheet_url' => 'https://docs.google.com/spreadsheets/d/1example_spreadsheet_lenteramaron/edit',
            'logo_path' => null,
            'hero_banner_path' => null,
        ];

        foreach ($settings as $key => $value) {
            Setting::setByKey($key, $value);
        }
    }
}
