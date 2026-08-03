<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'app_settings' => fn () => [
                'school_name' => Setting::getByKey('school_name', 'SD Negeri 02 Maron'),
                'library_name' => Setting::getByKey('library_name', 'LENTERA MARON'),
                'logo' => Setting::getByKey('logo'),
                'gmaps_url' => Setting::getByKey('gmaps_url'),
                'email' => Setting::getByKey('email'),
                'phone' => Setting::getByKey('phone'),
                'address' => Setting::getByKey('address'),
            ],
            'notifications' => fn () => $request->user() ? [
                'overdue_count' => \App\Models\Borrowing::where('status', 'Dipinjam')->whereDate('due_date', '<=', now())->count(),
                'guest_today_count' => \App\Models\GuestBook::whereDate('date', now()->toDateString())->count(),
                'items' => array_merge(
                    \App\Models\Borrowing::with(['member', 'book'])
                        ->where('status', 'Dipinjam')
                        ->whereDate('due_date', '<=', now())
                        ->take(4)->get()
                        ->map(fn($b) => [
                            'id' => 'borrow-'.$b->id,
                            'title' => 'Peminjaman Jatuh Tempo / Terlambat',
                            'message' => ($b->member?->name ?? 'Anggota').' - '.($b->book?->title ?? 'Buku'),
                            'time' => \Carbon\Carbon::parse($b->due_date)->format('d/m/Y'),
                            'type' => 'warning',
                            'url' => '/borrowings',
                        ])->all(),
                    \App\Models\GuestBook::whereDate('date', now()->toDateString())
                        ->take(3)->get()
                        ->map(fn($g) => [
                            'id' => 'guest-'.$g->id,
                            'title' => 'Pengunjung Baru Hari Ini',
                            'message' => $g->name.' ('.$g->institution.')',
                            'time' => $g->time,
                            'type' => 'info',
                            'url' => '/guest-books',
                        ])->all(),
                    \App\Models\Book::where('stock', '<=', 2)
                        ->take(3)->get()
                        ->map(fn($bk) => [
                            'id' => 'book-'.$bk->id,
                            'title' => 'Stok Buku Menipis',
                            'message' => $bk->title.' (Sisa '.$bk->stock.' eksemplar)',
                            'time' => 'Stok: '.$bk->stock,
                            'type' => 'error',
                            'url' => '/books',
                        ])->all()
                )
            ] : null,
        ];
    }
}
