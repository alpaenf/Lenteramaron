# Product Requirements Document (PRD)

## Project Name
**LENTERA ILMU** - Sistem Pelayanan Perpustakaan SD Negeri 02 Maron

## Overview
LENTERA ILMU adalah aplikasi web pelayanan perpustakaan modern berbasis **Laravel 13**, **Vue 3**, **Inertia.js**, **Tailwind CSS**, dan **MySQL**. Aplikasi ini dirancang khusus untuk SD Negeri 02 Maron guna mempermudah pengelolaan koleksi buku, data anggota siswa, transaksi peminjaman & pengembalian buku, registrasi buku tamu pengunjung, statistik perpustakaan, galeri kegiatan, serta pembuatan laporan secara otomatis.

---

## Target User & Roles (RBAC)

1. **Admin**
   - Akses penuh ke seluruh modul sistem (Master Buku, Anggota, Transaksi, Laporan, Galeri, Pengaturan Sistem, Manajemen User).
2. **Pustakawan**
   - Mengelola operasional harian perpustakaan: Sirkulasi Peminjaman & Pengembalian, Data Buku, Data Anggota, Buku Tamu, Galeri, dan Laporan Operasional.
3. **Guru**
   - Mengakses katalog buku, melihat statistik perpustakaan, berita/galeri kegiatan, dan melihat laporan pelayanan.
4. **Kepala Sekolah**
   - Mengakses Executive Dashboard, melihat statistik kunjungan & sirkulasi buku, serta mengunduh/mencetak Laporan Rekapitulasi.
5. **Siswa**
   - Tidak memiliki akun login mandiri. Terdaftar sebagai Anggota Perpustakaan. Seluruh transaksi peminjaman/pengembalian dilayani langsung oleh Pustakawan di meja pelayanan.

---

## Technical Stack & Libraries

- **Backend Framework**: Laravel 13 (PHP 8.3+)
- **Frontend Framework**: Vue 3 (Composition API) + Inertia.js
- **Styling Framework**: Tailwind CSS (School Library Modern Theme, Child-Friendly, Rounded Cards, Soft Shadows)
- **Database**: MySQL 8.0+
- **Reporting & Export**:
  - `barryvdh/laravel-dompdf` (Cetak PDF Laporan & Cetak Kartu/Buku Tamu)
  - `maatwebsite/excel` (Import & Export Excel Data Buku, Anggota, Buku Tamu, Peminjaman)
- **Data Visualization**: Chart.js / `vue-chartjs`
- **Icon Set**: Lucide Vue Next

---

## Key Modules & Functional Requirements

### 1. Landing Page (Public Portal)
- **Hero Banner**: Banner interaktif ramah anak dengan pesan sambutan hangat perpustakaan.
- **Profil Sekolah & Perpustakaan**: Informasi seputar SD Negeri 02 Maron dan fasilitas perpustakaan.
- **Visi & Misi**: Poin-poin visi misi peningkatan minat baca siswa.
- **Layanan Perpustakaan**: Daftar jam buka, fasilitas baca, dan program literasi.
- **Statistik Publik**: Counter jumlah buku, total peminjaman, jumlah anggota, dan statistik kunjungan.
- **Galeri Kegiatan**: Grid foto kegiatan perpustakaan (Literasi, Outing Class, Lomba, dll).
- **Kontak & Lokasi**: Informasi kontak resmi, alamat, jam operasional, dan Google Maps iframe embed.
- **Form Buku Tamu Mandiri**: Pengunjung dapat mengisi buku tamu secara digital di area perpustakaan.

### 2. Executive Dashboard
- **Widget Ringkasan Data**: Total Koleksi Buku, Total Anggota Aktif, Total Pengunjung Hari Ini/Bulan Ini, Total Peminjaman Aktif, Total Pengembalian.
- **Buku Terpopuler**: Carousel / List 5-10 buku paling sering dipinjam.
- **Grafik Tren (Chart.js)**: Line Chart peminjaman vs pengembalian bulanan & Bar Chart statistik pengunjung harian/bulanan.
- **Aktivitas Terbaru**: Log real-time transaksi peminjaman, pengembalian, dan pengisian buku tamu.

