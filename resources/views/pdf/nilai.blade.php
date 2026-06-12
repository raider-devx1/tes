<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
    h2 { text-align: center; margin: 0; font-size: 16px; }
    .sub { text-align: center; margin: 2px 0 16px; font-size: 11px; color: #555; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #999; padding: 7px 8px; }
    th { background: #f1f5f9; text-align: left; }
    .nilai { text-align: center; width: 80px; }
    .total { font-weight: bold; background: #eef2ff; }
    .ttd { margin-top: 40px; width: 100%; border: none; }
    .ttd td { border: none; text-align: center; }
</style></head><body>
    <h2>LEMBAR PENILAIAN PKL</h2>
    <div class="sub">{{ $pengaturan['nama_sekolah'] ?? 'SMK' }}</div>
    <table style="border:none">
        <tr><td style="border:none;width:110px">Nama Siswa</td><td style="border:none">: {{ $siswa->name }}</td></tr>
        <tr><td style="border:none">NIS</td><td style="border:none">: {{ $siswa->nis ?? '-' }}</td></tr>
        <tr><td style="border:none">Industri</td><td style="border:none">: {{ optional($siswa->perusahaan)->nama ?? '-' }}</td></tr>
    </table>
    <table>
        <thead><tr><th>Komponen Penilaian</th><th class="nilai">Nilai (1-5)</th></tr></thead>
        <tbody>
            <tr><td>Soft Skill</td><td class="nilai">{{ $nilai->soft_skill }}</td></tr>
            <tr><td>Hard Skill</td><td class="nilai">{{ $nilai->hard_skill }}</td></tr>
            <tr><td>Pengembangan Hard Skill</td><td class="nilai">{{ $nilai->pengembangan_hard_skill }}</td></tr>
            <tr><td>Kewirausahaan</td><td class="nilai">{{ $nilai->kewirausahaan }}</td></tr>
            <tr class="total"><td>Rata-rata</td><td class="nilai">{{ $nilai->rata_rata }}</td></tr>
        </tbody>
    </table>
    @if($nilai->catatan_rekomendasi)<p><strong>Catatan/Rekomendasi:</strong> {{ $nilai->catatan_rekomendasi }}</p>@endif
    <table class="ttd"><tr><td>&nbsp;</td><td>Instruktur Industri<br><br><br><br>({{ optional($nilai->instruktur)->name ?? '...................' }})</td></tr></table>
</body></html>
