<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lembar Observasi PKL</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 11pt; margin: 20px; color: #000; }
        .header-title { text-align: center; font-weight: bold; font-size: 13pt; margin-bottom: 25px; text-transform: uppercase; }
        .info-table { margin-bottom: 15px; width: 100%; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .info-table td:nth-child(1) { width: 200px; }
        .info-table td:nth-child(2) { width: 15px; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid black; padding: 8px; vertical-align: top; }
        .data-table th { text-align: center; font-weight: bold; font-size: 10pt; background-color: #f9f9f9; text-transform: uppercase; }

        .text-center { text-align: center !important; }
        .paraf-col { width: 80px; }

        /* Kolom paraf: hasil PARAF DIGITAL (kanvas di web/HP) otomatis dicetak di sini.
           Bila belum ada paraf digital, kolom dibiarkan kosong untuk paraf basah.
           Selector dibuat lebih spesifik (.data-table td.paraf-cell) supaya menang
           dari aturan .data-table td { vertical-align: top; } di atas. */
        .data-table td.paraf-cell { text-align: center; vertical-align: middle; }
        .data-table td.paraf-cell img { display: block; margin: 0 auto; }
        .sign-text { font-family: 'Helvetica', Arial, sans-serif; font-size: 9pt; font-weight: bold; color: #000; }

        .footer-note { font-size: 9pt; color: #000; margin-top: 8px; }

        /* Tiap observasi 1 halaman; tanpa halaman kosong di depan */
        .lembar { page-break-after: always; }
        .lembar:last-child { page-break-after: auto; }
    </style>
</head>
<body>

@forelse($lembar as $data)
    @php
        extract($data);
        // Status validasi TIDAK dicetak pada lembar.
        $sudahValidasi = ($status ?? 'draft') === 'tervalidasi';

        // Paraf digital -> data URI base64 agar DomPDF tidak bergantung pada
        // symlink public/storage maupun izin folder di shared hosting.
        $parafInstruktur = \App\Support\TandaTangan::dataUri($ttd_instruktur ?? null);
        $parafGuru       = \App\Support\TandaTangan::dataUri($ttd_guru ?? null);
    @endphp

    <div class="lembar">
        <div class="header-title">LEMBAR OBSERVASI PKL</div>

        <table class="info-table" border="0">
            <tr><td>Nama Murid</td><td>:</td><td> {{ $nama_siswa }} </td></tr>
            <tr><td>Kelas</td><td>:</td><td> {{ $kelas }} </td></tr>
            <tr><td>Dunia Kerja Tempat PKL</td><td>:</td><td> {{ $dunia_kerja }} </td></tr>
            <tr><td>Nama Instruktur</td><td>:</td><td> {{ $nama_instruktur }} </td></tr>
            <tr><td>Nama Guru Mapel</td><td>:</td><td> {{ $nama_guru }} </td></tr>
            <tr><td>Pekerjaan / Projek</td><td>:</td><td> {{ $pekerjaan_projek }} </td></tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">NO</th>
                    <th width="35%">PERMASALAHAN</th>
                    <th width="35%">SOLUSI PEMECAHAN<br>MASALAH</th>
                    <th class="paraf-col">PARAF<br>INST.</th>
                    <th class="paraf-col">PARAF<br>PEMB.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $item)
                <tr>
                    <td class="text-center"> {{ $loop->iteration }} </td>
                    <td>{!! nl2br(e($item->permasalahan)) !!}</td>
                    <td>{!! nl2br(e($item->solusi)) !!}</td>
                    {{-- Paraf instruktur: hasil paraf digital, tanpa nama di bawahnya. --}}
                    <td class="paraf-cell">
                        @if($parafInstruktur)
                            <img src="{{ $parafInstruktur }}" alt="Paraf instruktur" style="height:24px; width:auto;">
                        @else
                            &nbsp;
                        @endif
                    </td>
                    {{-- Paraf guru pembimbing: hasil paraf digital, tanpa nama di bawahnya. --}}
                    <td class="paraf-cell">
                        @if($parafGuru)
                            <img src="{{ $parafGuru }}" alt="Paraf guru pembimbing" style="height:24px; width:auto;">
                        @else
                            &nbsp;
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada data observasi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Keterangan validasi sengaja TIDAK dicetak. Kolom paraf otomatis terisi
             paraf digital instruktur & guru pembimbing bila sudah dibubuhkan;
             bila belum, kolom tetap polos untuk diparaf manual. --}}
    </div>

@empty
    <div class="header-title">LEMBAR OBSERVASI PKL</div>
    <p class="text-center">Belum ada data observasi.</p>
@endforelse

</body>
</html>