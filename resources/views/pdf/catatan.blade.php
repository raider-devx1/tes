<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Catatan Kegiatan PKL</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; margin: 30px; color:#000; }
        .judul { text-align:center; font-weight:bold; text-decoration:underline; font-size:14pt; margin-bottom:20px; }
        .identitas { width:100%; margin-bottom:15px; }
        .identitas td { padding:3px; vertical-align:top; }
        .section { margin-top:14px; }
        .label { font-weight:bold; margin-bottom:5px; }
        .hint { font-style:italic; font-size:10pt; margin-bottom:4px; }
        .box { border:1px solid #000; min-height:70px; padding:8px; }
        .box-besar { border:1px solid #000; min-height:130px; padding:8px; }
        .catatan { border:1px solid #000; min-height:90px; padding:8px; }
        .ttd { margin-top:22px; width:100%; }
        .ttd-kanan { width:45%; float:right; text-align:center; }
        .nama-ttd { margin-top:70px; text-decoration:underline; }
        .nama-ttd-verified { margin-top:2px; text-decoration:underline; font-weight:bold; font-size:11pt; }

        /* Tanda tangan digital instruktur (hasil kanvas di web/HP).

           Tinggi paraf: 58px (sebelumnya 34px) -> cukup jelas terbaca saat dicetak,
           tapi TIDAK melebihi ruang paraf basah versi manual (margin-top:70px pada
           .nama-ttd). Karena blok bertanda tangan tetap lebih pendek daripada blok
           kosongnya, lembar catatan dijamin tidak turun ke halaman kedua.

           max-width:210px menjaga paraf yang gambarnya sangat lebar tetap berada di
           dalam kolom kanan (45% lebar halaman) dan tidak menabrak teks. */
        .ttd-img { margin-top:4px; height:58px; text-align:center; }
        .ttd-img img { height:58px; width:auto; max-width:210px; display:inline; }
        .ttd-info { font-size:8pt; margin-top:1px; }
        .empty { text-align:center; font-style:italic; color:#555; }

        /* Badge tanda tangan digital terverifikasi */
        .verified {
            border:1.5px solid #16a34a;
            background:#f0fdf4;
            color:#166534;
            border-radius:8px;
            padding:8px 10px;
            font-size:10pt;
            line-height:1.4;
            text-align:center;
            margin-top:8px;
        }
        .verified .verified-title { font-weight:bold; display:block; }
        .verified .verified-sub { font-size:9pt; }
    </style>
</head>
<body>

@forelse($catatan as $item)

    <div class="judul">CATATAN KEGIATAN PKL</div>

    <table class="identitas">
        <tr>
            <td width="200">Nama Peserta Didik</td>
            <td width="10">:</td>
            <td> {{ $item->user->name }} </td>
        </tr>
        <tr>
            <td>Dunia Kerja Tempat PKL</td>
            <td>:</td>
            <td> {{ $item->user->perusahaan->nama_perusahaan ?? '-' }} </td>
        </tr>
        <tr>
            <td>Nama Instruktur</td>
            <td>:</td>
            <td> {{ $item->user->instruktur->name ?? '-' }} </td>
        </tr>
        <tr>
            <td>Nama Guru Pembimbing</td>
            <td>:</td>
            <td> {{ $item->user->guru->name ?? '-' }} </td>
        </tr>
    </table>

    <div class="section">
        <div class="label">A. Nama Pekerjaan</div>
        <div class="box"> {{ $item->nama_pekerjaan }} </div>
    </div>

    <div class="section">
        <div class="label">B. Perencanaan Kegiatan</div>
        <div class="hint">* Jadwal kegiatan / dokumen perencanaan</div>
        <div class="box">{!! nl2br(e($item->perencanaan_kegiatan)) !!}</div>
    </div>

    <div class="section">
        <div class="label">C. Pelaksanaan Kegiatan / Hasil</div>
        <div class="hint">* Uraian proses kerja dan hasil</div>
        <div class="box-besar">{!! nl2br(e($item->pelaksanaan_kegiatan)) !!}</div>
    </div>

    <div class="section">
        <div class="label">D. Catatan Instruktur</div>
        <div class="catatan">{!! nl2br(e($item->catatan_instruktur ?? '-')) !!}</div>
    </div>

    <div class="ttd">
        <div class="ttd-kanan">
            Majene, {{ $tanggal_cetak }} 
            <br><br>
            Instruktur,

            @php $ttdParaf = \App\Support\TandaTangan::dataUri($item->ttd_instruktur); @endphp
            @if($ttdParaf)
                {{-- Tanda tangan digital instruktur: otomatis dari halaman pengajuan siswa --}}
                <div class="ttd-img">
                    <img src="{{ $ttdParaf }}" alt="Tanda tangan instruktur" style="height:58px; width:auto; max-width:210px;">
                </div>
                <div class="nama-ttd-verified">
                    {{ $item->ttd_instruktur_nama ?: ($item->user->instruktur->name ?? '-') }}
                </div>
               
            @else
                {{-- Belum ditandatangani: ruang paraf basah tetap dicetak kosong --}}
                <div class="nama-ttd"> {{ $item->user->instruktur->name ?? '-' }} </div>
            @endif
        </div>
    </div>
    <div style="clear:both;"></div>

    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif

@empty
    <div class="judul">CATATAN KEGIATAN PKL</div>
    <p class="empty">Belum ada catatan kegiatan yang disetujui instruktur.</p>
@endforelse

</body>
</html>