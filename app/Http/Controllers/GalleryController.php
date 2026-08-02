<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Traits\CompressesImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    use CompressesImage;

    public function index(Request $request): Response
    {
        $category = $request->input('category');

        $query = Gallery::query();

        if ($category) {
            $query->where('category', $category);
        }

        $galleries = $query->latest()->paginate(12)->withQueryString();

        return Inertia::render('Galleries/Index', [
            'galleries' => $galleries,
            'filters' => [
                'category' => $category,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:Literasi,Perpustakaan,Outing Class,Lomba',
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
            'description' => 'nullable|string',
        ]);

        $path = $this->compressAndSaveImage($request->file('image'), 'galleries');

        Gallery::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'image_path' => $path,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('galleries.index')->with('success', 'Foto kegiatan berhasil ditambahkan ke galeri.');
    }

    private function deleteImageFile(?string $imagePath): void
    {
        if (!$imagePath) return;

        // New uploads stored in public/uploads/
        if (str_starts_with($imagePath, 'uploads/')) {
            $fullPath = public_path($imagePath);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
            return;
        }

        // Legacy uploads stored in storage/app/public/
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    public function update(Request $request, Gallery $gallery)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:Literasi,Perpustakaan,Outing Class,Lomba',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:10240',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $this->deleteImageFile($gallery->image_path);
            $validated['image_path'] = $this->compressAndSaveImage($request->file('image'), 'galleries');
        }

        $gallery->update($validated);

        return redirect()->route('galleries.index')->with('success', 'Data galeri berhasil diperbarui.');
    }

    public function destroy(Gallery $gallery)
    {
        $this->deleteImageFile($gallery->image_path);
        $gallery->delete();

        return redirect()->route('galleries.index')->with('success', 'Foto galeri berhasil dihapus.');
    }
}
