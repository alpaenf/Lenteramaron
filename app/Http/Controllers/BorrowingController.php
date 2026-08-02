<?php

namespace App\Http\Controllers;

use App\Exports\BorrowingsExport;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;

class BorrowingController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Borrowing::with(['member', 'book']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_no', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%");
                  })
                  ->orWhereHas('book', function ($bq) use ($search) {
                      $bq->where('title', 'like', "%{$search}%")->orWhere('book_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $borrowings = $query->latest('borrow_date')->paginate(10)->withQueryString();
        $activeMembers = Member::where('status', 'Aktif')->get();
        $availableBooks = Book::where('stock', '>', 0)->get();

        return Inertia::render('Borrowings/Index', [
            'borrowings' => $borrowings,
            'members' => $activeMembers,
            'books' => $availableBooks,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:borrow_date',
            'notes' => 'nullable|string',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        if ($book->stock <= 0) {
            return redirect()->back()->with('error', 'Stok buku ini sedang habis.');
        }

        $trxCountToday = Borrowing::whereDate('created_at', Carbon::today())->count() + 1;
        $transactionNo = 'TRX-' . date('Ymd') . '-' . str_pad($trxCountToday, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($validated, $transactionNo, $book) {
            Borrowing::create([
                'transaction_no' => $transactionNo,
                'member_id' => $validated['member_id'],
                'book_id' => $validated['book_id'],
                'borrow_date' => $validated['borrow_date'],
                'due_date' => $validated['due_date'],
                'status' => 'Dipinjam',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Decrement book stock automatically
            $book->decrement('stock');
        });

        return redirect()->route('borrowings.index')->with('success', 'Peminjaman buku berhasil dicatat dan stok buku telah diperbarui.');
    }

    public function destroy(Borrowing $borrowing)
    {
        DB::transaction(function () use ($borrowing) {
            if ($borrowing->status === 'Dipinjam') {
                $borrowing->book->increment('stock');
            }
            $borrowing->delete();
        });

        return redirect()->route('borrowings.index')->with('success', 'Peminjaman berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new BorrowingsExport, 'Peminjaman_Buku_' . date('Ymd') . '.xlsx');
    }
}
