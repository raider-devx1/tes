@php
    $role = auth()->user()->role;
    $menu = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'roles' => ['*'], 'icon' => 'M3 12l9-9 9 9M4.5 10.5V21h15V10.5'],
        ['heading' => 'PKL', 'roles' => ['siswa_pkl', 'guru_pembimbing', 'instruktur_industri']],
        ['label' => 'Jurnal Kegiatan', 'route' => 'jurnal.index', 'roles' => ['siswa_pkl', 'guru_pembimbing', 'instruktur_industri'], 'icon' => 'M12 6.75v10.5M6.75 12h10.5'],
        ['label' => 'Catatan Kegiatan', 'route' => 'catatan.index', 'roles' => ['siswa_pkl', 'guru_pembimbing', 'instruktur_industri'], 'icon' => 'M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h4.5'],
        ['label' => 'Observasi', 'route' => 'observasi.index', 'roles' => ['guru_pembimbing', 'siswa_pkl', 'instruktur_industri'], 'icon' => 'M2.036 12.322a1 1 0 010-.644C3.423 7.51 7.36 4.5 12 4.5s8.573 3.01 9.964 7.178a1 1 0 010 .644C20.577 16.49 16.64 19.5 12 19.5s-8.573-3.01-9.964-7.178z'],
        ['label' => 'Absensi', 'route' => 'absensi.index', 'roles' => ['instruktur_industri', 'siswa_pkl', 'guru_pembimbing'], 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5h18v11.25'],
        ['label' => 'Penilaian', 'route' => 'nilai.index', 'roles' => ['instruktur_industri', 'siswa_pkl', 'guru_pembimbing'], 'icon' => 'M11.48 3.5a.56.56 0 011.04 0l2.12 5.11 5.52.44-4.2 3.6 1.28 5.39L12 16.5l-4.29 2.04 1.28-5.39-4.2-3.6 5.52-.44z'],
        ['label' => 'Dokumen', 'route' => 'dokumen.index', 'roles' => ['siswa_pkl', 'guru_pembimbing', 'instruktur_industri'], 'icon' => 'M19.5 14.25v-2.6a3.38 3.38 0 00-3.38-3.38h-1.5A1.13 1.13 0 0113.5 7.13v-1.5A3.38 3.38 0 0010.13 2.25H8.25'],
        ['heading' => 'Administrasi', 'roles' => ['admin']],
        ['label' => 'Kelola Pengguna', 'route' => 'admin.users.index', 'roles' => ['admin'], 'icon' => 'M15 19.13a9.38 9.38 0 002.63.37 9.34 9.34 0 004.12-.95'],
        ['label' => 'Data Industri', 'route' => 'admin.perusahaan.index', 'roles' => ['admin'], 'icon' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18'],
        ['label' => 'Periode PKL', 'route' => 'admin.periode.index', 'roles' => ['admin'], 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5h18v11.25'],
        ['label' => 'Informasi/Panduan', 'route' => 'admin.informasi.index', 'roles' => ['admin'], 'icon' => 'M11.25 11.25l.04-.02a.75.75 0 011.06.85l-.7 2.84'],
        ['label' => 'Pengaturan', 'route' => 'admin.pengaturan.edit', 'roles' => ['admin'], 'icon' => 'M9.59 3.94c.09-.54.56-.94 1.11-.94h2.59c.55 0 1.02.4 1.11.94'],
        ['heading' => 'Informasi', 'roles' => ['*']],
        ['label' => 'Panduan PKL', 'route' => 'informasi.index', 'roles' => ['*'], 'icon' => 'M12 6.04A8.97 8.97 0 006 3.75c-1.05 0-2.06.18-3 .51v14.25A8.99 8.99 0 016 18c2.31 0 4.41.87 6 2.29'],
    ];
@endphp
<nav class="space-y-1">
    @foreach($menu as $item)
        @php $tampil = in_array('*', $item['roles']) || in_array($role, $item['roles']); @endphp
        @continue(! $tampil)
        @if(isset($item['heading']))
            <p class="px-2 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $item['heading'] }}</p>
        @else
            @php $aktif = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition {{ $aktif ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" /></svg>
                {{ $item['label'] }}
            </a>
        @endif
    @endforeach
</nav>
