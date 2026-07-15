<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi PKL</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color:#000; }
        .text-center { text-align: center; }
        h3 { margin-bottom: 4px; }
        .header-info { margin-bottom: 12px; }
        .header-info td { padding: 3px 0; }

        table.data-absensi { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-absensi th, table.data-absensi td { border: 1px solid black; padding: 6px; text-align: left; vertical-align: top; }
        table.data-absensi th { text-align: center; font-weight: bold; background:#f2f2f2; }

        table.rekap { width: 60%; border-collapse: collapse; margin-top: 12px; }
        table.rekap th, table.rekap td { border: 1px solid black; padding: 5px 8px; text-align: center; }
        table.rekap th { background:#f2f2f2; }

        .footer-note { font-style: italic; font-size: 10px; margin-top: 5px; }

        /* Badge tanda tangan digital terverifikasi */
        .verified {
            border: 1.5px solid #16a34a;
            background: #f0fdf4;
            color: #166534;
            border-radius: 6px;
            padding: 5px 6px;
            font-size: 9px;
            line-height: 1.35;
            text-align: center;
        }
        .verified .verified-title { font-weight: bold; display: block; }
        .verified .verified-sub { font-size: 8px; }

        /* 1 siswa = 1 halaman, tanpa halaman kosong di depan */
        .lembar { page-break-after: always; }
        .lembar:last-child { page-break-after: auto; }
    </style>
</head>
<body>

@forelse($lembar as $data)
    @php
        $siswa    = $data['siswa'];
        $absensis = $data['absensis'];
        $rekap    = $data['rekap'];
    @endphp

    <div class="lembar">
        <h3 class="text-center" style="text-decoration: underline;">REKAP ABSENSI PKL</h3>

        <table class="header-info">
            <tr>
                <td width="150">Nama Peserta Didik</td>
                <td width="10">:</td>
                <td> {{ $siswa->name }} </td>
            </tr>
            <tr>
                <td>Dunia Kerja Tempat PKL</td>
                <td>:</td>
                <td> {{ $siswa->perusahaan->nama_perusahaan ?? '-' }} </td>
            </tr>
            <tr>
                <td>Nama Instruktur</td>
                <td>:</td>
                <td> {{ $siswa->instruktur->name ?? '-' }} </td>
            </tr>
            <tr>
                <td>Nama Guru Pembimbing</td>
                <td>:</td>
                <td> {{ $siswa->guru->name ?? '-' }} </td>
            </tr>
        </table>

        <table class="data-absensi">
            <thead>
                <tr>
                    <th width="5%">No.</th>
                    <th width="20%">Hari/Tanggal</th>
                    <th width="12%">Status</th>
                    <th width="12%">Jam Masuk</th>
                    <th width="12%">Jam Pulang</th>
                    <th width="17%">Keterangan</th>
                    <th width="22%">Validasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensis as $index => $row)
                <tr>
                    <td class="text-center"> {{ $index + 1 }} </td>
                    <td> {{ \Carbon\Carbon::parse($row->tanggal)->locale('id')->translatedFormat('l, d F Y') }} </td>
                    <td class="text-center"> {{ $row->status }} </td>
                    <td class="text-center"> {{ $row->jam_masuk ?? '-' }} </td>
                    <td class="text-center"> {{ $row->jam_pulang ?? '-' }} </td>
                    <td> {{ $row->catatan_instruktur ?? '-' }} </td>
                    <td>
                        @if(($row->status_validasi ?? 'draft') === 'disetujui')
                            <div class="verified">
                                <span class="verified-title">DISETUJUI OLEH INSTRUKTUR</span>
                                TERVERIFIKASI SISTEM
                                <span class="verified-sub">
                                    (Divalidasi oleh Guru Pembimbing @if($row->validated_at) pada {{ \Carbon\Carbon::parse($row->validated_at)->locale('id')->translatedFormat('d F Y') }} @endif)
                                </span>
                            </div>
                        @else
                            <br><br>
                            <div class="text-center">( .................... )</div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data absensi untuk ditampilkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <table class="rekap">
            <thead>
                <tr>
                    <th>Hadir</th>
                    <th>Izin</th>
                    <th>Sakit</th>
                    <th>Alpha</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td> {{ $rekap['hadir'] }} </td>
                    <td> {{ $rekap['izin'] }} </td>
                    <td> {{ $rekap['sakit'] }} </td>
                    <td> {{ $rekap['alpha'] }} </td>
                    <td> {{ $absensis->count() }} </td>
                </tr>
            </tbody>
        </table>

        <div class="footer-note">
            * Kolom validasi menampilkan status verifikasi bukti fisik yang telah divalidasi oleh Guru Pembimbing.
        </div>
    </div>

@empty
    <h3 class="text-center" style="text-decoration: underline;">REKAP ABSENSI PKL</h3>
    <p class="text-center">Tidak ada data absensi.</p>
@endforelse

</body>
</html>