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
        table.data-jurnal th, table.data-jurnal td { border: 1px solid black; padding: 8px; text-align: left; }
        table.data-jurnal th { text-align: center; font-weight: bold; }
        .footer-note { font-style: italic; font-size: 10px; margin-top: 5px; }
    </style>
</head>
<body>
    <h3 class="text-center" style="text-decoration: underline;">JURNAL KEGIATAN PKL</h3>
    
    <table class="header-info">
        <tr>
            <td width="150">Nama Peserta Didik</td>
            <td width="10">:</td>
            <!-- Data dinamis dari database -->
            <td>{{ $siswa->name }}</td>
        </tr>
        <tr>
            <td>Dunia Kerja Tempat PKL</td>
            <td>:</td>
            <!-- Data ini nanti disetting oleh admin di relasi penempatan -->
            <td>{{ $siswa->perusahaan->nama_perusahaan ?? '.......................................' }}</td>
        </tr>
        <tr>
            <td>Nama Instruktur</td>
            <td>:</td>
            <td>{{ $siswa->instruktur->name ?? '.......................................' }}</td>
        </tr>
        <tr>
            <td>Nama Guru Pembimbing</td>
            <td>:</td>
            <td>{{ $siswa->guru->name ?? '.......................................' }}</td>
        </tr>
    </table>

    <table class="data-jurnal">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="15%">Hari/Tanggal</th>
                <th width="30%">Unit Kerja/Pekerjaan</th>
                <th width="35%">Catatan*</th>
                <th width="15%">Paraf Instruktur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jurnals as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->hari_tanggal)->format('d-m-Y') }}</td>
                <td>{{ $row->unit_kerja }}</td>
                <td>{{ $row->catatan_instruktur }}</td>
                <td></td> <!-- Ruang kosong untuk paraf -->
            </tr>
            @empty
            <!-- Baris kosong jika belum ada isian, menyesuaikan format cetak manual -->
            @for($i=1; $i<=5; $i++)
            <tr>
                <td class="text-center" style="height: 40px;">{{ $i }}</td>
                <td></td><td></td><td></td><td></td>
            </tr>
            @endfor
            @endforelse
        </tbody>
    </table>
    <div class="footer-note">
        * Catatan diberikan oleh instruktur pada setiap kegiatan atau waktu tertentu
    </div>
</body>
</html>