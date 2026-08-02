# Project Roadmap & Implementation Schedule

## Project: LENTERA ILMU - SD Negeri 02 Maron

---

## Phase 1: Environment Setup & Foundation (Milestone 1)
- [ ] Initialize Laravel 13 project dependencies (Composer & NPM packages).
- [ ] Install `@inertiajs/vue3`, `@vitejs/plugin-vue`, `tailwindcss`, `lucide-vue-next`, `chart.js`, `vue-chartjs`.
- [ ] Install Laravel packages: `laravel/breeze` (Inertia Vue), `barryvdh/laravel-dompdf`, `maatwebsite/excel`.
- [ ] Configure database connection (`.env` MySQL configuration).
- [ ] Create core UI Layouts (`GuestLayout.vue`, `AuthenticatedLayout.vue`).

---

## Phase 2: Database Schema, Models & Seeders (Milestone 2)
- [ ] Create Database Migrations for 10 tables:
  - `users`, `book_categories`, `books`, `members`, `guest_books`, `borrowings`, `returns`, `galleries`, `settings`.
- [ ] Create Eloquent Models with proper relationships (`Book`, `BookCategory`, `Member`, `Borrowing`, `Return`, `GuestBook`, `Gallery`, `Setting`).
- [ ] Create Database Seeders:
  - `UserSeeder` (Admin, Pustakawan, Guru, Kepala Sekolah).
  - `CategorySeeder` (Dewey Decimal Classification).
  - `BookSeeder`, `MemberSeeder`, `GallerySeeder`, `SettingSeeder`.

---

## Phase 3: Core Master Data Modules (Milestone 3)
- [ ] **Master Buku Module**:
  - CRUD operations (Index, Create, Edit, Delete, Show).
  - Book cover upload & storage linking.
  - Search, DDC category filter, shelf filter, pagination.
  - Excel Import & Export features (`BooksImport`, `BooksExport`).
- [ ] **Master Anggota Module**:
  - Student member CRUD (NIS, Nama, Kelas, Ortu).
  - Search, class filter, status toggle.
  - Excel Export.
- [ ] **Buku Tamu Module**:
  - Digital visitor submission form.
  - Visitor logs management table (Search by date/name).
  - Export to Excel & Print view.

---

## Phase 4: Circulation Transactions (Milestone 4)
- [ ] **Peminjaman Buku (Borrowing Module)**:
  - Create transaction form (Auto-generated reference number, Member search dropdown, Book search dropdown).
  - Stock validation & automatic stock deduction (-1).
  - List of active borrowings with overdue highlight tags.
- [ ] **Pengembalian Buku (Return Module)**:
  - Return processing dialog.
  - Automatic calculation of overdue days (`return_date` vs `due_date`).
  - Condition logging (`Baik`, `Rusak Ringan`, `Rusak Berat`).
  - Automatic stock restoration (+1) & status update to `Dikembalikan`.

---

## Phase 5: Reports & Analytics (Milestone 5)
- [ ] **Dashboard Analytics**:
  - Real-time statistics counters.
  - Line chart: Monthly borrow & return trends (Chart.js).
  - Pie chart: Book distribution by DDC Category.
  - Popular books list & recent activity feed.
- [ ] **Reports Module**:
  - Filterable reports for Visitors, Borrowings, Returns, Popular Books.
  - Export to PDF (DomPDF with official school header/kop).
  - Export to Excel (`.xlsx`).

---

## Phase 6: Public Portal, Settings & Polishing (Milestone 6)
- [ ] **Landing Page**:
  - Hero banner, School & Library Profile, Vision & Mission, Live Counters, Book Catalog preview, Gallery, Contact & Google Maps embed.
- [ ] **Galeri Activities**:
  - Photo gallery CRUD with category tags (`Literasi`, `Perpustakaan`, `Outing Class`, `Lomba`).
- [ ] **Pengaturan (Settings)**:
  - Configuration form for Logo, Hero Banner, School Info, Google Maps link, Email, Google Spreadsheet reference link.
- [ ] **Testing & Polish**:
  - RBAC permission tests for all 4 roles.
  - Final UI responsiveness check (Desktop, Tablet, Mobile).
