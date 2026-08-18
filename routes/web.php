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
use App\Http\Controllers\LiteraSearchController;
use App\Http\Controllers\ResearchWorkspaceController;
use App\Http\Controllers\MetadataEnrichmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/buku-tamu/simpan', [LandingController::class, 'storeGuest'])->name('landing.guest-book.store');

// LITERA Public Search & API
Route::get('/litera/search', [LiteraSearchController::class, 'index'])->name('litera.search');
Route::post('/litera/api/search', [LiteraSearchController::class, 'search'])->name('litera.api.search');
Route::post('/litera/api/explain', [LiteraSearchController::class, 'explain'])->name('litera.api.explain');
Route::post('/litera/api/analyze', [LiteraSearchController::class, 'analyze'])->name('litera.api.analyze');
Route::post('/litera/api/path', [LiteraSearchController::class, 'generatePath'])->name('litera.api.path');

Route::get('/files-media/{path}', function ($path) {
    $cleanPath = str_replace('..', '', $path);
    $cleanPath = ltrim($cleanPath, '/');
    
    // Strip redundant leading "uploads/" if passed in path
    $subPath = preg_replace('#^uploads/#', '', $cleanPath);

    // 1. Check in public/uploads/ (with subPath or cleanPath)
    foreach ([$cleanPath, $subPath, 'settings/' . $subPath, 'books/' . $subPath, 'galleries/' . $subPath] as $p) {
        $publicUploadPath = public_path('uploads/' . $p);
        if (file_exists($publicUploadPath) && is_file($publicUploadPath)) {
            $mimeType = @mime_content_type($publicUploadPath) ?: 'image/jpeg';
            return response(file_get_contents($publicUploadPath), 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
        
        $directPublic = public_path($p);
        if (file_exists($directPublic) && is_file($directPublic)) {
            $mimeType = @mime_content_type($directPublic) ?: 'image/jpeg';
            return response(file_get_contents($directPublic), 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }

    // 2. Check in storage/app/public
    foreach ([$cleanPath, $subPath] as $p) {
        $storagePath = storage_path('app/public/' . $p);
        if (file_exists($storagePath) && is_file($storagePath)) {
            $mimeType = @mime_content_type($storagePath) ?: 'image/jpeg';
            return response(file_get_contents($storagePath), 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }

    abort(404);
})->where('path', '.*');

Route::get('/uploads/{path}', function ($path) {
    $cleanPath = str_replace('..', '', $path);
    $cleanPath = ltrim($cleanPath, '/');
    $subPath = preg_replace('#^uploads/#', '', $cleanPath);

    foreach ([$cleanPath, $subPath, 'settings/' . $subPath, 'books/' . $subPath, 'galleries/' . $subPath] as $p) {
        $full = public_path('uploads/' . $p);
        if (file_exists($full) && is_file($full)) {
            $mimeType = @mime_content_type($full) ?: 'image/jpeg';
            return response(file_get_contents($full), 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
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

    // LITERA Research Workspace (Authenticated Users)
    Route::get('/litera/workspace', [ResearchWorkspaceController::class, 'index'])->name('litera.workspace');
    Route::post('/litera/workspace/saved', [ResearchWorkspaceController::class, 'storeSavedSource'])->name('litera.workspace.saved.store');
    Route::patch('/litera/workspace/saved/{savedSource}', [ResearchWorkspaceController::class, 'updateSavedSource'])->name('litera.workspace.saved.update');
    Route::delete('/litera/workspace/saved/{savedSource}', [ResearchWorkspaceController::class, 'deleteSavedSource'])->name('litera.workspace.saved.destroy');
    Route::post('/litera/workspace/topics', [ResearchWorkspaceController::class, 'storeTopic'])->name('litera.workspace.topics.store');
    Route::delete('/litera/workspace/topics/{topic}', [ResearchWorkspaceController::class, 'deleteTopic'])->name('litera.workspace.topics.destroy');

    // ISBN & Web Metadata Auto-Enrichment (Admin & Pustakawan)
    Route::post('/books/enrich-by-isbn', [MetadataEnrichmentController::class, 'enrichByIsbn'])->name('books.enrich-by-isbn');
    Route::post('/books/search-web', [MetadataEnrichmentController::class, 'searchWeb'])->name('books.search-web');
    Route::post('/books/batch-import-isbn', [MetadataEnrichmentController::class, 'batchImportIsbn'])->name('books.batch-import-isbn');

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
        Route::post('/books/batch-import-json', [BookController::class, 'batchImportJson'])->name('books.batch-import-json');
    });

    // Master Reference Books (Read for all, Create/Edit/Delete for Admin & Pustakawan)
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/books/export-excel', [BookController::class, 'exportExcel'])->name('books.export-excel');

    Route::middleware(['role:Admin,Pustakawan'])->group(function () {
        Route::post('/books', [BookController::class, 'store'])->name('books.store');
        Route::post('/books/{book}/update', [BookController::class, 'update'])->name('books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
        Route::post('/books/import-excel', [BookController::class, 'importExcel'])->name('books.import-excel');
    });

    // Galleries (Galeri Activities & Research Media)
    Route::get('/galleries', [GalleryController::class, 'index'])->name('galleries.index');

    Route::middleware(['role:Admin,Pustakawan'])->group(function () {
        Route::post('/galleries', [GalleryController::class, 'store'])->name('galleries.store');
        Route::match(['put', 'post'], '/galleries/{gallery}', [GalleryController::class, 'update'])->name('galleries.update');
        Route::post('/galleries/{gallery}/update', [GalleryController::class, 'update']);
        Route::delete('/galleries/{gallery}', [GalleryController::class, 'destroy'])->name('galleries.destroy');
    });

    // User Management & Settings (Admin Only)
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';
