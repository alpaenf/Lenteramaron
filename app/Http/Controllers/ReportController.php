<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ExternalSource;
use App\Models\ResearchTopic;
use App\Models\SavedSource;
use App\Models\SearchQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $type = $request->input('type', 'search_queries');

        $reportData = [];

        if ($type === 'search_queries') {
            $reportData = SearchQuery::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->latest()
                ->get();
        } elseif ($type === 'saved_sources') {
            $reportData = SavedSource::with(['book', 'externalSource', 'user', 'researchTopic'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->latest()
                ->get();
        } elseif ($type === 'topics') {
            $reportData = ResearchTopic::with('user')
                ->withCount('savedSources')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->latest()
                ->get();
        } elseif ($type === 'reference_books') {
            $reportData = Book::with('category')
                ->orderBy('title')
                ->get();
        }

        return Inertia::render('Reports/Index', [
            'reportData' => $reportData,
            'filters' => [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'type'       => $type,
            ],
            'summary' => [
                'total_searches' => SearchQuery::count(),
                'total_saved'    => SavedSource::count(),
                'total_topics'   => ResearchTopic::count(),
                'total_books'    => Book::count(),
            ]
        ]);
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $type = $request->input('type', 'search_queries');

        $title = "Laporan Analitis Penelitian & Literasi LITERA";
        $reportData = [];

        if ($type === 'search_queries') {
            $reportData = SearchQuery::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->latest()
                ->get();
            $title = "Laporan Riwayat Pencarian Riset AI";
        } elseif ($type === 'saved_sources') {
            $reportData = SavedSource::with(['book', 'externalSource', 'user'])
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->latest()
                ->get();
            $title = "Laporan Sumber Literatur Tersimpan di Workspace";
        } elseif ($type === 'topics') {
            $reportData = ResearchTopic::with('user')
                ->withCount('savedSources')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->latest()
                ->get();
            $title = "Laporan Proyek & Topik Penelitian";
        } elseif ($type === 'reference_books') {
            $reportData = Book::with('category')->get();
            $title = "Laporan Katalog Buku Referensi";
        }

        $pdf = Pdf::loadView('pdf.research_report', compact('reportData', 'startDate', 'endDate', 'type', 'title'));
        return $pdf->download('LITERA_Laporan_' . ucfirst($type) . '_' . date('Ymd') . '.pdf');
    }
}
