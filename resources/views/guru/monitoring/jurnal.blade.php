<x-app-layout title="Monitoring Jurnal">
    <div class="max-w-7xl mx-auto space-y-6 py-12 sm:px-6 lg:px-8">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-[#0a0b0d]">Monitoring Jurnal Siswa</h2>
                <p class="text-sm text-[#5b616e] mt-1">Pantau jurnal kegiatan siswa bimbingan Anda (hanya-baca).</p>
            </div>
            <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6] shrink-0">
                &larr; Kembali
            </button>
        </div>

        <!-- ===== KARTU REKAP ===== -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="rounded-2xl border border-[#dee1e6] bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Total Jurnal</p>
                <p class="mt-1 text-2xl font-bold text-[#0a0b0d]"> {{ $rekap['total'] }} </p>
            </div>
            <div class="rounded-2xl border border-[#dee1e6] bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Disetujui</p>
                <p class="mt-1 text-2xl font-bold text-[#05b169]"> {{ $rekap['disetujui'] }} </p>
            </div>
            <div class="rounded-2xl border border-[#dee1e6] bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Pending</p>
                <p class="mt-1 text-2xl font-bold text-[#f4b000]"> {{ $rekap['pending'] }} </p>
            </div>
            <div class="rounded-2xl border border-[#dee1e6] bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Revisi</p>
                <p class="mt-1 text-2xl font-bold text-[#cf202f]"> {{ $rekap['revisi'] }} </p>
            </div>
        </div>

        <!-- ===== FILTER ===== -->
        <form method="GET" action="{{ route('guru.monitoring.jurnal') }}" class="rounded-2xl border border-[#dee1e6] bg-white p-5 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Cari (Nama / NISN)</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                       class="w-full rounded-full border-[#dee1e6] bg-[#f7f7f7] px-5 py-2.5 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Status</label>
                <select name="status" class="rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                    <option value="">Semua</option>
                    <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="revisi" @selected(request('status') === 'revisi')>Revisi</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                       class="rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
            </div>
            <button type="submit"
                    class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">Filter</button>
            <a href="{{ route('guru.monitoring.jurnal') }}"
               class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Reset</a>
        </form>

        <!-- ===== TOMBOL CETAK SEMUA PDF ===== -->
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs text-[#7c828a]">
                Tanpa filter tanggal, "Cetak Semua PDF" hanya mencetak jurnal <strong>hari ini</strong>. Pilih tanggal di filter untuk mencetak tanggal tertentu.
            </p>
            <a href="{{ route('cetak.jurnal.semua', ['tanggal' => request('tanggal')]) }}" target="_blank"
               class="inline-flex items-center gap-2 rounded-full bg-[#e11d48] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#be123c] shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                </svg>
                Cetak Semua PDF
            </a>
        </div>

        <!-- ===== TABEL ===== -->
        <div class="rounded-2xl border border-[#dee1e6] bg-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                            <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">NISN</th>
                            <th class="px-4 py-3 font-semibold">Unit Kerja</th>
                           
                            <th class="px-4 py-3 text-center font-semibold">Status</th>
                            <th class="px-4 py-3 text-center font-semibold">Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eef0f3]">
                        @forelse ($jurnals as $j)
                            @php
                                $badge = match($j->status_persetujuan) {
                                    'disetujui' => 'bg-[#05b169]/10 text-[#05b169]',
                                    'pending'   => 'bg-[#f4b000]/10 text-[#f4b000]',
                                    'revisi'    => 'bg-[#cf202f]/10 text-[#cf202f]',
                                    default     => 'bg-[#eef0f3] text-[#5b616e]',
                                };
                            @endphp
                            <tr class="align-top transition hover:bg-[#f7f7f7]">
                                <td class="px-4 py-3 text-center text-[#7c828a]"> {{ $jurnals->firstItem() + $loop->index }} </td>
                                <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]"> {{ \Carbon\Carbon::parse($j->hari_tanggal)->translatedFormat('d M Y') }} </td>
                                <td class="px-4 py-3 font-semibold text-[#0a0b0d]"> {{ $j->siswa->name }} </td>
                                <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]"> {{ $j->siswa->nisn ?? '-' }} </td>
                                <td class="px-4 py-3 text-[#5b616e]"> {{ $j->unit_kerja ?? '-' }} </td>
                                
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}"> {{ ucfirst($j->status_persetujuan) }} </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <!-- Cetak per orang (seluruh jurnal siswa ini) -->
                                    <a href="{{ route('cetak.jurnal', $j->siswa_id) }}" target="_blank"
                                       class="inline-flex items-center rounded-full bg-[#eef0f3] px-3 py-1.5 text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-[#a8acb3] italic">Tidak ada data jurnal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== PAGINATION ===== -->
        <div>
            {!! $jurnals->links() !!}
        </div>
    </div>
</x-app-layout>