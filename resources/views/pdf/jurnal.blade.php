<!DOCTYPE html>
<html>
<head>
    <title>Jurnal Kegiatan PKL</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .text-center { text-align: center; }
        .header-info { margin-bottom: 20px; }
        .header-info td { padding: 3px 0; }
        table.data-jurnal { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-jurnal th, table.data-jurnal td { border: 1px solid black; padding: 8px; text-align: left; vertical-align: top; }
        table.data-jurnal th { text-align: center; font-weight: bold; }
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
    @php $siswa = $data['siswa']; $jurnals = $data['jurnals']; @endphp

    <div class="lembar">
        <h3 class="text-center" style="text-decoration: underline;">JURNAL KEGIATAN PKL</h3>

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

        <table class="data-jurnal">
            <thead>
                <tr>
                    <th width="5%">No.</th>
                    <th width="15%">Hari/Tanggal</th>
                    <th width="28%">Unit Kerja/Pekerjaan</th>
                    <th width="30%">Catatan*</th>
                    <th width="22%">Paraf Instruktur</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jurnals as $index => $row)
                <tr>
                    <td class="text-center"> {{ $index + 1 }} </td>
                    <td> {{ \Carbon\Carbon::parse($row->hari_tanggal)->locale('id')->translatedFormat('l, d F Y') }} </td>
                    <td>
                        @if($row->items->count())
                            <ol style="margin:0; padding-left:16px;">
                                @foreach($row->items as $it)
                                    <li> {{ $it->unit_kerja }} </li>
                                @endforeach
                            </ol>
                        @else
                            -
                        @endif
                    </td>
                    <td> {{ $row->catatan_instruktur ?? '-' }} </td>
                    <td>
                        @if($row->status === 'disetujui')
                            <div class="verified">
                                <span class="verified-title">DISETUJUI OLEH INSTRUKTUR</span>
                                TERVERIFIKASI SISTEM
                                <span class="verified-sub">
                                    (Divalidasi oleh Guru Pembimbing @if($row->validated_at) pada {{ \Carbon\Carbon::parse($row->validated_at)->locale('id')->translatedFormat('d F Y') }} @endif)
                                </span>
                            </div>
                        @else
                            <br><br><br>
                            <div class="text-center">( .................... )</div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada jurnal untuk ditampilkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer-note">
            * Catatan diberikan oleh instruktur pada setiap kegiatan atau waktu tertentu
        </div>
    </div>

@empty
    <h3 class="text-center" style="text-decoration: underline;">JURNAL KEGIATAN PKL</h3>
    <p class="text-center">Tidak ada data jurnal.</p>
@endforelse

</body>
</html>