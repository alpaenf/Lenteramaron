<?php

namespace App\Http\Controllers;

use App\Exports\BorrowingsExport;
use App\Exports\GuestBooksExport;
use App\Exports\ReturnsExport;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\GuestBook;
use App\Models\ReturnBook;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $type = $request->input('type', 'borrowings');

        $reportData = [];

        if ($type === 'borrowings') {
            $reportData = Borrowing::with(['member', 'book'])
                ->whereBetween('borrow_date', [$startDate, $endDate])
                ->latest('borrow_date')
                ->get();
        } elseif ($type === 'returns') {
            $reportData = ReturnBook::with(['member', 'book', 'borrowing'])
                ->whereBetween('return_date', [$startDate, $endDate])
                ->latest('return_date')
                ->get();
        } elseif ($type === 'guest_books') {
            $reportData = GuestBook::whereBetween('date', [$startDate, $endDate])
                ->latest('date')
                ->get();
        } elseif ($type === 'popular_books') {
            $reportData = Book::with('category')
                ->withCount('borrowings')
                ->orderBy('borrowings_count', 'desc')
                ->take(20)
                ->get();
        }

        return Inertia::render('Reports/Index', [
            'reportData' => $reportData,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'type' => $type,
            ],
        ]);
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $type = $request->input('type', 'borrowings');

        if ($type === 'borrowings') {
            $data = Borrowing::with(['member', 'book'])
                ->whereBetween('borrow_date', [$startDate, $endDate])
                ->latest('borrow_date')
                ->get();
            $pdf = Pdf::loadView('pdf.borrowings', compact('data', 'startDate', 'endDate'));
            return $pdf->download('Laporan_Peminjaman_' . date('Ymd') . '.pdf');
        } elseif ($type === 'returns') {
            $data = ReturnBook::with(['member', 'book', 'borrowing'])
                ->whereBetween('return_date', [$startDate, $endDate])
                ->latest('return_date')
                ->get();
            $pdf = Pdf::loadView('pdf.returns', compact('data', 'startDate', 'endDate'));
            return $pdf->download('Laporan_Pengembalian_' . date('Ymd') . '.pdf');
        } elseif ($type === 'guest_books') {
            $data = GuestBook::whereBetween('date', [$startDate, $endDate])
                ->latest('date')
                ->get();
            $pdf = Pdf::loadView('pdf.guest_books', compact('data', 'startDate', 'endDate'));
            return $pdf->download('Laporan_Buku_Tamu_' . date('Ymd') . '.pdf');
        } elseif ($type === 'popular_books') {
            $data = Book::with('category')
                ->withCount('borrowings')
                ->orderBy('borrowings_count', 'desc')
                ->take(20)
                ->get();
            $pdf = Pdf::loadView('pdf.popular_books', compact('data'));
            return $pdf->download('Laporan_Buku_Terpopuler_' . date('Ymd') . '.pdf');
        }

        return redirect()->back();
    }
}
