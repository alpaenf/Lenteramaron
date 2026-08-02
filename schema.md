# Database Schema Specification

## Project: LENTERA ILMU - SD Negeri 02 Maron

---

## Entity Relationship Diagram (Conceptual)

```
[roles] 1 --- * [users]
[book_categories] 1 --- * [books]
[books] 1 --- * [borrowings]
[members] 1 --- * [borrowings]
[borrowings] 1 --- 1 [returns]
[members] 1 --- * [returns]
[books] 1 --- * [returns]
[galleries] (standalone)
[guest_books] (standalone)
[settings] (key-value config)
```

---

## Tables & Fields Specification

### 1. `users`
Sistem pengguna internal (Staff & Management).

| Field Name | Type | Constraints | Description |
|---|---|---|---|
| `id` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | User ID |
| `name` | VARCHAR(255) | NOT NULL | Nama Lengkap User |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | Email Login |
| `password` | VARCHAR(255) | NOT NULL | Hashed Password |
| `role` | ENUM | NOT NULL, DEFAULT 'Pustakawan' | Values: `'Admin'`, `'Pustakawan'`, `'Guru'`, `'Kepala Sekolah'` |
| `avatar` | VARCHAR(255) | NULLABLE | Path Foto Profil |
| `remember_token` | VARCHAR(100) | NULLABLE | Remember Me Token |
| `created_at` | TIMESTAMP | NULLABLE | Timestamp Dibuat |
| `updated_at` | TIMESTAMP | NULLABLE | Timestamp Perubahan |

### 2. `book_categories`
Kategori Klasifikasi Desimal Dewey (DDC).

| Field Name | Type | Constraints | Description |
|---|---|---|---|
| `id` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Category ID |
| `code` | VARCHAR(20) | UNIQUE, NOT NULL | Kode DDC (e.g. `000`, `300`, `500`) |
| `name` | VARCHAR(255) | NOT NULL | Nama Kategori (e.g. Sains & Matematika) |
| `description` | TEXT | NULLABLE | Deskripsi Singkat |
| `created_at` | TIMESTAMP | NULLABLE | Timestamp |
| `updated_at` | TIMESTAMP | NULLABLE | Timestamp |

### 3. `books`
Master koleksi buku perpustakaan.

| Field Name | Type | Constraints | Description |
|---|---|---|---|
| `id` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Book ID |
| `book_code` | VARCHAR(50) | UNIQUE, NOT NULL | Kode Buku Internal (e.g. `BK-001`) |
| `isbn` | VARCHAR(30) | NULLABLE | Nomor ISBN |
| `title` | VARCHAR(255) | NOT NULL | Judul Buku |
| `author` | VARCHAR(255) | NOT NULL | Pengarang |
| `publisher` | VARCHAR(255) | NOT NULL | Penerbit |
| `year` | INT | NOT NULL | Tahun Terbit |
| `category_id` | BIGINT | FOREIGN KEY (`book_categories.id`) | Relasi Kategori DDC |
| `shelf` | VARCHAR(50) | NOT NULL | Lokasi Rak (e.g. `Rak A-01`) |
| `stock` | INT | NOT NULL, DEFAULT 0 | Jumlah Stok Saat Ini |
| `cover` | VARCHAR(255) | NULLABLE | Path Upload Sampul Buku |
| `description` | TEXT | NULLABLE | Sinopsis / Ringkasan Buku |
| `created_at` | TIMESTAMP | NULLABLE | Timestamp |
| `updated_at` | TIMESTAMP | NULLABLE | Timestamp |

### 4. `members`
Master data anggota perpustakaan (Siswa).

| Field Name | Type | Constraints | Description |
|---|---|---|---|
| `id` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Member ID |
| `nis` | VARCHAR(30) | UNIQUE, NOT NULL | Nomor Induk Siswa |
| `name` | VARCHAR(255) | NOT NULL | Nama Lengkap Siswa |
| `class_name` | VARCHAR(20) | NOT NULL | Kelas (e.g. `Kelas 1A`, `Kelas 5B`) |
| `gender` | ENUM('L', 'P') | NOT NULL | Jenis Kelamin |
| `address` | TEXT | NULLABLE | Alamat Tempat Tinggal |
| `parent_name` | VARCHAR(255) | NULLABLE | Nama Orang Tua / Wali |
| `parent_phone` | VARCHAR(30) | NULLABLE | Nomor HP/WhatsApp Orang Tua |
| `status` | ENUM('Aktif', 'Nonaktif') | DEFAULT 'Aktif' | Status Keanggotaan |
| `created_at` | TIMESTAMP | NULLABLE | Timestamp |
| `updated_at` | TIMESTAMP | NULLABLE | Timestamp |

### 5. `guest_books`
Catatan buku tamu digital perpustakaan.

