<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCategory;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $catKomputer = BookCategory::where('code', '000')->first()?->id ?? 1;
        $catPsikologi = BookCategory::where('code', '100')->first()?->id ?? 1;
        $catSosial = BookCategory::where('code', '300')->first()?->id ?? 1;
        $catBahasa = BookCategory::where('code', '400')->first()?->id ?? 1;
        $catSains = BookCategory::where('code', '500')->first()?->id ?? 1;
        $catTeknologi = BookCategory::where('code', '600')->first()?->id ?? 1;
        $catSastra = BookCategory::where('code', '800')->first()?->id ?? 1;
        $catSejarah = BookCategory::where('code', '900')->first()?->id ?? 1;

        $books = [
            [
                'book_code' => 'BK-001',
                'isbn' => '978-623-7131-46-5',
                'title' => 'Artificial Intelligence: Searching, Reasoning, Planning, dan Learning (Edisi 3)',
                'author' => 'Suyanto',
                'publisher' => 'Informatika Bandung',
                'year' => 2021,
                'category_id' => $catKomputer,
                'shelf' => 'Rak A-01 (Informatika)',
                'stock' => 8,
                'cover' => 'https://images.unsplash.com/photo-1677442136019-21780efad99a?auto=format&fit=crop&w=600&q=80',
                'description' => 'Buku panduan komprehensif mengenai konsep pencarian, penalaran, perencanaan, dan pemelajaran mesin dalam kecerdasan buatan.',
            ],
            [
                'book_code' => 'BK-002',
                'isbn' => '978-623-8075-61-4',
                'title' => 'Metodologi Penelitian Teknik Informatika',
                'author' => 'Rohmat Taufiq, Supriyono',
                'publisher' => 'Teknosain',
                'year' => 2023,
                'category_id' => $catKomputer,
                'shelf' => 'Rak B-01 (Metodologi)',
                'stock' => 12,
                'cover' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=600&q=80',
                'description' => 'Panduan riset ilmiah berorientasi bidang komputer dan IT, dari perumusan masalah hingga publikasi jurnal terakreditasi.',
            ],
            [
                'book_code' => 'BK-003',
                'isbn' => '978-623-124-379-9',
                'title' => 'Deep Learning: Teori, Contoh Perhitungan, dan Implementasi',
                'author' => 'Dr. Eng. Agus Zainal Arifin, M.T.',
                'publisher' => 'Deepublish',
                'year' => 2024,
                'category_id' => $catKomputer,
                'shelf' => 'Rak A-02 (Artificial Intelligence)',
                'stock' => 6,
                'cover' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=600&q=80',
                'description' => 'Membahas arsitektur Convolutional Neural Networks (CNN), Recurrent Neural Networks (RNN), Transformer, dan implementasi Python.',
            ],
            [
                'book_code' => 'BK-004',
                'isbn' => '978-602-6232-78-6',
                'title' => 'Machine Learning: Tingkat Dasar dan Lanjut',
                'author' => 'Prof. Dr. Suyanto',
                'publisher' => 'Informatika Bandung',
                'year' => 2022,
                'category_id' => $catKomputer,
                'shelf' => 'Rak A-03 (Sains Data)',
                'stock' => 10,
                'cover' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=600&q=80',
                'description' => 'Pembahasan lengkap algoritma supervised, unsupervised, reinforcement learning, dan pengujian model data science.',
            ],
            [
                'book_code' => 'BK-005',
                'isbn' => '978-623-92672-2-3',
                'title' => 'AI And Data Science: Technology, Innovation & Use Cases In Indonesia',
                'author' => 'ABDI (Asosiasi Big Data & AI Indonesia)',
                'publisher' => 'ABDI Press',
                'year' => 2023,
                'category_id' => $catTeknologi,
                'shelf' => 'Rak A-04 (Sains Data)',
                'stock' => 7,
                'cover' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=600&q=80',
                'description' => 'Studi kasus implementasi Big Data, AI, dan analitik data cerdas di berbagai industri publik dan swasta Indonesia.',
            ],
            [
                'book_code' => 'BK-006',
                'isbn' => '978-623-02-0819-5',
                'title' => 'Dasar-Dasar Teknik Informatika',
                'author' => 'Novehitasari, M.Kom',
                'publisher' => 'Deepublish',
                'year' => 2022,
                'category_id' => $catKomputer,
                'shelf' => 'Rak D-01 (Informatika)',
                'stock' => 9,
                'cover' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=600&q=80',
                'description' => 'Pengenalan konsep dasar sistem komputer, algoritma logika, struktur data, dan jaringan komputer.',
            ],
            [
                'book_code' => 'BK-007',
                'isbn' => '978-634-2660-49-2',
                'title' => 'Literasi Informatika: Mengenal Kecerdasan Artifisial',
                'author' => 'Tim Erlangga Digital',
                'publisher' => 'Erlangga',
                'year' => 2024,
                'category_id' => $catBahasa,
                'shelf' => 'Rak B-02 (Literasi Digital)',
                'stock' => 15,
                'cover' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=600&q=80',
                'description' => 'Panduan literasi digital dan pemanfaatan AI secara bijak, etis, dan produktif dalam kegiatan belajar riset.',
            ],
            [
                'book_code' => 'BK-008',
                'isbn' => '978-623-209-852-7',
                'title' => 'Pengantar Teknologi Informatika dan Komunikasi Data',
                'author' => 'Bagus Satrio, M.T.',
                'publisher' => 'Deepublish',
                'year' => 2023,
                'category_id' => $catTeknologi,
                'shelf' => 'Rak A-05 (Komunikasi Data)',
                'stock' => 5,
                'cover' => 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=600&q=80',
                'description' => 'Konsep komunikasi data digital, protokol jaringan modern, cloud computing, dan keamanan informasi.',
            ]
        ];

        foreach ($books as $b) {
            Book::updateOrCreate(['book_code' => $b['book_code']], $b);
        }
    }
}
