<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
    h2 { text-align: center; margin: 0; font-size: 16px; }
    .sub { text-align: center; margin: 2px 0 12px; font-size: 11px; color: #555; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #999; padding: 5px 6px; vertical-align: top; }
    th { background: #f1f5f9; text-align: left; }
    .meta td { border: none; padding: 1px 0; }
    .ttd { margin-top: 30px; width: 100%; }
    .ttd td { border: none; text-align: center; }
</style></head><body>
    <h2>JURNAL KEGIATAN PKL</h2>
    <div class="sub">{{ $pengaturan['nama_sekolah'] ?? 'SMK' }} &mdash; {{ $pengaturan['tahun_ajaran'] ?? '' }}</div>
    <table class="meta">
        <tr><td style="width:120px">Nama Siswa</td><td>: {{ $siswa->name }}</td><td style="width:120px">Industri</td><td>: {{ optional($siswa->perusahaan)->nama ?? '-' }}</td></tr>
        <tr><td>NIS</td><td>: {{ $siswa->nis ?? '-' }}</td><td>Kelas</td><td>: {{ $siswa->kelas ?? '-' }}</td></tr>
    </table>
    <table>
        <thead><tr><th style="width:30px">No</th><th style="width:90px">Hari/Tgl</th><th>Unit Kerja</th><th>Deskripsi Pekerjaan</th><th>Catatan Instruktur</th><th style="width:70px">Status</th></tr></thead>
        <tbody>
            @foreach($jurnals as $i => $j)
                <tr><td>{{ $i + 1 }}</td><td>{{ $j->hari_tanggal->format('d/m/Y') }}</td><td>{{ $j->unit_kerja }}</td><td>{{ $j->deskripsi_pekerjaan }}</td><td>{{ $j->catatan_instruktur ?? '-' }}</td><td>{{ ucfirst($j->status_persetujuan) }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <table class="ttd"><tr><td>Mengetahui,<br>Guru Pembimbing<br><br><br><br>(__________________)</td><td>Instruktur Industri<br><br><br><br><br>(__________________)</td><td>Siswa PKL<br><br><br><br><br>({{ $siswa->name }})</td></tr></table>
</body></html>
