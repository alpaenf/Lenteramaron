<?php

namespace App\Http\Controllers;

use App\Exports\BooksExport;
use App\Imports\BooksImport;
use App\Models\Book;
use App\Models\BookCategory;
use App\Traits\CompressesImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class BookController extends Controller
{
    use CompressesImage;

    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $query = Book::with('category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('book_code', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $books = $query->latest()->paginate(10)->withQueryString();
        $categories = BookCategory::all();

        return Inertia::render('Books/Index', [
            'books' => $books,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_code'   => 'required|string|max:50|unique:books,book_code',
            'isbn'        => 'nullable|string|max:30',
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:255',
            'publisher'   => 'required|string|max:255',
            'year'        => 'required|integer|min:1900|max:' . (date('Y') + 5),
            'category_id' => 'nullable|exists:book_categories,id',
            'shelf'       => 'nullable|string|max:50',
            'stock'       => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'cover'       => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
            'cover_url'   => 'nullable|string',
        ]);

        if (empty($validated['category_id'])) {
            $firstCategory = BookCategory::first();
            $validated['category_id'] = $firstCategory ? $firstCategory->id : 1;
        }

        if (empty($validated['shelf'])) {
            $validated['shelf'] = 'Rak Referensi';
        }

        if (!isset($validated['stock'])) {
            $validated['stock'] = 1;
        }

        if ($request->hasFile('cover')) {
            $path = $this->compressAndSaveImage($request->file('cover'), 'covers');
            $validated['cover'] = $path;
        } elseif (!empty($request->input('cover_url'))) {
            $validated['cover'] = $request->input('cover_url');
        }

        Book::create($validated);

        return redirect()->route('books.index')->with('success', 'Buku baru berhasil ditambahkan.');
    }

    private function deleteCoverFile(?string $coverPath): void
    {
        if (!$coverPath) return;
        if (str_starts_with($coverPath, 'uploads/')) {
            $full = public_path($coverPath);
            if (file_exists($full)) @unlink($full);
            return;
        }
        if (Storage::disk('public')->exists($coverPath)) {
            Storage::disk('public')->delete($coverPath);
        }
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'book_code' => 'required|string|max:50|unique:books,book_code,' . $book->id,
            'isbn' => 'nullable|string|max:30',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'category_id' => 'required|exists:book_categories,id',
            'shelf' => 'required|string|max:50',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        if ($request->hasFile('cover')) {
            $this->deleteCoverFile($book->cover);
            $path = $this->compressAndSaveImage($request->file('cover'), 'covers');
            $validated['cover'] = $path;
        }

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Data buku berhasil diperbarui.');
    }

    public function destroy(Book $book)
    {
        $this->deleteCoverFile($book->cover);
        $book->delete();

        return redirect()->route('books.index')->with('success', 'Buku berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new BooksExport, 'master_data_buku_lenteramaron.xlsx');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480',
        ]);

        try {
            Excel::import(new BooksImport, $request->file('file'));
            return redirect()->route('books.index')->with('success', 'Data buku berhasil diimport (Maksimal 500 baris per unggahan).');
        } catch (\Throwable $e) {
            return redirect()->route('books.index')->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }
}
