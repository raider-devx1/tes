<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Catatan Kegiatan PKL</title>

    <style>
        body{
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            margin: 30px;
        }

        .judul{
            text-align:center;
            font-weight:bold;
            text-decoration:underline;
            font-size:16pt;
            margin-bottom:20px;
        }

        .identitas{
            width:100%;
            margin-bottom:20px;
        }

        .identitas td{
            padding:4px;
            vertical-align:top;
        }

        .section{
            margin-top:15px;
        }

        .label{
            font-weight:bold;
            margin-bottom:5px;
        }

        .box{
            border:1px solid #000;
            min-height:80px;
            padding:10px;
        }

        .box-besar{
            border:1px solid #000;
            min-height:180px;
            padding:10px;
        }

        .catatan{
            border:1px solid #000;
            min-height:100px;
            padding:10px;
        }

        .ttd{
            margin-top:40px;
            width:100%;
        }

        .ttd-kanan{
            width:35%;
            float:right;
            text-align:center;
        }

        .nama-ttd{
            margin-top:80px;
            text-decoration:underline;
        }
    </style>
</head>
<body>

    @foreach($catatan as $item)

    <div class="judul">
        CATATAN KEGIATAN PKL
    </div>

    <table class="identitas">
        <tr>
            <td width="200">Nama Peserta Didik</td>
            <td width="10">:</td>
            <td>{{ $nama_siswa }}</td>
        </tr>

        <tr>
            <td>Dunia Kerja Tempat PKL</td>
            <td>:</td>
            <td>{{ $dunia_kerja }}</td>
        </tr>

        <tr>
            <td>Nama Instruktur</td>
            <td>:</td>
            <td>{{ $nama_instruktur }}</td>
        </tr>

        <tr>
            <td>Nama Guru Pembimbing</td>
            <td>:</td>
            <td>{{ $nama_guru }}</td>
        </tr>
    </table>

    <div class="section">
        <div class="label">
            A. Nama Pekerjaan
        </div>

        <div class="box">
            {{ $item->nama_pekerjaan }}
        </div>
    </div>

    <div class="section">
        <div class="label">
            B. Perencanaan Kegiatan
        </div>

        <div style="font-style:italic;">
            * Jadwal kegiatan / dokumen perencanaan
        </div>

        <div class="box">
            {!! nl2br(e($item->perencanaan_kegiatan)) !!}
        </div>
    </div>

    <div class="section">
        <div class="label">
            C. Pelaksanaan Kegiatan / Hasil
        </div>

        <div style="font-style:italic;">
            * Uraian proses kerja dan hasil
        </div>

        <div class="box-besar">
            {!! nl2br(e($item->pelaksanaan_kegiatan)) !!}
        </div>
    </div>

    <div class="section">
        <div class="label">
            D. Catatan Instruktur
        </div>

        <div class="catatan">
            {!! nl2br(e($item->catatan_instruktur ?? '-')) !!}
        </div>
    </div>

    <div class="ttd">

        <div class="ttd-kanan">

            ......................,
            {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}

            <br><br>

            Instruktur,

            <div class="nama-ttd">
                {{ $nama_instruktur }}
            </div>

        </div>

    </div>

    <div style="clear:both;"></div>

    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif

    @endforeach

</body>
</html>