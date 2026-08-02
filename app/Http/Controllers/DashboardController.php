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

        // Daily borrowing & returning trends for last 7 days
        $daily7Days = [];
        $daily7BorrowData = [];
        $daily7ReturnData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $daily7Days[] = $date->translatedFormat('d M');

            $daily7BorrowData[] = Borrowing::whereDate('borrow_date', $date->toDateString())->count();
            $daily7ReturnData[] = ReturnBook::whereDate('return_date', $date->toDateString())->count();
        }

        // Daily borrowing & returning trends for last 30 days
        $daily30Days = [];
        $daily30BorrowData = [];
        $daily30ReturnData = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $daily30Days[] = $date->translatedFormat('d M');

            $daily30BorrowData[] = Borrowing::whereDate('borrow_date', $date->toDateString())->count();
            $daily30ReturnData[] = ReturnBook::whereDate('return_date', $date->toDateString())->count();
        }

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
                'daily_7' => [
                    'labels' => $daily7Days,
                    'borrowings' => $daily7BorrowData,
                    'returns' => $daily7ReturnData,
                ],
                'daily_30' => [
                    'labels' => $daily30Days,
                    'borrowings' => $daily30BorrowData,
                    'returns' => $daily30ReturnData,
                ],
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
