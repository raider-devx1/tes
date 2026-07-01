<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">
                Persetujuan Jurnal Siswa
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
                <form method="GET" action="{{ route('instruktur.jurnal.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-full border-[#dee1e6] bg-[#f7f7f7] px-5 py-2.5 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">
                        </div>

                        <div class="w-full md:w-56">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Status</label>
                            <select name="status"
                                    class="w-full rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                                <option value="">-- Semua Status --</option>
                                <option value="disetujui" @selected(request('status') === 'disetujui')>Sudah Disetujui</option>
                                <option value="revisi"    @selected(request('status') === 'revisi')>Revisi</option>
                                <option value="pending"   @selected(request('status') === 'pending')>Menunggu</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">
                                Cari
                            </button>
                            <a href="{{ route('instruktur.jurnal.index') }}"
                               class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL JURNAL ===== --}}
                <div class="overflow-x-auto rounded-2xl border border-[#eef0f3]">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                                <th class="px-4 py-3 font-semibold">NISN</th>
                                <th class="px-4 py-3 font-semibold">Tanggal &amp; Unit Kerja</th>
                                <th class="px-4 py-3 w-1/3 font-semibold">Deskripsi Pekerjaan</th>
                                <th class="px-4 py-3 text-center font-semibold">Foto</th>
                                <th class="px-4 py-3 text-center font-semibold">Tindakan Persetujuan</th>
                                <th class="px-4 py-3 text-center font-semibold">Cetak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
                            @forelse($jurnals as $jurnal)
                            <tr class="align-top transition hover:bg-[#f7f7f7]">
                                <td class="px-4 py-3 text-center text-[#7c828a]">{{ $jurnals->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 font-semibold text-[#0a0b0d]">{{ $jurnal->siswa->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]">{{ $jurnal->siswa->nisn ?? '-' }}</td>
                                <td class="px-4 py-3 text-[#5b616e]">
                                    {{ \Carbon\Carbon::parse($jurnal->hari_tanggal)->translatedFormat('d M Y') }} <br>
                                    <span class="text-xs text-[#a8acb3]">{{ $jurnal->unit_kerja }}</span>
                                </td>
                                <td class="px-4 py-3 text-[#5b616e]">{{ $jurnal->deskripsi_pekerjaan }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($jurnal->dokumentasi)
                                        <a href="{{ asset('storage/'.$jurnal->dokumentasi) }}" target="_blank"
                                           class="text-sm font-semibold text-[#0052ff] hover:text-[#003ecc]">Lihat</a>
                                    @else
                                        <span class="text-[#a8acb3]">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('instruktur.jurnal.update', $jurnal->id) }}" method="POST" class="flex flex-col gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="status_persetujuan"
                                                class="w-full rounded-lg border-[#dee1e6] text-sm focus:border-[#0052ff] focus:ring-[#0052ff]">
                                            <option value="pending"   @selected($jurnal->status_persetujuan === 'pending')>Menunggu</option>
                                            <option value="disetujui" @selected($jurnal->status_persetujuan === 'disetujui')>Setujui</option>
                                            <option value="revisi"    @selected($jurnal->status_persetujuan === 'revisi')>Revisi</option>
                                        </select>
                                        <textarea name="catatan_instruktur" rows="2" placeholder="Catatan/Feedback..."
                                                  class="w-full rounded-lg border-[#dee1e6] text-sm focus:border-[#0052ff] focus:ring-[#0052ff]">{{ $jurnal->catatan_instruktur }}</textarea>
                                        <button type="submit"
                                                class="inline-flex items-center justify-center rounded-full bg-[#0052ff] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#003ecc]">
                                            Simpan
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('cetak.jurnal', $jurnal->siswa_id) }}" target="_blank"
                                       class="inline-flex items-center rounded-full bg-[#eef0f3] px-3 py-1.5 text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">PDF</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-[#a8acb3] italic">Belum ada jurnal dari siswa bimbingan Anda.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $jurnals->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>