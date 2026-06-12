<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
    h2 { text-align: center; margin: 0; font-size: 16px; }
    .sub { text-align: center; margin: 2px 0 12px; font-size: 11px; color: #555; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #999; padding: 5px 6px; vertical-align: top; }
    th { background: #f1f5f9; text-align: left; }
</style></head><body>
    <h2>CATATAN KEGIATAN PKL</h2>
    <div class="sub">{{ $siswa->name }} &mdash; {{ $siswa->nis ?? '-' }}</div>
    <table>
        <thead><tr><th style="width:30px">No</th><th>Nama Pekerjaan</th><th>Perencanaan</th><th>Pelaksanaan/Hasil</th><th>Catatan Instruktur</th></tr></thead>
        <tbody>
            @foreach($catatans as $i => $c)
                <tr><td>{{ $i + 1 }}</td><td>{{ $c->nama_pekerjaan }}</td><td>{{ $c->perencanaan }}</td><td>{{ $c->pelaksanaan }}</td><td>{{ $c->catatan_instruktur ?? '-' }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body></html>
