@php
    $user    = auth()->user();
    $dokumen = $siswa->dokumen;
    $exclude = $exclude ?? [];   // jenis yang ingin disembunyikan
@endphp

<table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
    <thead class="bg-gray-50 text-left">
        <tr>
            <th class="px-4 py-2">Nama Dokumen</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2 text-center">Aksi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @foreach(\App\Models\Dokumen::ATURAN as $jenis => $info)
            @continue(in_array($jenis, $exclude, true))
            @php
                if ($jenis === 'surat_tugas') {
                    $adaFile    = \App\Models\Pengaturan::ambil('surat_tugas');
                    $urlLihat   = route('dokumen.surat-tugas.lihat');
                    $urlUnduh   = route('dokumen.surat-tugas.download');
                    $bolehLihat = in_array($user->role, $info['lihat'], true);
                    $bolehUnduh = in_array($user->role, $info['download'], true);
                } else {
                    $adaFile    = optional($dokumen)->{$jenis};
                    $urlLihat   = route('dokumen.lihat', [$siswa->id, $jenis]);
                    $urlUnduh   = route('dokumen.download', [$siswa->id, $jenis]);
                    $bolehLihat = \App\Models\Dokumen::boleh('lihat', $jenis, $user, $siswa);
                    $bolehUnduh = \App\Models\Dokumen::boleh('download', $jenis, $user, $siswa);
                }
            @endphp
            <tr>
                <td class="px-4 py-2 font-medium text-gray-800">{{ $info['label'] }}</td>
                <td class="px-4 py-2">
                    @if($adaFile)
                        <span class="text-green-600">● Tersedia</span>
                    @else
                        <span class="text-gray-400">○ Belum ada</span>
                    @endif
                </td>
                <td class="px-4 py-2 text-center space-x-2">
                    @if($adaFile)
                        @if($bolehLihat)
                            <a href="{{ $urlLihat }}" target="_blank"
                               class="px-3 py-1 rounded bg-gray-100 text-gray-700 hover:bg-gray-200">Lihat</a>
                        @endif
                        @if($bolehUnduh)
                            <a href="{{ $urlUnduh }}"
                               class="px-3 py-1 rounded bg-[#2563EB] text-white hover:opacity-90">Download PDF</a>
                        @endif
                        @unless($bolehLihat || $bolehUnduh)
                            <span class="text-gray-400 italic">Tidak ada akses</span>
                        @endunless
                    @else
                        <span class="text-gray-400 italic">—</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>