<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">
                Input Absensi Siswa
            </h2>
            <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                &larr; Kembali
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-[#dee1e6] bg-white p-6 md:p-8">

                @if(session('success'))
                    <div class="mb-4 rounded-2xl border border-[#05b169]/30 bg-[#05b169]/10 px-4 py-3 text-sm font-medium text-[#05b169]">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ===== FORM FILTER ===== --}}
                <form action="{{ route('instruktur.absensi.index') }}" method="GET" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Tanggal Absen</label>
                            <input type="date" name="tanggal" value="{{ $tanggal }}"
                                   class="rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-full border-[#dee1e6] bg-[#f7f7f7] px-5 py-2.5 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Status Kehadiran</label>
                            <select name="status"
                                    class="w-full rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                                <option value="">-- Semua Status --</option>
                                <option value="Hadir" @selected(request('status') === 'Hadir')>Hadir</option>
                                <option value="Izin"  @selected(request('status') === 'Izin')>Izin</option>
                                <option value="Sakit" @selected(request('status') === 'Sakit')>Sakit</option>
                                <option value="Alpha" @selected(request('status') === 'Alpha')>Alpha</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="inline-flex items-center rounded-full bg-[#0052ff] text-white px-5 py-2.5 text-sm font-semibold transition hover:bg-[#003ecc]">Tampilkan</button>
                            <a href="{{ route('instruktur.absensi.index') }}"
                               class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Reset</a>
                        </div>
                    </div>
                </form>

                <hr class="mb-6 border-[#eef0f3]">

                {{-- ===== FORM SIMPAN ABSENSI ===== --}}
                <form action="{{ route('instruktur.absensi.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                    <div class="overflow-x-auto rounded-2xl border border-[#eef0f3]">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                    <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                                    <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                                    <th class="px-4 py-3 font-semibold">NISN</th>
                                    <th class="px-4 py-3 font-semibold">Status Kehadiran</th>
                                    <th class="px-4 py-3 text-center w-40 font-semibold">Jam Masuk</th>
                                    <th class="px-4 py-3 text-center w-40 font-semibold">Jam Pulang</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#eef0f3]">
                                @forelse($siswas as $siswa)
                                    @php
                                        // Cari data absen siswa ini di tanggal yang dipilih (jika sudah ada)
                                        $absen = $absensis->get($siswa->id);
                                    @endphp
                                    <tr class="transition hover:bg-[#f7f7f7]">
                                        <td class="px-4 py-3 text-center text-[#7c828a]">{{ $siswas->firstItem() + $loop->index }}</td>
                                        <td class="px-4 py-3 font-semibold text-[#0a0b0d]">{{ $siswa->name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]">{{ $siswa->nisn ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <select name="absensi[{{ $siswa->id }}][status]"
                                                    class="w-full rounded-lg border-[#dee1e6] text-sm focus:border-[#0052ff] focus:ring-[#0052ff]">
                                                <option value="Hadir" @selected(optional($absen)->status === 'Hadir')>Hadir</option>
                                                <option value="Izin"  @selected(optional($absen)->status === 'Izin')>Izin</option>
                                                <option value="Sakit" @selected(optional($absen)->status === 'Sakit')>Sakit</option>
                                                <option value="Alpha" @selected(optional($absen)->status === 'Alpha')>Alpha (Tanpa Keterangan)</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="time" name="absensi[{{ $siswa->id }}][jam_masuk]" value="{{ optional($absen)->jam_masuk }}"
                                                   class="w-32 rounded-lg border-[#dee1e6] text-center text-sm focus:border-[#0052ff] focus:ring-[#0052ff]">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="time" name="absensi[{{ $siswa->id }}][jam_pulang]" value="{{ optional($absen)->jam_pulang }}"
                                                   class="w-32 rounded-lg border-[#dee1e6] text-center text-sm focus:border-[#0052ff] focus:ring-[#0052ff]">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-[#a8acb3] italic">Belum ada siswa bimbingan yang di-mapping ke Anda / tidak cocok dengan pencarian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($siswas->count() > 0)
                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center rounded-full bg-[#0052ff] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">
                                Simpan Absensi
                            </button>
                        </div>
                    @endif
                </form>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $siswas->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>