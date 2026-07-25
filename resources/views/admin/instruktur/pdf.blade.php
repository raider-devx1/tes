<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Industri &amp; Pembimbing</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #1f2937; }
        h2 { margin: 0 0 2px; color: #1E3A8A; }
        .sub { color: #6b7280; font-size: 9px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 5px; text-align: left; }
        th { background: #2563EB; color: #fff; font-size: 9px; }
        tr:nth-child(even) td { background: #f1f5f9; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h2>Data Industri &amp; Pembimbing</h2>
    <div class="sub">Dicetak: {{ now()->format('d/m/Y H:i') }} &middot; Total: {{ $industri->count() }} industri</div>

    <table>
        <thead>
            <tr>
                <th class="center">No</th>
                <th>Nama Perusahaan</th>
                <th>Pembimbing (Instruktur)</th>
                <th>Alamat</th>
                <th>Telepon</th>
                <th class="center">Jumlah Siswa</th>
            </tr>
        </thead>
        <tbody>
            @forelse($industri as $i => $item)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $item->nama_perusahaan }}</td>
                    <td>{{ $item->pembimbing_industri ?? '-' }}</td>
                    <td>{{ $item->alamat ?? '-' }}</td>
                    <td>{{ $item->telepon ?? '-' }}</td>
                    <td class="center">{{ $item->siswa_count ?? 0 }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="center">Belum ada data industri.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
