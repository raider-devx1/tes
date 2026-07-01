<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dokumen Siswa Bimbingan</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <p class="text-sm text-gray-500">Lihat & unduh dokumen siswa bimbingan Anda sesuai hak akses.</p>

            @php
                $suratTugas   = \App\Models\Pengaturan::ambil('surat_tugas');
                $aturanST     = \App\Models\Dokumen::ATURAN['surat_tugas'];
                $bolehLihatST = in_array(auth()->user()->role, $aturanST['lihat'], true);
                $bolehUnduhST = in_array(auth()->user()->role, $aturanST['download'], true);
            @endphp

            {{-- Kartu Surat Tugas global --}}
            <div class="bg-white rounded-xl border border-blue-100 p-5">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">📄 Surat Tugas PKL</h3>
                        <p class="text-xs text-gray-500 mt-1">Berkas resmi dari Admin — berlaku sebagai acuan untuk <strong>semua</strong> siswa bimbingan.</p>
                        @if($suratTugas)
                            <span class="inline-block mt-2 text-xs text-green-600">● Tersedia</span>
                        @else
                            <span class="inline-block mt-2 text-xs text-gray-400">○ Belum diunggah Admin</span>
                        @endif
                    </div>
                    <div class="flex gap-2 shrink-0">
                        @if($suratTugas && $bolehLihatST)
                            <a href="{{ route('dokumen.surat-tugas.lihat') }}" target="_blank"
                               class="px-3 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200 transition">Lihat</a>
                        @endif
                        @if($suratTugas && $bolehUnduhST)
                            <a href="{{ route('dokumen.surat-tugas.download') }}"
                               class="px-3 py-2 rounded-lg bg-[#2563EB] text-white text-sm hover:bg-blue-700 transition">Download</a>
                        @endif
                        @if(!$suratTugas)
                            <span class="text-xs text-gray-400 italic self-center">Menunggu unggahan Admin</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Filter: pencarian nama/NISN + dropdown status --}}
            <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-gray-500 mb-1">Cari siswa</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama / NISN"
                           class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Status Dokumen</label>
                    <select name="status" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        <option value="">Semua</option>
                        <option value="lengkap" @selected(request('status') === 'lengkap')>Lengkap</option>
                        <option value="sebagian" @selected(request('status') === 'sebagian')>Sebagian</option>
                        <option value="belum" @selected(request('status') === 'belum')>Belum</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700 transition">Filter</button>
                <a href="{{ route('guru.dokumen.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50 transition inline-block text-center">Reset</a>
            </form>

            {{-- Tabel dokumen per siswa --}}
            <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-50 text-gray-600 text-left">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">NISN</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3">Dokumen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($siswa as $s)
                                @php
                                    $d = $s->dokumen;
                                    $punyaLaporan = $d && $d->laporan_akhir;
                                    $punyaSurat   = $d && $d->surat_penerimaan;
                                    if ($punyaLaporan && $punyaSurat) { $stLabel = 'Lengkap';  $stClass = 'bg-green-50 text-green-700'; }
                                    elseif ($punyaLaporan || $punyaSurat) { $stLabel = 'Sebagian'; $stClass = 'bg-amber-50 text-amber-700'; }
                                    else { $stLabel = 'Belum'; $stClass = 'bg-red-50 text-red-600'; }
                                @endphp
                                <tr class="hover:bg-blue-50/40 align-top transition">
                                    <td class="px-4 py-3 text-center text-gray-500">{{ $siswa->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $s->name }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $s->nisn ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $s->kelas ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $stClass }}">{{ $stLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @include('partials.dokumen-aksi', ['siswa' => $s, 'exclude' => ['surat_tugas']])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">Belum ada siswa bimbingan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            <div class="mt-2">
                {!! $siswa->links() !!}
            </div>
        </div>
    </div>
</x-app-layout>