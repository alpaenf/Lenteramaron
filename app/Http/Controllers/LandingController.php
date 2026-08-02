<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Gallery;
use App\Models\GuestBook;
use App\Models\Member;
use App\Models\Borrowing;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Carbon;

class LandingController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $categoryId = $request->input('category_id');

        $booksQuery = Book::with('category');

        if ($search !== '') {
            $booksQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if (!empty($categoryId)) {
            $booksQuery->where('category_id', $categoryId);
        }

        $books = $booksQuery->latest()->paginate(12)->withQueryString();

        $stats = [
            'total_books' => Book::sum('stock'),
            'total_members' => Member::where('status', 'Aktif')->count(),
            'total_borrowings' => Borrowing::count(),
            'total_visitors' => GuestBook::count(),
        ];

        $categories = BookCategory::all();
        $galleries = Gallery::latest()->get();
        
        $settings = [
            'school_name' => Setting::getByKey('school_name', 'SD Negeri 02 Maron'),
            'library_name' => Setting::getByKey('library_name', 'LENTERA MARON'),
            'school_address' => Setting::getByKey('school_address', 'Desa Maron, Kec. Garung, Kab. Wonosobo, Jawa Tengah'),
            'school_email' => Setting::getByKey('school_email', 'sdn02maron@gmail.com'),
            'school_phone' => Setting::getByKey('school_phone', '0812-3456-7890'),
            'vision' => Setting::getByKey('vision'),
            'mission' => Setting::getByKey('mission'),
            'gmaps_embed_url' => Setting::getByKey('gmaps_embed_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3957.6751394476323!2d109.92206757357137!3d-7.277756071516778!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e700b18b719df35%3A0x753393990615190a!2sSD%20N%202%20MARON!5e0!3m2!1sid!2sid!4v1785695520988!5m2!1sid!2sid'),
            'spreadsheet_url' => Setting::getByKey('spreadsheet_url'),
            'hero_banner_path' => Setting::getByKey('hero_banner_path'),
            'profile_photo_1' => Setting::getByKey('profile_photo_1'),
            'profile_photo_2' => Setting::getByKey('profile_photo_2'),
            'profile_photo_3' => Setting::getByKey('profile_photo_3'),
            'profile_photo_4' => Setting::getByKey('profile_photo_4'),
        ];

        return Inertia::render('Landing/Index', [
            'books' => $books,
            'stats' => $stats,
            'categories' => $categories,
            'galleries' => $galleries,
            'settings' => $settings,
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
            ],
        ]);
    }

    public function storeGuest(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'feedback' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $visitorCountToday = GuestBook::whereDate('date', Carbon::today())->count() + 1;
        $visitorNo = 'VIS-' . date('Ymd') . '-' . str_pad($visitorCountToday, 3, '0', STR_PAD_LEFT);

        $guestBook = GuestBook::create([
            'visitor_no' => $visitorNo,
            'name' => $validated['name'],
            'institution' => $validated['institution'],
            'purpose' => $validated['purpose'],
            'feedback' => $validated['feedback'] ?? null,
            'note' => $validated['note'] ?? null,
            'date' => Carbon::now()->toDateString(),
            'time' => Carbon::now()->toTimeString(),
        ]);

        // Send automatic notification email to Admin Gmail configured in Settings
        try {
            $adminEmail = Setting::getByKey('school_email', 'sdn02maron@gmail.com');
            if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\GuestBookNotification($guestBook));
            }
        } catch (\Throwable $e) {
            // Log or ignore mail sending failure so guest entry submission never fails
        }

        return redirect()->back()->with('success', 'Terima kasih! Kunjungan Anda telah berhasil dicatat.');
    }
}
