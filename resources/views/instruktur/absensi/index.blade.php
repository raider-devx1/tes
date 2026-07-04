<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">
                Input Absensi Siswa
            </h2>
            <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                &larr; Kembali
            </button>
        </div>
    </x-slot>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                @if(session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ===== FILTER TANGGAL / PENCARIAN / STATUS ===== --}}
                <form action="{{ route('instruktur.absensi.index') }}" method="GET" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal Absen</label>
                            <input type="date" name="tanggal" value="{{ $tanggal }}"
                                   class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status Kehadiran</label>
                            <select name="status"
                                    class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Status</option>
                                <option value="Hadir" @selected(request('status') === 'Hadir')>Hadir</option>
                                <option value="Izin"  @selected(request('status') === 'Izin')>Izin</option>
                                <option value="Sakit" @selected(request('status') === 'Sakit')>Sakit</option>
                                <option value="Alpha" @selected(request('status') === 'Alpha')>Alpha</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Tampilkan</button>
                            <a href="{{ route('instruktur.absensi.index') }}"
                               class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="mb-6 rounded-xl border-2 border-[#0047d6]/15 bg-[#0047d6]/5 px-4 py-3">
                    <p class="text-xs font-medium text-black">Mengisi absensi untuk tanggal
                        <span class="font-bold text-[#0047d6]"> {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }} </span>.
                        Ubah tanggal di filter lalu tekan <span class="font-bold">Tampilkan</span> untuk hari lain.</p>
                </div>

                {{-- ===== FORM INPUT ABSENSI ===== --}}
                <form action="{{ route('instruktur.absensi.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                    <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                        <table class="w-full min-w-[800px] text-left text-sm">
                            <thead>
                                <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                    <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                    <th class="px-4 py-3 font-bold">Nama Siswa</th>
                                    <th class="px-4 py-3 font-bold">NISN</th>
                                    <th class="px-4 py-3 font-bold">Status Kehadiran</th>
                                    <th class="px-4 py-3 text-center w-40 font-bold">Jam Masuk</th>
                                    <th class="px-4 py-3 text-center w-40 font-bold">Jam Pulang</th>
                                </tr>
                            </tr>
                            <tbody class="divide-y divide-[#0047d6]/10">
                                @forelse($siswas as $siswa)
                                    @php
                                        // Cari data absen siswa ini di tanggal yang dipilih (jika sudah ada)
                                        $absen = $absensis->get($siswa->id);
                                    @endphp
                                    <tr class="transition hover:bg-[#0047d6]/5">
                                        <td class="px-4 py-3 text-center font-semibold text-black">{{ $siswas->firstItem() + $loop->index }}</td>
                                        <td class="px-4 py-3 font-bold text-black">{{ $siswa->name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $siswa->nisn }}</td>
                                        <td class="px-4 py-3">
                                            <select name="absensi[{{ $siswa->id }}][status]"
                                                    class="w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                <option value="Hadir" @selected(optional($absen)->status === 'Hadir')>Hadir</option>
                                                <option value="Izin"  @selected(optional($absen)->status === 'Izin')>Izin</option>
                                                <option value="Sakit" @selected(optional($absen)->status === 'Sakit')>Sakit</option>
                                                <option value="Alpha" @selected(optional($absen)->status === 'Alpha')>Alpha (Tanpa Keterangan)</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="time" name="absensi[{{ $siswa->id }}][jam_masuk]" value="{{ optional($absen)->jam_masuk }}"
                                                   class="w-32 rounded-lg border-2 border-[#0047d6]/25 bg-white px-2 py-2 text-center text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="time" name="absensi[{{ $siswa->id }}][jam_pulang]" value="{{ optional($absen)->jam_pulang }}"
                                                   class="w-32 rounded-lg border-2 border-[#0047d6]/25 bg-white px-2 py-2 text-center text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada siswa bimbingan yang di-mapping ke Anda / tidak cocok dengan pencarian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($siswas->count() > 0)
                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
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