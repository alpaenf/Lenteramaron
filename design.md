# UI/UX Design System Specification

## Project: LENTERA ILMU - SD Negeri 02 Maron

---

## Design System & Principles

1. **Theme Atmosphere**:
   - **School Library Modern & Child Friendly**: Ceria, hangat, profesional, ramah anak, mudah dinavigasi oleh pustakawan maupun tamu sekolah.
2. **Typography**:
   - **Font Primary**: `Poppins` (Google Font)
   - Weights: `Regular (400)`, `Medium (500)`, `SemiBold (600)`, `Bold (700)`.
3. **Card & Border Aesthetic**:
   - **Flat Design + Soft Shadows**: `rounded-2xl`, `shadow-sm hover:shadow-md transition-all duration-300`.
   - Clear contrast between background and cards.

---

## Color Palette

| Token Name | Color Code (HEX) | Tailind Class Equivalent | Usage Context |
|---|---|---|---|
| **Primary Blue** | `#2563EB` | `bg-blue-600`, `text-blue-600` | Primary buttons, active sidebar items, header accents |
| **Primary Soft Blue** | `#EFF6FF` | `bg-blue-50` | Active menu background, subtle card badges |
| **Secondary Emerald** | `#10B981` | `bg-emerald-500`, `text-emerald-500` | Return status `Dikembalikan`, Success alerts, Green stats card |
| **Warm Yellow** | `#F59E0B` | `bg-amber-500`, `text-amber-500` | Overdue warning `Terlambat`, Warning badges, Warm highlights |
| **Rose Alert** | `#EF4444` | `bg-rose-500`, `text-rose-500` | Book damage state `Rusak Berat`, Delete modals, Error alerts |
| **Neutral Slate Dark** | `#1E293B` | `text-slate-800`, `bg-slate-900` | Dark typography text, Dark mode option elements |
| **Neutral Background** | `#F8FAFC` | `bg-slate-50` | Main application backdrop |
| **Pure White** | `#FFFFFF` | `bg-white` | Form cards, tables, modal containers |

---

## UI Components & Visual Layout Patterns

### 1. Public Landing Page (`GuestLayout.vue`)
- **Hero Section**:
  - Gradient background: `from-blue-600 to-indigo-700` with subtle geometric reading illustration overlay.
  - Large friendly heading with Poppins font: *"LENTERA ILMU - Perpustakaan SD Negeri 02 Maron"*.
  - Call to Action (CTA) buttons: *"Lihat Katalog Buku"*, *"Isi Buku Tamu"*, dan *"Login Petugas"*.
- **Live Counters (Stats Widget)**:
  - 4 rounded cards with bright soft icon containers:
    - 📚 Total Koleksi Buku
    - 👨‍🎓 Anggota Siswa Aktif
    - 🔄 Peminjaman Bulan Ini
    - 👥 Total Pengunjung Perpustakaan
- **Interactive Book Catalog Preview**:
  - Filterable by DDC Category pills (`Sains`, `Cerita Rakyat`, `Karya Umum`, `Bahasa`).
  - Book Card Component: Cover image container (`aspect-[3/4]`), Title, Author, Category Pill, Stock Badge (`Tersedia (5)` in Emerald / `Habis (0)` in Rose).
- **Digital Visitor Form Section**:
  - Clean card form for quick input: Nama, Instansi/Kelas, Keperluan, Kesan & Pesan.
- **Google Maps Location Footer**:
  - Full-width dark slate footer (`bg-slate-900`) containing embedded Google Maps iframe, contact info, operating hours, and social/spreadsheet link references.

### 2. Dashboard Layout (`AuthenticatedLayout.vue`)
- **Left Sidebar Navigation**:
  - Logo section: Custom emblem LENTERA ILMU + SD Negeri 02 Maron.
  - Collapsible / Fixed modern sidebar with Lucide Vue icons:
    - 📊 Dashboard
    - 📚 Master Buku
    - 👥 Data Anggota
    - 📖 Buku Tamu
    - 🔄 Transaksi Peminjaman
    - ↩️ Pengembalian Buku
    - 📈 Laporan
    - 🖼️ Galeri Kegiatan
    - ⚙️ Pengaturan
- **Top Bar Header**:
  - Quick Search bar for book lookup.
  - Quick action buttons (Pinjam Buku Quick Modal, Isi Buku Tamu).
  - User Avatar dropdown with Role Badge (`[Admin]`, `[Pustakawan]`, `[Guru]`, `[Kepala Sekolah]`).
- **Dashboard Analytics Widgets**:
  - **Chart 1**: Line Chart - Tren Peminjaman & Pengembalian 12 Bulan terakhir.
  - **Chart 2**: Pie / Doughnut Chart - Distribusi Buku per Kategori DDC.
  - **Buku Terpopuler Table**: List top 5 books with borrowing frequency counts.
  - **Recent Activity Feed**: Timeline list of recent borrow & return transactions.

---

## Form Controls & Interactive States

- **Form Fields**:
  - `rounded-xl border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all`.
  - Clear floating or stacked labels with required red asterisk (`*`).
- **Tables**:
  - Styled headers with `bg-slate-100 text-slate-700 font-semibold text-xs uppercase tracking-wider`.
  - Alternating subtle hover states (`hover:bg-slate-50/80 transition-colors`).
  - Integrated Pagination controls with item count indicators.
- **Modal Dialogs**:
  - Centered overlay backdrop blur (`backdrop-blur-sm bg-slate-900/40`).
  - Animated slide up & fade transition.
