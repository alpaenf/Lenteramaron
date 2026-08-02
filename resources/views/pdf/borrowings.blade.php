<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman Buku - SDN 02 Maron</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #1e293b; font-size: 18px; text-transform: uppercase; }
        .header h3 { margin: 5px 0 0; color: #2563eb; font-size: 14px; }
        .header p { margin: 3px 0 0; font-size: 11px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #cbd5e1; }
        th { background-color: #f1f5f9; color: #1e293b; padding: 8px; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px; font-size: 11px; }
        .footer-sign { margin-top: 30px; float: right; width: 220px; text-align: center; font-size: 11px; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-dipinjam { background-color: #dbeafe; color: #1e40af; }
        .badge-dikembalikan { background-color: #d1fae5; color: #065f46; }
        .badge-terlambat { background-color: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="header">
        <h2>PEMERINTAH KABUPATEN PROBOLINGGO</h2>
        <h3>DINAS PENDIDIKAN - SD NEGERI 02 MARON</h3>
        <p>PERPUSTAKAAN "LENTERA MARON"</p>
        <p>Jl. Raya Maron No. 45, Kec. Maron, Kab. Probolinggo | Email: sdn02maron@gmail.com</p>
    </div>

    <h3 style="text-align: center; margin-bottom: 5px;">LAPORAN TRANSAKSI PEMINJAMAN BUKU</h3>
    <p style="text-align: center; margin-top: 0; font-size: 11px; color: #64748b;">Periode: {{ $startDate }} s/d {{ $endDate }}</p>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="18%">No. Transaksi</th>
                <th width="22%">Nama Anggota</th>
                <th width="25%">Judul Buku</th>
                <th width="12%">Tgl Pinjam</th>
                <th width="12%">Jatuh Tempo</th>
                <th width="6%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td>{{ $item->transaction_no }}</td>
                    <td>{{ $item->member?->name }} ({{ $item->member?->class_name }})</td>
                    <td>{{ $item->book?->title }}</td>
                    <td align="center">{{ \Carbon\Carbon::parse($item->borrow_date)->format('d/m/Y') }}</td>
                    <td align="center">{{ \Carbon\Carbon::parse($item->due_date)->format('d/m/Y') }}</td>
                    <td align="center">
                        <span class="badge badge-{{ strtolower($item->status) }}">{{ $item->status }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" align="center">Tidak ada data peminjaman pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-sign">
        <p>Maron, {{ date('d F Y') }}</p>
        <p>Kepala Perpustakaan,</p>
        <br><br><br>
        <p><strong><u>Siti Pustakawan, S.IP</u></strong></p>
        <p>NIP. 19850512 201001 2 015</p>
    </div>
</body>
</html>
