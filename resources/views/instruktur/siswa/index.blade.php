<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Ruang Monitoring &amp; Daftar Siswa</h2>
          <a href="{{ route('instruktur.dashboard') }}"
   class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
    Kembali ke Dashboard
</a>
        </div>
    </x-slot>

   

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
             <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
       
        <div class="flex items-center gap-3 rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm transition hover:border-[#0047d6]/40 sm:gap-4 sm:p-5">
          
            <div class="min-w-0">
                <p class="truncate text-xs font-bold uppercase tracking-wide text-[#0047d6] sm:text-sm">Aktif</p>
                <p class="mt-0.5 text-2xl font-extrabold leading-none text-black sm:text-3xl">{{ $rekap['aktif'] }}</p>
            </div>
        </div>
       
       
    </div>
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                <h3 class="text-lg font-bold text-black mb-6">Siswa Bimbingan Industri Anda</h3>

                {{-- ===== FORM SEARCH & FILTER STATUS ===== --}}
                <form method="GET" action="{{ route('instruktur.siswa.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                Cari (Nama / NISN / Kelas / Jurusan)
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
                            <a href="{{ route('instruktur.siswa.index') }}"
                               class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL MONITORING SISWA ===== --}}
                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1000px] text-sm text-left table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold w-44">Nama Siswa</th>
                                <th class="px-4 py-3 font-bold w-28">NISN</th>
                                <th class="px-4 py-3 font-bold w-20">Kelas</th>
                                <th class="px-4 py-3 font-bold w-36">Jurusan</th>
                                <th class="px-4 py-3 font-bold w-44">Guru Pembimbing</th>
                                <th class="px-4 py-3 text-center font-bold w-24">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-[45%]">Aksi Navigasi internal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse($siswas as $siswa)
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $siswas->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 font-bold text-black break-words">{{ $siswa->name }}</td>
                                    <td class="px-4 py-3 font-medium text-black whitespace-nowrap">{{ $siswa->nisn }}</td>
                                    <td class="px-4 py-3 font-medium text-black">{{ $siswa->kelas }}</td>
                                    <td class="px-4 py-3 font-medium text-black break-words">{{ $siswa->jurusan }}</td>
                                    <td class="px-4 py-3 font-medium text-black break-words">{{ $siswa->guru->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php $sp = $siswa->status_pkl ?? 'belum'; @endphp
                                        @if($sp === 'aktif')
                                            <span class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white">Aktif</span>
                                        @elseif($sp === 'selesai')
                                            <span class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1 text-xs font-bold text-white">Selesai</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white">Belum</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            <a href="{{ route('instruktur.jurnal.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                Validasi Jurnal
                                            </a>
                                            <a href="{{ route('instruktur.catatan.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                Persetujuan Catatan
                                            </a>
                                            <a href="{{ route('instruktur.observasi.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                Persetujuan Observasi
                                            </a>
                                            <a href="{{ route('instruktur.absensi.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                Input Absensi
                                            </a>
                                            <a href="{{ route('instruktur.nilai.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                Lembar Penilaian PKL
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">
                                        Tidak ada siswa yang cocok dengan pencarian / belum ada siswa bimbingan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== PAGINATION NAVIGATION ===== --}}
                <div class="mt-4">
                    {!! $siswas->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>