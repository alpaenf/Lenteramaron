<?php

namespace App\Http\Controllers;

use App\Exports\ReturnsExport;
use App\Models\Borrowing;
use App\Models\ReturnBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;

class ReturnController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $query = ReturnBook::with(['member', 'book', 'borrowing']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_no', 'like', "%{$search}%")
                  ->orWhereHas('member', fn($mq) => $mq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('book', fn($bq) => $bq->where('title', 'like', "%{$search}%"));
            });
        }

        $returns = $query->latest('return_date')->paginate(10)->withQueryString();
        $activeBorrowings = Borrowing::with(['member', 'book'])
            ->where('status', 'Dipinjam')
            ->get();

        return Inertia::render('Returns/Index', [
            'returns' => $returns,
            'active_borrowings' => $activeBorrowings,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrowing_id' => 'required|exists:borrowings,id',
            'return_date' => 'required|date',
            'condition' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'note' => 'nullable|string',
        ]);

        $borrowing = Borrowing::with(['book', 'member'])->findOrFail($validated['borrowing_id']);

        if ($borrowing->status === 'Dikembalikan') {
            return redirect()->back()->with('error', 'Transaksi peminjaman ini sudah dikembalikan.');
        }

        $returnDate = Carbon::parse($validated['return_date']);
        $dueDate = Carbon::parse($borrowing->due_date);
        
        $lateDays = 0;
        if ($returnDate->greaterThan($dueDate)) {
            $lateDays = $returnDate->diffInDays($dueDate);
        }

        $returnCountToday = ReturnBook::whereDate('created_at', Carbon::today())->count() + 1;
        $returnNo = 'RET-' . date('Ymd') . '-' . str_pad($returnCountToday, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($validated, $borrowing, $returnNo, $returnDate, $lateDays) {
            ReturnBook::create([
                'return_no' => $returnNo,
                'borrowing_id' => $borrowing->id,
                'member_id' => $borrowing->member_id,
                'book_id' => $borrowing->book_id,
                'return_date' => $returnDate->toDateString(),
                'condition' => $validated['condition'],
                'late_days' => $lateDays,
                'note' => $validated['note'] ?? null,
            ]);

            // Update borrowing status & return_date
            $borrowing->update([
                'status' => 'Dikembalikan',
                'return_date' => $returnDate->toDateString(),
            ]);

            // Increment book stock automatically
            $borrowing->book->increment('stock');
        });

        return redirect()->route('returns.index')->with('success', 'Pengembalian buku berhasil diproses dan stok buku telah bertambah.');
    }

    public function exportExcel()
    {
        return Excel::download(new ReturnsExport, 'Pengembalian_Buku_' . date('Ymd') . '.xlsx');
    }
}
