<?php

namespace App\Http\Controllers;

use App\Exports\MembersExport;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $className = $request->input('class_name');
        $status = $request->input('status');

        $query = Member::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('parent_name', 'like', "%{$search}%");
            });
        }

        if ($className) {
            $query->where('class_name', $className);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $members = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Members/Index', [
            'members' => $members,
            'filters' => [
                'search' => $search,
                'class_name' => $className,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:30|unique:members,nis',
            'name' => 'required|string|max:255',
            'class_name' => 'required|string|max:20',
            'gender' => 'required|in:L,P',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:30',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        Member::create($validated);

        return redirect()->route('members.index')->with('success', 'Anggota siswa baru berhasil ditambahkan.');
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:30|unique:members,nis,' . $member->id,
            'name' => 'required|string|max:255',
            'class_name' => 'required|string|max:20',
            'gender' => 'required|in:L,P',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:30',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        $member->update($validated);

        return redirect()->route('members.index')->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('members.index')->with('success', 'Data anggota berhasil dihapus.');
    }

    public function exportExcel()
    {
        return Excel::download(new MembersExport, 'Data_Anggota_Siswa_' . date('Ymd') . '.xlsx');
    }
}
