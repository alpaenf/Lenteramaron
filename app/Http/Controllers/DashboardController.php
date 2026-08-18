<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\ExternalSource;
use App\Models\ResearchTopic;
use App\Models\SavedSource;
use App\Models\SearchQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalBooks = Book::count();
        $totalSavedSources = SavedSource::count();
        $totalSearches = SearchQuery::count();
        $totalTopics = ResearchTopic::count();
        $totalExternalCached = ExternalSource::count();

        // Recent Search Queries Feed
        $recentSearches = SearchQuery::latest()
            ->take(8)
            ->get();

        // Recent Saved Sources in Workspace
        $recentSaved = SavedSource::with(['book', 'externalSource', 'user'])
            ->latest()
            ->take(5)
            ->get();

        // Category distribution of Reference Books (Pie Chart)
        $categoryLabels = [];
        $categoryData = [];

        $categories = BookCategory::withCount('books')->get();
        foreach ($categories as $cat) {
            if ($cat->books_count > 0) {
                $categoryLabels[] = $cat->name;
                $categoryData[] = $cat->books_count;
            }
        }

        // Search trend data for last 7 days
        $daily7Days = [];
        $daily7SearchData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $daily7Days[] = $date->translatedFormat('d M');
            $daily7SearchData[] = SearchQuery::whereDate('created_at', $date->toDateString())->count();
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_books'           => $totalBooks,
                'total_saved_sources'   => $totalSavedSources,
                'total_searches'        => $totalSearches,
                'total_topics'          => $totalTopics,
                'total_external_cached' => $totalExternalCached,
            ],
            'recent_searches' => $recentSearches,
            'recent_saved'    => $recentSaved,
            'charts' => [
                'daily_7' => [
                    'labels'   => $daily7Days,
                    'searches' => $daily7SearchData,
                ],
                'categories' => [
                    'labels' => $categoryLabels,
                    'data'   => $categoryData,
                ],
            ],
        ]);
    }
}
