<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengembalian Buku - SDN 02 Maron</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 15px 25px; line-height: 1.4; }
        
        /* Kop Surat Resmi */
        .kop { text-align: center; margin-bottom: 15px; }
        .kop h2 { margin: 0; font-size: 15px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; }
        .kop h1 { margin: 3px 0 2px; font-size: 17px; font-weight: 900; color: #004b87; text-transform: uppercase; letter-spacing: 0.8px; }
        .kop p.sub { margin: 0; font-size: 12px; font-weight: 700; color: #334155; text-transform: uppercase; }
        .kop p.address { margin: 4px 0 0; font-size: 10px; color: #64748b; font-weight: 500; }
        
        /* Garis Kop Ganda */
        .line-double { border-top: 3px solid #0f172a; border-bottom: 1px solid #0f172a; height: 2px; margin: 10px 0 18px 0; }

        /* Document Title */
        .doc-title { text-align: center; margin-bottom: 20px; }
        .doc-title h3 { margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-title p { margin: 3px 0 0; font-size: 11px; font-weight: 600; color: #64748b; }

        /* Minimalist Table */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8fafc; color: #334155; font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 9px 8px; border-bottom: 2px solid #cbd5e1; border-top: 1px solid #e2e8f0; letter-spacing: 0.3px; }
        td { padding: 8px 8px; font-size: 10.5px; border-bottom: 1px solid #e2e8f0; color: #334155; vertical-align: middle; }
        tr:nth-child(even) td { background-color: #fcfcfc; }

        /* Sign Section */
        .footer-section { margin-top: 35px; width: 100%; }
        .sign-box { float: right; width: 220px; text-align: center; font-size: 11px; }
        .sign-box p { margin: 2px 0; }
        .sign-space { height: 50px; }
    </style>
</head>
<body>
    <div class="kop">
        <h2>PEMERINTAH KABUPATEN WONOSOBO • DINAS PENDIDIKAN</h2>
        <h1>SD NEGERI 2 MARON</h1>
        <p class="sub">PERPUSTAKAAN "LENTERA MARON"</p>
        <p class="address">Desa Maron, Kec. Garung, Kab. Wonosobo, Jawa Tengah | Email: sdn02maron@gmail.com</p>
    </div>

    <div class="line-double"></div>

    <div class="doc-title">
        <h3>LAPORAN PENGEMBALIAN BUKU</h3>
        <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%" align="center">NO</th>
                <th width="18%" align="left">NO. RETURN</th>
                <th width="24%" align="left">NAMA ANGGOTA</th>
                <th width="26%" align="left">JUDUL BUKU</th>
                <th width="12%" align="center">TGL KEMBALI</th>
                <th width="10%" align="center">KONDISI</th>
                <th width="6%" align="center">TERLAMBAT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold; color: #15803d;">{{ $item->return_no }}</td>
                    <td><strong>{{ $item->member?->name }}</strong><br><span style="font-size: 9.5px; color: #64748b;">{{ $item->member?->class_name }}</span></td>
                    <td>{{ $item->book?->title }}</td>
                    <td align="center">{{ \Carbon\Carbon::parse($item->return_date)->format('d/m/Y') }}</td>
                    <td align="center"><strong>{{ $item->condition }}</strong></td>
                    <td align="center">{{ $item->late_days > 0 ? $item->late_days.' hr' : '0' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" align="center" style="padding: 20px; color: #94a3b8;">Tidak ada data pengembalian pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-section">
        <div class="sign-box">
            <p>Maron, {{ date('d F Y') }}</p>
            <p><strong>Kepala Perpustakaan,</strong></p>
            <div class="sign-space"></div>
            <p><strong><u>Siti Pustakawan, S.IP</u></strong></p>
            <p style="color: #64748b; font-size: 10px;">NIP. 19850512 201001 2 015</p>
        </div>
    </div>
</body>
</html>
