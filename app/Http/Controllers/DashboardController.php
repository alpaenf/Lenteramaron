<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Borrowing;
use App\Models\GuestBook;
use App\Models\Member;
use App\Models\ReturnBook;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalBooks = Book::sum('stock');
        $totalMembers = Member::where('status', 'Aktif')->count();
        $totalVisitors = GuestBook::count();
        $totalBorrowings = Borrowing::count();
        $totalReturns = ReturnBook::count();

        // Top 5 Popular Books
        $popularBooks = Book::withCount('borrowings')
            ->orderBy('borrowings_count', 'desc')
            ->take(5)
            ->get();

        // Monthly borrowing & returning trends for 6 months
        $months = [];
        $borrowChartData = [];
        $returnChartData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthLabel = $monthDate->translatedFormat('M Y');
            $months[] = $monthLabel;

            $borrowCount = Borrowing::whereYear('borrow_date', $monthDate->year)
                ->whereMonth('borrow_date', $monthDate->month)
                ->count();

            $returnCount = ReturnBook::whereYear('return_date', $monthDate->year)
                ->whereMonth('return_date', $monthDate->month)
                ->count();

            $borrowChartData[] = $borrowCount;
            $returnChartData[] = $returnCount;
        }

        // Category distribution (Pie Chart)
        $categoryLabels = [];
        $categoryData = [];

        $categories = BookCategory::withCount('books')->get();
        foreach ($categories as $cat) {
            if ($cat->books_count > 0) {
                $categoryLabels[] = $cat->name;
                $categoryData[] = $cat->books_count;
            }
        }

        // Recent Activity Feed
        $recentBorrowings = Borrowing::with(['member', 'book'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_books' => $totalBooks,
                'total_members' => $totalMembers,
                'total_visitors' => $totalVisitors,
                'total_borrowings' => $totalBorrowings,
                'total_returns' => $totalReturns,
            ],
            'popular_books' => $popularBooks,
            'charts' => [
                'monthly' => [
                    'labels' => $months,
                    'borrowings' => $borrowChartData,
                    'returns' => $returnChartData,
                ],
                'categories' => [
                    'labels' => $categoryLabels,
                    'data' => $categoryData,
                ],
            ],
            'recent_borrowings' => $recentBorrowings,
        ]);
    }
}
