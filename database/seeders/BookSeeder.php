<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCategory;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $catSains = BookCategory::where('code', '500')->first()?->id ?? 1;
        $catBahasa = BookCategory::where('code', '400')->first()?->id ?? 1;
        $catDongeng = BookCategory::where('code', '800')->first()?->id ?? 1;
        $catSejarah = BookCategory::where('code', '900')->first()?->id ?? 1;
        $catAgama = BookCategory::where('code', '200')->first()?->id ?? 1;

        $books = [
            [
                'book_code' => 'BK-001',
                'isbn' => '978-602-1234-01-1',
                'title' => 'Ensiklopedi Sains Anak: Mengenal Tata Surya',
                'author' => 'Dr. Ahmad Fauzi',
                'publisher' => 'Erlangga Kid',
                'year' => 2022,
                'category_id' => $catSains,
                'shelf' => 'Rak A-01',
                'stock' => 12,
                'cover' => null,
                'description' => 'Buku ensiklopedi berilustrasi menarik tentang keajaiban planet dan bintang untuk siswa sekolah dasar.',
            ],
            [
                'book_code' => 'BK-002',
                'isbn' => '978-602-1234-02-8',
                'title' => 'Kumpulan Dongeng Nusantara Ramah Anak',
                'author' => 'Retno Wulandari',
                'publisher' => 'Balai Pustaka',
                'year' => 2021,
                'category_id' => $catDongeng,
                'shelf' => 'Rak B-02',
                'stock' => 8,
                'cover' => null,
                'description' => 'Kumpulan kisah fabel dan folklore Indonesia yang mendidik serta kaya nilai budi pekerti.',
            ],
            [
                'book_code' => 'BK-003',
                'isbn' => '978-602-1234-03-5',
                'title' => 'Kisah 25 Nabi dan Rasul Berwarna',
                'author' => 'Ust. Muhammad Ilyas',
                'publisher' => 'Gema Insani',
                'year' => 2023,
                'category_id' => $catAgama,
                'shelf' => 'Rak C-01',
                'stock' => 15,
                'cover' => null,
                'description' => 'Panduan inspiratif sejarah para nabi dengan penyajian gambar berwarna cerah.',
            ],
            [
                'book_code' => 'BK-004',
                'isbn' => '978-602-1234-04-2',
                'title' => 'Pahlawan Kemerdekaan Indonesia: Ki Hajar Dewantara',
                'author' => 'Bambang Sukarno',
                'publisher' => 'Yudhistira',
                'year' => 2020,
                'category_id' => $catSejarah,
                'shelf' => 'Rak D-03',
                'stock' => 6,
                'cover' => null,
                'description' => 'Biografi ringkas bapak pendidikan nasional untuk memupuk jiwa patriotisme siswa.',
            ],
            [
                'book_code' => 'BK-005',
                'isbn' => '978-602-1234-05-9',
                'title' => 'Belajar Bahasa Inggris Bergambar untuk SD Kelas 1-3',
                'author' => 'Sarah Johnson, M.Pd',
                'publisher' => 'Tiga Serangkai',
                'year' => 2023,
                'category_id' => $catBahasa,
                'shelf' => 'Rak A-03',
                'stock' => 10,
                'cover' => null,
                'description' => 'Kosakata dasar sehari-hari Bahasa Inggris disertai kartu kata dan latihan seru.',
            ],
        ];

        foreach ($books as $b) {
            Book::updateOrCreate(['book_code' => $b['book_code']], $b);
        }
    }
}
