# Comprehensive Feature Matrix

## Project: LENTERA ILMU - SD Negeri 02 Maron

---

## Access Control Matrix (Role-Based Permissions)

| Feature / Module | Admin | Pustakawan | Guru | Kepala Sekolah | Siswa (Public) |
|---|:---:|:---:|:---:|:---:|:---:|
| **Landing Page & Public Catalog** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Isi Buku Tamu Digital** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Dashboard Executive Analytics** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Master Buku (View)** | ✅ | ✅ | ✅ | ✅ | ✅ (Public Catalog) |
| **Master Buku (Create/Edit/Delete)** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Master Buku (Import/Export Excel)** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Master Anggota (View & Search)** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Master Anggota (Create/Edit/Delete)** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Buku Tamu (View & Export)** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Peminjaman Buku (Create & View)** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Pengembalian Buku (Process Return)** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Laporan (View & Download PDF/Excel)** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Galeri Kegiatan (Create/Edit/Delete)** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Pengaturan Sistem (School & Web Config)** | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## Detailed Module Features

### 1. Public Portal & Landing Page
- **Hero Carousel Banner**: Visual banner ramah anak dengan pesan literasi.
- **Profil & Visi Misi**: Detail profil SD Negeri 02 Maron dan fasilitas perpustakaan.
- **Statistik Publik**: Real-time counter total koleksi, jumlah peminjaman, anggota aktif, dan pengunjung.
- **Katalog Buku Publik**: Pencarian cepat buku berdasarkan judul/pengarang/kategori tanpa perlu login.
- **Buku Tamu Digital**: Form pengisian tamu perpustakaan mandiri.
- **Galeri Kegiatan**: Filter foto kegiatan perpustakaan (Literasi, Outing Class, Lomba).
- **Kontak & Map**: Informasi lokasi terintegrasi Google Maps embed.

### 2. Executive Dashboard
- **Counter Cards**: Widget statistik ringkas dengan indikator tren.
- **Grafik Peminjaman vs Pengembalian**: Line chart bulanan (Chart.js).
- **Grafik Kategori DDC**: Pie chart persentase koleksi buku per kategori.
- **Buku Terpopuler Widget**: Daftar 5 buku terlaris dipinjam.
- **Feed Aktivitas Terbaru**: Transaksi peminjaman & pengembalian real-time.

### 3. Master Data Buku
- **Form Buku Complete**: Kode Buku, ISBN, Judul, Pengarang, Penerbit, Tahun, Kategori DDC, Rak, Stok, Deskripsi, Upload Cover.
- **Pencarian & Filter Multi-Kriteria**: Cari berdasarkan kata kunci judul/ISBN/pengarang + filter kategori & rak.
- **Import Data Buku Excel**: Bulk import data buku via file `.xlsx`.
- **Export Data Buku Excel**: Download seluruh katalog buku ke `.xlsx`.

### 4. Master Data Anggota Siswa
- **Manajemen Siswa**: NIS, Nama, Kelas (1-6), Jenis Kelamin, Alamat, Ortu, HP Ortu, Status.
- **Filter Status & Kelas**: Saring anggota berdasarkan kelas dan status aktif/nonaktif.
- **Export Excel Data Anggota**: Unduh daftar siswa anggota ke Excel.

### 5. Buku Tamu (Visitor Log)
- **Catatan Kunjungan**: Nama, Instansi/Kelas, Keperluan, Kesan/Pesan, Tanggal, Jam.
- **Export Excel & Cetak**: Download data pengunjung dan versi cetak ramah printer.

### 6. Sirkulasi Peminjaman Buku
- **Input Peminjaman Quick Form**: Pilih Anggota & Buku via Select2/Searchable dropdown.
- **Auto Stock Deduction**: Stok buku otomatis berkurang 1 saat transaksi dibuat.
- **Auto Reference Number**: Format kode transaksi `TRX-YYYYMMDD-XXXX`.
- **Warning System**: Indikator warna Amber untuk peminjaman mendekati/melebihi jatuh tempo.

### 7. Sirkulasi Pengembalian Buku
- **Proses Pengembalian**: Pilih peminjaman aktif, sistem menghitung durasi keterlambatan otomatis.
- **Catatan Kondisi Buku**: Pilihan kondisi (`Baik`, `Rusak Ringan`, `Rusak Berat`).
- **Auto Stock Restoration**: Stok buku otomatis bertambah 1 saat pengembalian diproses.

### 8. Laporan & Export Data
- **Jenis Laporan**:
  1. Laporan Pengunjung / Buku Tamu
  2. Laporan Transaksi Peminjaman
  3. Laporan Transaksi Pengembalian & Kondisi Buku
  4. Laporan Statistik Buku Terpopuler
- **Dual Export Option**: PDF (DomPDF dengan Kop Resmi SD Negeri 02 Maron) & Excel (`.xlsx`).

### 9. Galeri Activities
- **Media Manager**: Upload foto kegiatan literasi, outing class, perpustakaan, dan lomba.
- **Category Filter**: Kategori badge yang rapi.

### 10. Pengaturan Sistem
- **Dynamic Site Branding**: Ganti Logo Sekolah, Hero Banner, Deskripsi Profil, Links Google Maps, Email Gmail, dan Link Google Spreadsheet.
