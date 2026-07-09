<x-app-layout>
    <x-slot name="header">
       <div class="flex items-center justify-between gap-4">
        <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">
            Daftar Siswa Bimbingan
        </h2>
        <a href="{{ route('guru.dashboard') }}"
           class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
            Kembali ke Dashboard
        </a>
    </div>
    </x-slot>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- ===== CARD REKAP INFORMASI (LAYOUT UTAMA) ===== -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
               
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Siswa Aktif</p>
                    <p class="mt-2 text-4xl font-bold text-[#05b169]">{{ $rekap['aktif'] }}</p>
                    <p class="mt-1 text-sm font-medium text-[#5b616e]">Sedang menjalani PKL.</p>
                </div>
                
               
            </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                <h3 class="text-lg font-bold text-black mb-6">Siswa PKL Anda</h3>

                <!-- ===== FORM FILTER ===== -->
                <form method="GET" action="{{ route('guru.siswa.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                Cari (Nama / NISN / Kelas / Jurusan / Instruktur)
                            </label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik kata kunci..."
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>

                       

                       

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                                Cari
                            </button>
                            <a href="{{ route('guru.siswa.index') }}"
                               class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                <!-- ===== TABEL DATA ===== -->
                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1100px] text-left text-sm">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-3 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-3 py-3 font-bold">Nama Siswa</th>
                                <th class="px-3 py-3 font-bold">NISN</th>
                                <th class="px-3 py-3 font-bold">Kelas</th>
                                <th class="px-3 py-3 font-bold">Jurusan</th>
                                <th class="px-3 py-3 font-bold">Nama Instruktur</th>
                                <th class="px-3 py-3 font-bold">Tempat Industri</th>
                                
                                <th class="px-3 py-3 text-center font-bold">Status</th>
                                <th class="px-3 py-3 text-center font-bold">Aksi Monitoring</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse($siswas as $siswa)
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-3 py-3 text-center font-semibold text-black">{{ $siswas->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-3 font-bold text-black break-words">{{ $siswa->name }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap font-medium text-black">{{ $siswa->nisn ?? '-' }}</td>
                                    <td class="px-3 py-3 font-medium text-black">{{ $siswa->kelas ?? '-' }}</td>
                                    <td class="px-3 py-3 font-medium text-black">{{ $siswa->jurusan ?? '-' }}</td>
                                    <td class="px-3 py-3 font-medium text-black">{{ optional($siswa->instruktur)->name ?? '-' }}</td>
                                    <td class="px-3 py-3 font-medium text-black">{{ optional($siswa->perusahaan)->nama_perusahaan ?? '-' }}</td>
                                    
                                    <td class="px-3 py-3 text-center">
                                        @php $sp = $siswa->status_pkl ?? 'belum'; @endphp
                                        @if($sp === 'aktif')
                                            <span class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white">Aktif</span>
                                        @elseif($sp === 'selesai')
                                            <span class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1 text-xs font-bold text-white">Selesai</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white">Belum</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            <a href="{{ route('guru.catatan.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Catatan</a>
                                            <a href="{{ route('guru.observasi.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Observasi</a>
                                            <a href="{{ route('guru.nilai.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Rekap Nilai</a>
                                            <a href="{{ route('guru.monitoring.jurnal', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Jurnal</a>
                                            <a href="{{ route('guru.monitoring.absensi', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Absensi</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">
                                        Tidak ada siswa yang cocok dengan pencarian / belum ada siswa bimbingan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- ===== PAGINATION ===== -->
                <div class="mt-4">
                    {!! $siswas->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>