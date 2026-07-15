<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Nilai Murid PKL</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color:#000; margin: 30px; }
        .header { text-align:center; font-weight:bold; line-height:1.5; margin-bottom:20px; }
        .header div { text-transform:uppercase; }
        .sub-template { font-weight:bold; text-transform:uppercase; margin-top:4px; }
        .biodata { width:100%; margin-bottom:18px; }
        .biodata td { padding:2px 0; vertical-align:top; }
        .biodata td.label { width:160px; }
        .biodata td.colon { width:12px; }
        table.nilai { width:100%; border-collapse:collapse; margin-bottom:22px; }
        table.nilai th, table.nilai td { border:1px solid #000; padding:7px 8px; vertical-align:middle; }
        table.nilai th { background:#f2f2f2; text-align:center; font-size:10.5pt; }
        .text-center { text-align:center; }
        table.hadir { border-collapse:collapse; margin-bottom:35px; }
        table.hadir td { border:1px solid #000; padding:5px 10px; }
        table.hadir .judul-hadir { border:none; padding:0 0 4px 0; font-weight:bold; }
        .footer { width:100%; margin-top:10px; }
        .footer td { width:50%; vertical-align:top; text-align:left; }
        .nama-ttd { margin-top:70px; }
        .nip-ttd  { margin-top:2px; } /* NIP tepat di bawah nama */

        /* Tiap siswa 1 halaman; mulai halaman 1, tanpa halaman kosong */
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
    </style>
</head>
<body>
    @php
        $predikat = function ($n) {
            if (is_null($n)) return '-';
            return match (true) {
                $n >= 5 => 'Sangat Baik',
                $n == 4 => 'Baik',
                $n == 3 => 'Cukup',
                $n == 2 => 'Kurang',
                default => 'Sangat Kurang',
            };
        };
    @endphp

    @foreach($lembar as $data)
        @php 
            extract($data); 
            $isTemplate = $isTemplate ?? false;
            // Memastikan variabel berbentuk objek agar tidak error saat diakses di Blade
            $nilaiObj = (object) ($nilai ?? []);
            $kehadiranArr = (array) ($kehadiran ?? []);
        @endphp

        <div class="page">
            <div class="header">
                <div>DAFTAR NILAI MURID</div>
                <div>MATA PELAJARAN PKL</div>
                <div> {{ $nama_sekolah ?? '' }} </div>
                <div>TAHUN PELAJARAN  {{ $tahun_ajaran ?? '' }} </div>
                @if($isTemplate)
                    <div class="sub-template">(Lembar Penilaian untuk Diisi Instruktur)</div>
                @endif
            </div>

            <table class="biodata">
                <tr><td class="label">Nama Murid</td><td class="colon">:</td><td> {{ $nama_siswa ?? '' }} </td></tr>
                <tr><td class="label">Kelas</td><td class="colon">:</td><td> {{ $kelas ?? '' }} </td></tr>
                <tr><td class="label">Program Keahlian</td><td class="colon">:</td><td> {{ $program_keahlian ?? '' }} </td></tr>
                <tr><td class="label">Tempat PKL</td><td class="colon">:</td><td> {{ $dunia_kerja ?? '' }} </td></tr>
                <tr>
                    <td class="label">Tanggal Observasi</td><td class="colon">:</td>
                    <td> {{ ($isTemplate || empty($tanggal_observasi)) ? '.....................' : \Carbon\Carbon::parse($tanggal_observasi)->format('d F Y') }} </td>
                </tr>
                <tr><td class="label">Nama Instruktur</td><td class="colon">:</td><td> {{ $nama_instruktur ?? '' }} </td></tr>
                <tr><td class="label">Nama Pembimbing</td><td class="colon">:</td><td> {{ $nama_guru ?? '' }} </td></tr>
            </table>

            <table class="nilai">
                <thead>
                    <tr>
                        <th width="60%">Tujuan Pembelajaran</th>
                        <th width="12%">Skor</th>
                        <th width="28%">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1. Internalisasi dan Penerapan Soft Skills</td>
                        <td class="text-center"> {{ $isTemplate ? '' : ($nilaiObj->soft_skill ?? '-') }} </td>
                        <td class="text-center"> {{ $isTemplate ? '' : $predikat($nilaiObj->soft_skill ?? null) }} </td>
                    </tr>
                    <tr>
                        <td>2. Penerapan Hard Skills</td>
                        <td class="text-center"> {{ $isTemplate ? '' : ($nilaiObj->hard_skill ?? '-') }} </td>
                        <td class="text-center"> {{ $isTemplate ? '' : $predikat($nilaiObj->hard_skill ?? null) }} </td>
                    </tr>
                    <tr>
                        <td>3. Peningkatan &amp; Pengembangan Hard Skills</td>
                        <td class="text-center"> {{ $isTemplate ? '' : ($nilaiObj->pengembangan_hard_skill ?? '-') }} </td>
                        <td class="text-center"> {{ $isTemplate ? '' : $predikat($nilaiObj->pengembangan_hard_skill ?? null) }} </td>
                    </tr>
                    <tr>
                        <td>4. Penyiapan kemandirim kewirausahaan</td>
                        <td class="text-center"> {{ $isTemplate ? '' : ($nilaiObj->kewirausahaan ?? '-') }} </td>
                        <td class="text-center"> {{ $isTemplate ? '' : $predikat($nilaiObj->kewirausahaan ?? null) }} </td>
                    </tr>
                </tbody>
            </table>

            <table class="hadir">
                <tr><td class="judul-hadir" colspan="2">Kehadiran :</td></tr>
                <tr><td>Sakit</td><td>:  {{ $isTemplate ? '' : ($kehadiranArr['sakit'] ?? 0) }}  Hari</td></tr>
                <tr><td>Ijin</td><td>:  {{ $isTemplate ? '' : ($kehadiranArr['izin'] ?? 0) }}  Hari</td></tr>
                <tr><td>Tanpa Keterangan</td><td>:  {{ $isTemplate ? '' : ($kehadiranArr['alpha'] ?? 0) }}  Hari</td></tr>
            </table>

            <table class="footer">
                <tr>
                    <td>
                        <br>
                        Instruktur
                        <div class="nama-ttd">  {{ $nama_instruktur ?? '' }}  </div>
                    </td>
                    <td>
                        Majene,  {{ $isTemplate ? '.....................' : ($tanggal_cetak ?? '') }}  <br>
                        Guru Pembimbing,
                        <div class="nama-ttd"> {{ $nama_guru ?? '' }}  </div>
                        <div class="nip-ttd">NIP.  {{ $nip_guru ?? '' }} </div>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
</body>
</html>