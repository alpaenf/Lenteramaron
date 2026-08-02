<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Buku Terpopuler - SDN 02 Maron</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h2>PEMERINTAH KABUPATEN PROBOLINGGO</h2>
        <h3>DINAS PENDIDIKAN - SD NEGERI 02 MARON</h3>
        <p>PERPUSTAKAAN "LENTERA MARON"</p>
        <p>Jl. Raya Maron No. 45, Kec. Maron, Kab. Probolinggo | Email: sdn02maron@gmail.com</p>
    </div>

    <h3 style="text-align: center; margin-bottom: 5px;">LAPORAN BUKU TERPOPULER</h3>
    <p style="text-align: center; margin-top: 0; font-size: 11px; color: #64748b;">Per Tanggal: {{ date('d F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th width="8%">Peringkat</th>
                <th width="15%">Kode Buku</th>
                <th width="35%">Judul Buku</th>
                <th width="20%">Pengarang</th>
                <th width="12%">Kategori</th>
                <th width="10%">Total Dipinjam</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td align="center"><strong>#{{ $index + 1 }}</strong></td>
                    <td align="center">{{ $item->book_code }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->author }}</td>
                    <td>{{ $item->category?->name ?? '-' }}</td>
                    <td align="center"><strong>{{ $item->borrowings_count }} kali</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" align="center">Belum ada transaksi peminjaman buku.</td>
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
