<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\GuestBookController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/buku-tamu/simpan', [LandingController::class, 'storeGuest'])->name('landing.guest-book.store');

Route::get('/files-media/{path}', function ($path) {
    $cleanPath = str_replace('..', '', $path);
    $cleanPath = ltrim($cleanPath, '/');
    
    // Check in public/uploads first
    $publicUploadPath = public_path('uploads/' . $cleanPath);
    if (file_exists($publicUploadPath) && is_file($publicUploadPath)) {
        $mimeType = @mime_content_type($publicUploadPath) ?: 'image/jpeg';
        return response(file_get_contents($publicUploadPath), 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    // Check in storage/app/public
    $storagePath = storage_path('app/public/' . $cleanPath);
    if (file_exists($storagePath) && is_file($storagePath)) {
        $mimeType = @mime_content_type($storagePath) ?: 'image/jpeg';
        return response(file_get_contents($storagePath), 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    abort(404);
})->where('path', '.*');

/*
|--------------------------------------------------------------------------
| Authenticated Dashboard & Service Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Executive Dashboard (Admin, Pustakawan, Guru, Kepala Sekolah)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reports & Analytics (All authenticated staff & leadership)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');

    // Master Books (Read for all, Create/Edit/Delete for Admin & Pustakawan)
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/export-excel', [BookController::class, 'exportExcel'])->name('books.export-excel');

    Route::middleware(['role:Admin,Pustakawan'])->group(function () {
        Route::post('/books', [BookController::class, 'store'])->name('books.store');
        Route::post('/books/{book}/update', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
        Route::post('/books/import-excel', [BookController::class, 'importExcel'])->name('books.import-excel');
    });

    // Data Anggota
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/export-excel', [MemberController::class, 'exportExcel'])->name('members.export-excel');

    Route::middleware(['role:Admin,Pustakawan'])->group(function () {
        Route::post('/members', [MemberController::class, 'store'])->name('members.store');
        Route::patch('/members/{member}', [MemberController::class, 'update'])->name('members.update');
        Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
    });

    // Buku Tamu (Visitor Log Management)
    Route::get('/guest-books', [GuestBookController::class, 'index'])->name('guest-books.index');
    Route::get('/guest-books/export-excel', [GuestBookController::class, 'exportExcel'])->name('guest-books.export-excel');

    Route::middleware(['role:Admin,Pustakawan'])->group(function () {
        Route::post('/guest-books', [GuestBookController::class, 'store'])->name('guest-books.store');
        Route::patch('/guest-books/{guestBook}', [GuestBookController::class, 'update'])->name('guest-books.update');
        Route::delete('/guest-books/{guestBook}', [GuestBookController::class, 'destroy'])->name('guest-books.destroy');
    });

    // Circulation: Borrowing (Peminjaman)
    Route::middleware(['role:Admin,Pustakawan'])->group(function () {
        Route::get('/borrowings', [BorrowingController::class, 'index'])->name('borrowings.index');
        Route::post('/borrowings', [BorrowingController::class, 'store'])->name('borrowings.store');
        Route::delete('/borrowings/{borrowing}', [BorrowingController::class, 'destroy'])->name('borrowings.destroy');
        Route::get('/borrowings/export-excel', [BorrowingController::class, 'exportExcel'])->name('borrowings.export-excel');

        // Circulation: Return (Pengembalian)
        Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
        Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
        Route::get('/returns/export-excel', [ReturnController::class, 'exportExcel'])->name('returns.export-excel');
    });

    // Galleries (Galeri Kegiatan)
    Route::get('/galleries', [GalleryController::class, 'index'])->name('galleries.index');

    Route::middleware(['role:Admin,Pustakawan'])->group(function () {
        Route::post('/galleries', [GalleryController::class, 'store'])->name('galleries.store');
        Route::match(['put', 'post'], '/galleries/{gallery}', [GalleryController::class, 'update'])->name('galleries.update');
        Route::post('/galleries/{gallery}/update', [GalleryController::class, 'update']);
        Route::delete('/galleries/{gallery}', [GalleryController::class, 'destroy'])->name('galleries.destroy');
    });

    // Settings (Admin Only)
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';
