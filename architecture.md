# System Architecture Document

## Project: LENTERA ILMU - SD Negeri 02 Maron

---

## High-Level Architecture Overview

Aplikasi **LENTERA ILMU** dibangun menggunakan pola **Single Page Application (SPA)** hybrid berbasis **Laravel 13** dan **Vue 3** yang dihubungkan tanpa pembatas API terpisah melalui **Inertia.js**.

```
+-----------------------------------------------------------------------+
|                             USER BROWSER                              |
|   Vue 3 Components (Composition API) + Tailwind CSS + Chart.js        |
+-----------------------------------------------------------------------+
                                   |
                       Inertia.js Protocol (JSON)
                                   |
+-----------------------------------------------------------------------+
|                          LARAVEL 13 BACKEND                           |
|  +-----------------------------------------------------------------+  |
|  | Web & Auth Routes (Laravel Breeze)                              |  |
|  +-----------------------------------------------------------------+  |
|  | Role-Based Access Control Middleware (Admin, Pustakawan, Guru...)  |  |
|  +-----------------------------------------------------------------+  |
|  | Controllers (Book, Member, Borrowing, Return, Report, etc.)      |  |
|  +-----------------------------------------------------------------+  |
|  | Business Logic & Domain Services (Stock management, Late calc)  |  |
|  +-----------------------------------------------------------------+  |
|  | Eloquent ORM (Models & Relationships)                           |  |
|  +-----------------------------------------------------------------+  |
+-----------------------------------------------------------------------+
                                   |
                            PDO / SQL Driver
                                   |
+-----------------------------------------------------------------------+
|                             MYSQL DATABASE                            |
|  (users, books, book_categories, members, borrowings, returns, etc.)  |
+-----------------------------------------------------------------------+
```

---

## Component Architecture

### 1. Frontend Layers (Resources/js)

- **`js/app.js`**: Application entry point, initializes Vue 3, Inertia App, Ziggy routes, and global plugins.
- **`js/Layouts/`**:
  - `GuestLayout.vue`: Public layout containing Navbar, Hero Banner, Dynamic Sections, Google Maps embed, Footer.
  - `AuthenticatedLayout.vue`: Staff dashboard layout featuring Sidebar navigation, Top Header with Notifications & User Profile menu, Breadcrumbs.
- **`js/Pages/`**:
  - `Landing/`: Landing page sections (Public catalog, Visitor Guest Book form, Gallery).
  - `Dashboard/`: Role-tailored dashboards with Chart.js statistics.
  - `Books/`: Index table, Form dialogs, Import/Export modal.
  - `Members/`: Student directory management.
  - `GuestBooks/`: Visitor logs table and print views.
  - `Borrowings/`: Borrow transaction forms & status overview.
  - `Returns/`: Return processing modal & late penalty calculator.
  - `Reports/`: Date-filtered reporting interface with PDF & Excel download triggers.
  - `Galleries/`: Media gallery card grid & upload modal.
  - `Settings/`: System configuration forms.
- **`js/Components/`**: Reusable UI primitives (Buttons, Cards, Inputs, Modals, Badges, DataTable, Chart Containers).

### 2. Backend Layers (App/)

- **`App\Http\Controllers`**:
  - Receive Inertia requests, call service logic, return Inertia responses with Vue page bindings and props (`Inertia::render()`).
- **`App\Http\Middleware`**:
  - `HandleInertiaRequests`: Shares global flash messages, authenticated user object, permissions, and school setting configs.
  - `CheckRole`: Validates user role (`Admin`, `Pustakawan`, `Guru`, `Kepala Sekolah`).
- **`App\Models`**:
  - Eloquent models representing domain entities with casting, scopes, and relations.
- **`App\Imports` & `App\Exports`**:
  - Laravel Excel classes for batch importing books and exporting reports.
- **`App\Services`**:
  - `BorrowingService`: Encapsulates transaction logic, stock updates inside `DB::transaction()`.

---

## Directory Structure

```
Lenteramaron/
├── app/
│   ├── Exports/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── BookController.php
│   │   │   ├── BorrowingController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── GalleryController.php
│   │   │   ├── GuestBookController.php
│   │   │   ├── LandingController.php
│   │   │   ├── MemberController.php
│   │   │   ├── ReportController.php
│   │   │   ├── ReturnController.php
│   │   │   └── SettingController.php
│   │   └── Middleware/
│   │       ├── CheckRole.php
│   │       └── HandleInertiaRequests.php
│   ├── Imports/
│   ├── Models/
│   └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── Components/
│   │   ├── Layouts/
│   │   ├── Pages/
│   │   └── app.js
│   └── views/
│       ├── app.blade.php
│       └── pdf/
│           ├── borrowing_report.blade.php
│           ├── guest_book_report.blade.php
│           └── return_report.blade.php
├── routes/
│   ├── auth.php
│   └── web.php
├── prd.md
├── architecture.md
├── schema.md
├── design.md
├── rules.md
├── copilot-instructions.md
├── roadmap.md
└── features.md
```

---

## Data Flow Pipeline

### 1. Peminjaman Buku (Borrow Transaction Flow)
1. Staff selects Member & Book in Vue 3 frontend form (`Borrowings/Create.vue`).
2. Request sent via Inertia POST to `BorrowingController@store`.
3. `BorrowingService` checks:
   - Book stock > 0?
   - Member status == 'Aktif'?
4. Executes inside DB Transaction:
   - Creates `borrowings` record with status `Dipinjam`.
   - Decrements `books.stock` by 1.
5. Returns Inertia redirect back with success flash message.

### 2. Pengembalian Buku (Return Flow)
1. Staff selects active borrowing ID in `Returns/Create.vue` & inputs book condition (`Baik`/`Rusak Ringan`/`Rusak Berat`).
2. Inertia POST to `ReturnController@store`.
3. Controller calculates late days (`return_date` vs `due_date`).
4. Executes inside DB Transaction:
   - Creates `returns` record.
   - Updates `borrowings.status` to `Dikembalikan`.
   - Increments `books.stock` by 1.
5. Returns redirect with flash notice.

---

## Security & Session Management

- **Authentication**: Laravel Breeze session-based authentication.
- **Role Guards**: Custom `CheckRole` middleware (`role:Admin,Pustakawan`).
- **CSRF Token**: Automatically managed by Inertia.js and Laravel Axios headers.
- **File Uploads**: Validated image mimetypes (`jpeg,png,webp`), stored in `storage/app/public/` using Laravel Storage disk.
