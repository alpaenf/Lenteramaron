<?php

namespace App\Http\Controllers;

use App\Exports\GuestBooksExport;
use App\Models\GuestBook;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;

class GuestBookController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $date = $request->input('date');

        $query = GuestBook::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('institution', 'like', "%{$search}%")
                  ->orWhere('visitor_no', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        if ($date) {
            $query->whereDate('date', $date);
        }

        $guests = $query->latest('date')->latest('time')->paginate(10)->withQueryString();

        return Inertia::render('GuestBooks/Index', [
            'guests' => $guests,
            'filters' => [
                'search' => $search,
                'date' => $date,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'feedback' => 'nullable|string',
            'note' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required',
        ]);

        $visitorCountToday = GuestBook::whereDate('date', $validated['date'])->count() + 1;
        $visitorNo = 'VIS-' . date('Ymd', strtotime($validated['date'])) . '-' . str_pad($visitorCountToday, 3, '0', STR_PAD_LEFT);

        GuestBook::create([
            'visitor_no' => $visitorNo,
            ...$validated,
        ]);

        return redirect()->route('guest-books.index')->with('success', 'Catatan buku tamu berhasil disimpan.');
    }

    public function update(Request $request, GuestBook $guestBook)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'feedback' => 'nullable|string',
            'note' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required',
        ]);

        $guestBook->update($validated);

        return redirect()->route('guest-books.index')->with('success', 'Buku tamu berhasil diperbarui.');
    }

    public function destroy(GuestBook $guestBook)
    {
        $guestBook->delete();

        return redirect()->route('guest-books.index')->with('success', 'Catatan buku tamu dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new GuestBooksExport, 'Buku_Tamu_LENTERA_ILMU_' . date('Ymd') . '.xlsx');
    }
}
