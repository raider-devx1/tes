<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lembar Penilaian Sertifikasi PKL</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 11pt; color: #000; margin: 15px; }
        .title-doc { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .table-biodata { width: 100%; margin-bottom: 25px; }
        .table-biodata td { padding: 4px 0; vertical-align: top; }
        .table-biodata td.label { width: 180px; }
        .table-biodata td.colon { width: 15px; text-align: center; }
        
        .table-score { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table-score th, .table-score td { border: 1px solid #000; padding: 10px 8px; vertical-align: middle; }
        .table-score th { text-align: center; font-weight: bold; font-size: 10pt; background-color: #f2f2f2; }
        
        .text-center { text-align: center !important; }
        .font-bold { font-weight: bold; }
        
        .box-rekomendasi { border: 1px solid #000; padding: 12px; margin-top: 25px; min-height: 80px; }
        .box-rekomendasi h4 { margin: 0 0 6px 0; font-size: 11pt; font-weight: bold; }
        .box-rekomendasi p { margin: 0; font-style: italic; color: #333; font-size: 10pt; }

        .footer-sign { width: 100%; margin-top: 50px; }
        .footer-sign td { width: 50%; text-align: center; vertical-align: top; }
    </style>
</head>
<body>

    <div class="title-doc">LEMBAR PENILAIAN PERKEMBANGAN SISWA PKL</div>

    <table class="table-biodata" border="0">
        <tr>
            <td class="label">Nama Peserta Didik</td>
            <td class="colon">:</td>
            <td class="font-bold">{{ $nama_siswa }}</td>
        </tr>
        <tr>
            <td class="label">Kelas</td>
            <td class="colon">:</td>
            <td>{{ $kelas }}</td>
        </tr>
        <tr>
            <td class="label">Dunia Kerja Tempat PKL</td>
            <td class="colon">:</td>
            <td>{{ $dunia_kerja }}</td>
        </tr>
        <tr>
            <td class="label">Nama Instruktur / Penilai</td>
            <td class="colon">:</td>
            <td>{{ $nama_instruktur }}</td>
        </tr>
        <tr>
            <td class="label">Nama Guru Pembimbing</td>
            <td class="colon">:</td>
            <td>{{ $nama_guru }}</td>
        </tr>
    </table>

    <table class="table-score">
        <thead>
            <tr>
                <th width="8%">NO</th>
                <th width="67%">KOMPONEN PENILAIAN EVALUASI</th>
                <th width="25%">SKOR NILAI (1 - 5)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Internalisasi dan Penerapan Soft Skill</td>
                <td class="text-center font-bold">{{ $nilai->soft_skill }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Penerapan Hard Skill</td>
                <td class="text-center font-bold">{{ $nilai->hard_skill }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Peningkatan dan Pengembangan Hard Skill</td>
                <td class="text-center font-bold">{{ $nilai->pengembangan_hard_skill }}</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Penyiapan Kemandirian dan Kewirausahaan</td>
                <td class="text-center font-bold">{{ $nilai->kewirausahaan }}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td colspan="2" class="font-bold" style="text-align: right; padding-right: 15px;">NILAI AKHIR RATA-RATA :</td>
                <td class="text-center font-bold" style="font-size: 13pt; color: #1e3a8a;">{{ $nilai->rata_rata }}</td>
            </tr>
        </tbody>
    </table>

    <div class="box-rekomendasi">
        <h4>Catatan Masukan / Rekomendasi Karir dari Instruktur Industri:</h4>
        <p>{{ $nilai->catatan_rekomendasi ? '"'.$nilai->catatan_rekomendasi.'"' : '-' }}</p>
    </div>

    <table class="footer-sign">
        <tr>
            <td>
                <br>
                Guru Pembimbing PKL,
                <br><br><br><br><br>
                ( {{ $nama_guru }} )
            </td>
            <td>
                Jepara, ......................... 2026<br>
                Instruktur Industri,
                <br><br><br><br><br>
                ( {{ $nama_instruktur }} )
            </td>
        </tr>
    </table>

</body>
</html>