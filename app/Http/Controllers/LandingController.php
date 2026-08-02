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
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $booksQuery = Book::with('category');

        if ($search) {
            $booksQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
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
            'school_address' => Setting::getByKey('school_address', 'Jl. Raya Maron No. 45, Maron, Probolinggo'),
            'school_email' => Setting::getByKey('school_email', 'sdn02maron@gmail.com'),
            'school_phone' => Setting::getByKey('school_phone', '0812-3456-7890'),
            'vision' => Setting::getByKey('vision'),
            'mission' => Setting::getByKey('mission'),
            'gmaps_embed_url' => Setting::getByKey('gmaps_embed_url'),
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