### 3. Master Data Buku
- **Fields**: Kode Buku (Auto/Manual), ISBN, Judul Buku, Pengarang, Penerbit, Tahun Terbit, Kategori (Dewey Decimal Classification), Rak Penyimpanan, Stok/Jumlah Exemplar, Deskripsi/Sinopsis, Foto Cover.
- **Features**: Search multi-field, Filter Kategori & Rak, Pagination, Upload Cover Image, Import Data Buku dari Excel, Export Data Buku ke Excel.

### 4. Master Data Anggota Siswa
- **Fields**: NIS (Nomor Induk Siswa), Nama Lengkap Siswa, Kelas (1-6), Jenis Kelamin, Alamat, Nama Orang Tua/Wali, Nomor HP Orang Tua, Status (Aktif/Nonaktif).
- **Features**: Search NIS/Nama/Kelas, Filter Status & Kelas, Quick Toggle Status, Export Excel.

### 5. Buku Tamu (Visitor Log)
- **Fields**: Nomor Urut, Nama Pengunjung, Asal Instansi (Siswa/Guru/Wali Kelas/Tamu Luar), Keperluan (Membaca, Meminjam Buku, Outing Class, dll), Kesan & Pesan, Keterangan, Tanggal, Jam Masuk.
- **Features**: Pencarian tanggal/nama, Export Excel Data Pengunjung, Cetak Ringkasan Buku Tamu.

### 6. Transaksi Peminjaman Buku
- **Fields**: Nomor Transaksi Auto-generated (`TRX-YYYYMMDD-XXXX`), Pilihan Anggota (Searchable), Pilihan Buku (Searchable), Tanggal Pinjam, Tanggal Harus Kembali (Default 7 hari), Status (`Dipinjam`, `Dikembalikan`, `Terlambat`).
- **Aturan Bisnis (Business Logic)**:
  - Saat transaksi peminjaman berhasil disimpan, **Stok Buku berkurang otomatis (-1)**.
  - Validasi sisa stok buku > 0.
  - Batas maksimal peminjaman aktif per anggota (misal: maks 2 buku).

### 7. Transaksi Pengembalian Buku
- **Fields**: Transaksi Peminjaman Referensi, Tanggal Pengembalian Aktual, Kondisi Buku (`Baik`, `Rusak Ringan`, `Rusak Berat`), Catatan/Denda (jika ada).
- **Aturan Bisnis (Business Logic)**:
  - Hitung durasi keterlambatan otomatis (Hari Keterlambatan = Tanggal Kembali - Tanggal Harus Kembali).
  - Status peminjaman berubah menjadi `Dikembalikan`.
  - **Stok Buku bertambah otomatis (+1)**.
  - Catat riwayat kondisi buku saat dikembalikan.

### 8. Laporan & Analytics
- **Jenis Laporan**:
  1. Laporan Pengunjung & Buku Tamu
  2. Laporan Peminjaman Buku
  3. Laporan Pengembalian & Kondisi Buku
  4. Laporan Statistik Buku Terpopuler & Kategori Terfavorit
- **Fitur Laporan**: Filter Periode (Rentang Tanggal/Bulan/Tahun), Export ke PDF (Siap Cetak dengan Kop Sekolah), Export ke Excel (`.xlsx`).

### 9. Manajemen Galeri Activities
- **Fields**: Judul Foto/Kegiatan, Kategori (`Literasi`, `Perpustakaan`, `Outing Class`, `Lomba`), Deskripsi, File Image Preview & Storage.

### 10. Pengaturan Sistem (Settings Management)
- **Konfigurasi Dynamic Content**: Logo Sekolah, Banner Hero Landing Page, Profil Sekolah & Visi Misi, Link Embed Google Maps, Email Gmail Resmi, Link Google Spreadsheet / Dokumentasi pendukung.

---

## Non-Functional Requirements
- **Performance**: Waktu muat halaman < 3 detik (Inertia.js SPA experience).
- **Security**: CSRF Protection, Password Hashing via Bcrypt, Role Middleware Guards, Sanitasi Input.
- **Usability & UX**: Layout responsif (Desktop, Tablet, Mobile), Font Poppins, palet warna sekolah yang ceria & bersih (Blue `#2563EB`, Green `#10B981`, Yellow `#F59E0B`, Clean White/Slate).