| Field Name | Type | Constraints | Description |
|---|---|---|---|
| `id` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Guest Book ID |
| `visitor_no` | VARCHAR(50) | NOT NULL | Nomor Urut Pengunjung |
| `name` | VARCHAR(255) | NOT NULL | Nama Pengunjung |
| `institution` | VARCHAR(255) | NOT NULL | Asal Instansi / Kelas / Profesi |
| `purpose` | VARCHAR(255) | NOT NULL | Keperluan (Membaca, Pinjam Buku, dll) |
| `feedback` | TEXT | NULLABLE | Kesan dan Pesan |
| `note` | TEXT | NULLABLE | Keterangan Tambahan |
| `date` | DATE | NOT NULL | Tanggal Kunjungan |
| `time` | TIME | NOT NULL | Waktu Kunjungan |
| `created_at` | TIMESTAMP | NULLABLE | Timestamp |
| `updated_at` | TIMESTAMP | NULLABLE | Timestamp |

### 6. `borrowings`
Transaksi peminjaman buku.

| Field Name | Type | Constraints | Description |
|---|---|---|---|
| `id` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Borrowing ID |
| `transaction_no` | VARCHAR(50) | UNIQUE, NOT NULL | Kode Transaksi (`TRX-YYYYMMDD-XXX`) |
| `member_id` | BIGINT | FOREIGN KEY (`members.id`) | ID Anggota |
| `book_id` | BIGINT | FOREIGN KEY (`books.id`) | ID Buku |
| `borrow_date` | DATE | NOT NULL | Tanggal Pinjam |
| `due_date` | DATE | NOT NULL | Tanggal Harus Kembali |
| `return_date` | DATE | NULLABLE | Tanggal Aktual Pengembalian |
| `status` | ENUM | NOT NULL, DEFAULT 'Dipinjam' | Values: `'Dipinjam'`, `'Dikembalikan'`, `'Terlambat'` |
| `notes` | TEXT | NULLABLE | Catatan Transaksi |
| `created_at` | TIMESTAMP | NULLABLE | Timestamp |
| `updated_at` | TIMESTAMP | NULLABLE | Timestamp |

### 7. `returns`
Catatan detail pengembalian buku.

| Field Name | Type | Constraints | Description |
|---|---|---|---|
| `id` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Return ID |
| `return_no` | VARCHAR(50) | UNIQUE, NOT NULL | Kode Pengembalian (`RET-YYYYMMDD-XXX`) |
| `borrowing_id` | BIGINT | FOREIGN KEY (`borrowings.id`) | ID Transaksi Peminjaman |
| `member_id` | BIGINT | FOREIGN KEY (`members.id`) | ID Anggota |
| `book_id` | BIGINT | FOREIGN KEY (`books.id`) | ID Buku |
| `return_date` | DATE | NOT NULL | Tanggal Pengembalian |
| `condition` | ENUM | NOT NULL, DEFAULT 'Baik' | Values: `'Baik'`, `'Rusak Ringan'`, `'Rusak Berat'` |
| `late_days` | INT | DEFAULT 0 | Jumlah Hari Terlambat |
| `note` | TEXT | NULLABLE | Catatan Pengembalian / Denda |
| `created_at` | TIMESTAMP | NULLABLE | Timestamp |
| `updated_at` | TIMESTAMP | NULLABLE | Timestamp |

### 8. `galleries`
Galeri foto kegiatan & perpustakaan.

| Field Name | Type | Constraints | Description |
|---|---|---|---|
| `id` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Gallery ID |
| `title` | VARCHAR(255) | NOT NULL | Judul Kegiatan |
| `category` | ENUM | NOT NULL | Values: `'Literasi'`, `'Perpustakaan'`, `'Outing Class'`, `'Lomba'` |
| `image_path` | VARCHAR(255) | NOT NULL | File Path Upload Gambar |
| `description` | TEXT | NULLABLE | Deskripsi Singkat Kegiatan |
| `created_at` | TIMESTAMP | NULLABLE | Timestamp |
| `updated_at` | TIMESTAMP | NULLABLE | Timestamp |

### 9. `settings`
Key-Value store untuk konfigurasi dynamic halaman & profil sekolah.

| Field Name | Type | Constraints | Description |
|---|---|---|---|
| `id` | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Setting ID |
| `key` | VARCHAR(100) | UNIQUE, NOT NULL | Setting Key (e.g. `school_name`, `logo`, `gmaps_url`) |
| `value` | LONGTEXT | NULLABLE | Setting Value (Text / JSON / Path) |
| `created_at` | TIMESTAMP | NULLABLE | Timestamp |
| `updated_at` | TIMESTAMP | NULLABLE | Timestamp |

---

## Indexes & Performance Constraints
- Foreign key constraints on `books.category_id`, `borrowings.member_id`, `borrowings.book_id`, `returns.borrowing_id`.
- Composite Index on `borrowings(status, due_date)` for fast overdue query lookups.
- Fulltext/Index on `books(title, author, book_code, isbn)` for rapid search performance.
