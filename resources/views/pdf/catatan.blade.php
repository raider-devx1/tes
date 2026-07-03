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
        .ttd { margin-top:35px; width:100%; }
        .ttd-kanan { width:40%; float:right; text-align:center; }
        .nama-ttd { margin-top:70px; text-decoration:underline; }
        .empty { text-align:center; font-style:italic; color:#555; }
    </style>
</head>
<body>

@forelse($catatan as $item)

    <div class="judul">CATATAN KEGIATAN PKL</div>

    <table class="identitas">
        <tr>
            <td width="200">Nama Peserta Didik</td>
            <td width="10">:</td>
            <td> {{ $item->user->name ?? '-' }} </td>
        </tr>
        <tr>
            <td>Dunia Kerja Tempat PKL</td>
            <td>:</td>
            <td> {{ $item->user->perusahaan->nama_perusahaan ?? 'Belum Diatur' }} </td>
        </tr>
        <tr>
            <td>Nama Instruktur</td>
            <td>:</td>
            <td> {{ $item->user->instruktur->name ?? 'Belum Diatur' }} </td>
        </tr>
        <tr>
            <td>Nama Guru Pembimbing</td>
            <td>:</td>
            <td> {{ $item->user->guru->name ?? 'Belum Diatur' }} </td>
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
            Majene,  {{ $tanggal_cetak }} 
            <br><br>
            Instruktur,
            <div class="nama-ttd"> {{ $item->user->instruktur->name ?? 'Belum Diatur' }} </div>
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