<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Siswa PKL</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #1f2937; }
        h2 { margin: 0 0 2px; color: #1E3A8A; }
        .sub { color: #6b7280; font-size: 10px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; }
        th { background: #2563EB; color: #fff; font-size: 10px; }
        tr:nth-child(even) td { background: #f1f5f9; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h2>Data Siswa PKL</h2>
    <div class="sub">Dicetak: <?= e(date('d-m-Y H:i')) ?> &middot; Total: <?= e($siswa->count()) ?> siswa</div>

    <table>
        <thead>
            <tr>
                <th class="center">No</th>
                <th>Nama</th>
                <th>NISN</th>
                <th>Kelas / Jurusan</th>
                <th>Tempat PKL</th>
                <th>Guru</th>
                <th>Instruktur</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswa as $i => $s)
                <tr>
                    <td class="center"><?= e($i + 1) ?></td>
                    <td><?= e($s->name) ?></td>
                    <td><?= e($s->nisn ?? '-') ?></td>
                    <td><?= e(trim(($s->kelas ?? '-') . ' / ' . ($s->jurusan ?? '-'))) ?></td>
                    <td><?= e($s->perusahaan->nama_perusahaan ?? '-') ?></td>
                    <td><?= e($s->guru->name ?? '-') ?></td>
                    <td><?= e($s->instruktur->name ?? '-') ?></td>
                    <td class="center"><?= e(ucfirst($s->status_pkl)) ?></td>
                </tr>
            @empty
                <tr><td colspan="8" class="center">Belum ada data siswa.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>