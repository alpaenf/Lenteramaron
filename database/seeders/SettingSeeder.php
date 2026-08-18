<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'school_name' => 'LITERA Knowledge Hub',
            'library_name' => 'LITERA — AI Research & Library Navigator',
            'school_address' => 'Jl. Literasi Akademik No. 10, Kota Pendidikan, Indonesia',
            'school_email' => 'info@litera-navigator.id',
            'school_phone' => '0812-3456-7890',
            'headmaster_name' => 'Dr. Mulyono, M.Pd',
            'librarian_name' => 'Siti Pustakawan, S.IP',
            'vision' => 'Menjadi Platform Navigator Pengetahuan Pintar Berstandar Ilmiah yang Menghubungkan Koleksi Referensi Lokal dengan Literatur Global.',
            'mission' => "1. Menyediakan pencarian literatur berbasis makna tanpa ketergantungan pada keyword persis.\n2. Memberikan sintesis relevansi AI yang jujur dan dapat diverifikasi.\n3. Menyusun alur eksplorasi urutan membaca (Research Path) secara terstruktur.\n4. Memudahkan integrasi metadata buku lokal dan jurnal publik open access.",
            'gmaps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.6751394476323!2d109.92206757357137!3d-7.277756071516778!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e700b18b719df35%3A0x753393990615190a!2sSD%20N%202%20MARON!5e0!3m2!1sid!2sid!4v1785695520988!5m2!1sid!2sid',
            'gmaps_url' => 'https://maps.app.goo.gl/AvLEeA6feNjgzVZVA',
            'spreadsheet_url' => 'https://docs.google.com/spreadsheets/d/1example_spreadsheet_lenteramaron/edit',
            'logo_path' => null,
            'hero_banner_path' => null,
        ];

        foreach ($settings as $key => $value) {
            Setting::setByKey($key, $value);
        }
    }
}
