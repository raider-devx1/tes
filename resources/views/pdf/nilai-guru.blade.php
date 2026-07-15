<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penilaian PKL Siswa - Guru</title>
    <style>
        @page {
            margin: 25px 30px 55px 30px;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        /* Header (RATA TENGAH sesuai format acuan) */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .header h4 {
            margin: 0;
            font-size: 15px;
            font-weight: bold;
        }

        /* Tabel Informasi Siswa (tanpa border) */
        .table-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-info td {
            padding: 3px 5px;
            vertical-align: top;
            border: none;
        }
        .table-info td:first-child {
            width: 180px;
        }
        .titik-dua {
            width: 15px;
            text-align: left;
        }

        /* Tabel Nilai (Border) */
        .table-score {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table-score th, .table-score td {
            border: 2px solid black;
            padding: 8px;
            vertical-align: top;
        }
        .table-score th {
            text-align: center;
            font-weight: bold;
        }
        .col-skor {
            text-align: center;
            width: 50px;
        }
        .col-tujuan {
            width: 180px;
        }
        .row-rata td {
            font-weight: bold;
            background: #f2f2f2;
        }

        /* Catatan (DIBERI BORDER sesuai format acuan) */
        .catatan {
            border: 2px solid black;
            padding: 8px;
            margin-bottom: 20px;
            text-align: justify;
        }

        /* Absen / Ketidakhadiran (DIBERI BORDER sesuai format acuan) */
        .absen-container {
            margin-bottom: 30px;
        }
        .table-absen {
            border-collapse: collapse;
            width: 300px;
        }
        .table-absen td {
            border: 2px solid black;
            padding: 3px 8px;
            vertical-align: top;
        }

        /* Tanda Tangan */
        .ttd-container {
            width: 100%;
            margin-top: 20px;
        }
        .ttd-left {
            float: left;
            width: 45%;
            text-align: left;
        }
        .ttd-right {
            float: right;
            width: 45%;
            text-align: left;
        }
        .clear {
            clear: both;
        }

        /* Footer (3 kolom + garis atas, sesuai format acuan) */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 11px;
            font-style: italic;
            font-weight: bold;
            border-top: 1px solid black;
            padding-top: 4px;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-table td {
            border: none;
            padding: 0;
        }
    </style>
</head>
<body>
    @php
        $daftarSkor = [
            optional($nilai)->skor_soft_skill,
            optional($nilai)->skor_hard_skill,
            optional($nilai)->skor_pengembangan,
            optional($nilai)->skor_kewirausahaan,
            optional($nilai)->skor_laporan,
            optional($nilai)->skor_presentasi,
        ];
        $nilaiLengkap  = $nilai && ! in_array(null, $daftarSkor, true);
        $rataRataAkhir = $nilaiLengkap ? round(array_sum($daftarSkor) / count($daftarSkor), 2) : null;
    @endphp

    <div class="header">
        <h3>UPTD SMKN 1 MAJENE</h3>
        <h4>Tahun Ajaran {{ $tahunAjaran ?? '' }}</h4>
    </div>

    <table class="table-info">
        <tr>
            <td>Nama Peserta Didik</td>
            <td class="titik-dua">:</td>
            <td>{{ $siswa->name ?? '' }}</td>
        </tr>
        <tr>
            <td>NISN</td>
            <td class="titik-dua">:</td>
            <td>{{ $siswa->nisn ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kelas</td>
            <td class="titik-dua">:</td>
            <td>{{ $siswa->kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td>Program Keahlian</td>
            <td class="titik-dua">:</td>
            <td>{{ $siswa->program_keahlian ?? 'Teknik Jaringan Komputer dan Telekomunikasi' }}</td>
        </tr>
        <tr>
            <td>Konsentrasi Keahlian</td>
            <td class="titik-dua">:</td>
            <td>{{ $siswa->konsentrasi_keahlian ?? 'Teknik Komputer dan Jaringan' }}</td>
        </tr>
        <tr>
            <td>Tempat PKL</td>
            <td class="titik-dua">:</td>
            <td>{{ $namaPerusahaan ?? '' }}</td>
        </tr>
        <tr>
            <td>Tanggal PKL</td>
            <td class="titik-dua">:</td>
            <td>
                Mulai: {{ $tanggalMulaiFormat ?? '' }}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Selesai: {{ $tanggalSelesaiFormat ?? '' }}
            </td>
        </tr>
        <tr>
            <td>Nama Instruktur</td>
            <td class="titik-dua">:</td>
            <td>{{ $siswa->instruktur->name ?? 'MULFIANTI' }}</td>
        </tr>
        <tr>
            <td>Nama Pembimbing</td>
            <td class="titik-dua">:</td>
            <td>{{ $siswa->guru->name ?? 'M. ASRI, Amd.Kom' }}</td>
        </tr>
    </table>

    <table class="table-score">
        <thead>
            <tr>
                <th class="col-tujuan">Tujuan Pembelajaran</th>
                <th class="col-skor">Skor</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Internalisasi dan penerapan soft skill</td>
                <td class="col-skor">{{ optional($nilai)->skor_soft_skill ?? '-' }}</td>
                <td>{{ optional($nilai)->deskripsi_soft_skill ?? '-' }}</td>
            </tr>
            <tr>
                <td>Penerapan hard skill</td>
                <td class="col-skor">{{ optional($nilai)->skor_hard_skill ?? '-' }}</td>
                <td>{{ optional($nilai)->deskripsi_hard_skill ?? '-' }}</td>
            </tr>
            <tr>
                <td>Peningkatan dan pengembangan hard skill</td>
                <td class="col-skor">{{ optional($nilai)->skor_pengembangan ?? '-' }}</td>
                <td>{{ optional($nilai)->deskripsi_pengembangan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Penyiapan dan kemandirian kewirausahaan</td>
                <td class="col-skor">{{ optional($nilai)->skor_kewirausahaan ?? '-' }}</td>
                <td>{{ optional($nilai)->deskripsi_kewirausahaan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Penulisan laporan</td>
                <td class="col-skor">{{ optional($nilai)->skor_laporan ?? '-' }}</td>
                <td>{{ optional($nilai)->deskripsi_laporan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Pemaparan presentasi</td>
                <td class="col-skor">{{ optional($nilai)->skor_presentasi ?? '-' }}</td>
                <td>{{ optional($nilai)->deskripsi_presentasi ?? '-' }}</td>
            </tr>
            <tr class="row-rata">
                <td>Rata-rata Nilai Akhir</td>
                <td class="col-skor">{{ $nilaiLengkap ? $rataRataAkhir : '-' }}</td>
                <td>{{ $nilaiLengkap ? 'Nilai lengkap' : 'Nilai belum lengkap' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="catatan">
        <span style="font-weight: bold;">Catatan:</span> {{ optional($nilai)->catatan_guru ?? '-' }}
    </div>

    <div class="absen-container">
        <table class="absen-table" style="border-collapse: collapse; width: 300px;">
            <tr>
                <td colspan="2" style="border: 2px solid black; padding: 3px 8px; vertical-align: top;"><span style="font-weight: bold;">Ketidakhadiran</span></td>
            </tr>
            <tr>
                <td style="border: 2px solid black; padding: 3px 8px; vertical-align: top;">Sakit</td>
                <td style="border: 2px solid black; padding: 3px 8px; vertical-align: top;">: {{ $sakit ?? 0 }} hari</td>
            </tr>
            <tr>
                <td style="border: 2px solid black; padding: 3px 8px; vertical-align: top;">Ijin</td>
                <td style="border: 2px solid black; padding: 3px 8px; vertical-align: top;">: {{ $ijin ?? 0 }} hari</td>
            </tr>
            <tr>
                <td style="border: 2px solid black; padding: 3px 8px; vertical-align: top;">Tanpa Keterangan</td>
                <td style="border: 2px solid black; padding: 3px 8px; vertical-align: top;">: {{ $alpa ?? 0 }} hari</td>
            </tr>
        </table>
    </div>

    <div class="ttd-container">
        <div class="ttd-left">
            <p style="margin:0;">Guru Pembimbing</p>
            <br><br><br><br>
            <p style="margin:0;">
                <span style="font-weight:bold; text-decoration: underline;">{{ $siswa->guru->name ?? 'M. ASRI, Amd.Kom' }}</span><br>
                NIP. {{ $siswa->guru->nip ?? '197609102005021007' }}
            </p>
        </div>
        <div class="ttd-right">
            <p style="margin:0;">Majene, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p style="margin:0;">Pembimbing Dunia Kerja,</p>
            <br><br><br><br>
            <p style="margin:0;">
                <span style="font-weight:bold; text-decoration: underline;">{{ $siswa->instruktur->name ?? 'MULFIANTI' }}</span><br>
                NIP. {{ $siswa->instruktur->nip ?? '-' }}
            </p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td style="text-align:left; width:33%;">{{ $siswa->name ?? '' }} - {{ $siswa->kelas ?? 'TKJ' }}</td>
                <td style="text-align:center; width:34%;">1</td>
                <td style="text-align:right; width:33%;">Dicetak dari e-Rapor SMK v.8.0.3</td>
            </tr>
        </table>
    </div>
</body>
</html>