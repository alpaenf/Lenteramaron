<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 20px; }
        .header { text-align: center; border-b: 2px solid #2563eb; padding-bottom: 12px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #1e293b; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 4px 0 0 0; color: #2563eb; font-weight: bold; font-size: 12px; }
        .meta { margin-bottom: 15px; font-size: 10px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { background-color: #eff6ff; color: #1e3a8a; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LITERA — AI RESEARCH &amp; KNOWLEDGE NAVIGATOR</h2>
        <p>{{ $title }}</p>
    </div>

    <div class="meta">
        <strong>Periode Laporan:</strong> {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }} | 
        <strong>Dicetak Pada:</strong> {{ date('d M Y H:i') }} WIB
    </div>

    <table>
        @if($type === 'search_queries')
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 55%;">Kueri Pencarian Riset</th>
                    <th style="width: 20%;">Jumlah Hasil</th>
                    <th style="width: 20%;">Waktu Pencarian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>"{{ $item->query_text }}"</td>
                        <td>{{ $item->results_count }} Hasil</td>
                        <td>{{ date('d M Y H:i', strtotime($item->created_at)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align: center;">Tidak ada data pencarian pada periode ini.</td></tr>
                @endforelse
            </tbody>
        @elseif($type === 'saved_sources')
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 20%;">Tipe Sumber</th>
                    <th style="width: 45%;">Judul Literatur</th>
                    <th style="width: 15%;">Status Bacaan</th>
                    <th style="width: 15%;">Tgl Disimpan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $item->source_type === 'local' ? 'Buku Referensi' : 'Jurnal Eksternal' }}</td>
                        <td>{{ $item->source_type === 'local' ? ($item->book->title ?? '-') : ($item->externalSource->title ?? '-') }}</td>
                        <td>{{ ucfirst($item->reading_status ?? 'unread') }}</td>
                        <td>{{ date('d M Y', strtotime($item->created_at)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center;">Tidak ada data sumber tersimpan.</td></tr>
                @endforelse
            </tbody>
        @elseif($type === 'topics')
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">Nama Topik Penelitian</th>
                    <th style="width: 25%;">Pembuat</th>
                    <th style="width: 15%;">Jumlah Sumber</th>
                    <th style="width: 15%;">Tgl Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><strong>{{ $item->title }}</strong></td>
                        <td>{{ $item->user->name ?? 'Pengguna' }}</td>
                        <td>{{ $item->saved_sources_count ?? 0 }} Artikel</td>
                        <td>{{ date('d M Y', strtotime($item->created_at)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center;">Tidak ada data topik penelitian.</td></tr>
                @endforelse
            </tbody>
        @else
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">Kode Buku</th>
                    <th style="width: 40%;">Judul Buku Referensi</th>
                    <th style="width: 25%;">Pengarang</th>
                    <th style="width: 15%;">Kategori</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $item->book_code }}</td>
                        <td><strong>{{ $item->title }}</strong></td>
                        <td>{{ $item->author }}</td>
                        <td>{{ $item->category->name ?? 'Umum' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center;">Tidak ada data buku referensi.</td></tr>
                @endforelse
            </tbody>
        @endif
    </table>

    <div class="footer">
        <p>LITERA Knowledge Navigator &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
