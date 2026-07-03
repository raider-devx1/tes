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

    <!-- agar x-cloak tidak berkedip sebelum Alpine siap -->
    <style>[x-cloak]{display:none !important;}</style>

    <div class="py-12"
         x-data="{
            open: false,
            action: '',
            status: 'pending',
            catatan: '',
            siswa: '',
            tanggal: '',
            openModal(d){
                this.action  = d.action;
                this.status  = d.status;
                this.catatan = d.catatan ?? '';
                this.siswa   = d.siswa;
                this.tanggal = d.tanggal;
                this.open    = true;
            }
         }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-[#dee1e6] bg-white p-6 md:p-8">

                @if(session('success'))
                    <div class="mb-4 rounded-2xl border border-[#05b169]/30 bg-[#05b169]/10 px-4 py-3 text-sm font-medium text-[#05b169]">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- ====== TOOLBAR ATAS: CETAK SEMUA PDF ====== -->
                <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-[#0a0b0d]">Cetak Jurnal Bimbingan</h3>
                        <p class="text-xs text-[#7c828a]">
                            Tombol <span class="font-semibold">Cetak Semua PDF</span> otomatis mencetak jurnal
                            <span class="font-semibold">
                                @if(request('tanggal'))
                                    tanggal {{ \Carbon\Carbon::parse(request('tanggal'))->translatedFormat('d F Y') }} 
                                @else
                                    hari ini ( {{ \Carbon\Carbon::today()->translatedFormat('d F Y') }} )
                                @endif
                            </span>
                            — 1 siswa per halaman. Untuk tanggal sebelumnya, gunakan filter tanggal di bawah.
                        </p>
                    </div>

                    <a href="{{ route('cetak.jurnal.semua', request('tanggal') ? ['tanggal' => request('tanggal')] : []) }}"
                       target="_blank"
                       class="inline-flex items-center justify-center gap-2 rounded-full bg-[#05b169] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#04965a]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                        </svg>
                        Cetak Semua PDF
                    </a>
                </div>

                <!-- ====== FILTER ====== -->
                <form method="GET" action="{{ route('instruktur.jurnal.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-full border-[#dee1e6] bg-[#f7f7f7] px-5 py-2.5 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">
                        </div>

                        <div class="w-full md:w-48">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Tanggal</label>
                            <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                                   class="w-full rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
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

                <!-- ====== TABEL ====== -->
                <div class="overflow-x-auto rounded-2xl border border-[#eef0f3]">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                                <th class="px-4 py-3 font-semibold">NISN</th>
                                <th class="px-4 py-3 font-semibold">Tanggal &amp; Unit Kerja</th>
                               
                                <th class="px-4 py-3 text-center font-semibold">Foto</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                                <th class="px-4 py-3 text-center font-semibold">Tindakan Persetujuan</th>
                                <th class="px-4 py-3 text-center font-semibold">Cetak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
                            @forelse($jurnals as $jurnal)
                            @php
                                $statusMap = [
                                    'pending'   => ['label' => 'Menunggu',  'class' => 'bg-amber-100 text-amber-700'],
                                    'disetujui' => ['label' => 'Disetujui', 'class' => 'bg-emerald-100 text-emerald-700'],
                                    'revisi'    => ['label' => 'Revisi',    'class' => 'bg-rose-100 text-rose-700'],
                                ];
                                $st = $statusMap[$jurnal->status_persetujuan] ?? $statusMap['pending'];
                                $sudahDivalidasi = !is_null($jurnal->disetujui_oleh);
                            @endphp
                            <tr class="align-top transition hover:bg-[#f7f7f7]">
                                <td class="px-4 py-3 text-center text-[#7c828a]"> {{ $loop->iteration + ($jurnals->firstItem() - 1) }} </td>
                                <td class="px-4 py-3 font-semibold text-[#0a0b0d]"> {{ $jurnal->siswa->name ?? '-' }} </td>
                                <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]"> {{ $jurnal->siswa->nisn ?? '-' }} </td>
                                <td class="px-4 py-3 text-[#5b616e]">
                                     {{ \Carbon\Carbon::parse($jurnal->hari_tanggal)->translatedFormat('d M Y') }}  <br>
                                    <span class="text-xs text-[#a8acb3]"> {{ $jurnal->unit_kerja }} </span>
                                </td>
                                <td class="px-4 py-3 text-[#5b616e]"> {{ $jurnal->deskripsi_pekerjaan }} </td>
                                <td class="px-4 py-3 text-center">
                                    @if($jurnal->dokumentasi)
                                        <a href="{{ asset('storage/'.$jurnal->dokumentasi) }}" target="_blank"
                                           class="text-sm font-semibold text-[#0052ff] hover:text-[#003ecc]">Lihat</a>
                                    @else
                                        <span class="text-[#a8acb3]">-</span>
                                    @endif
                                </td>

                                <!-- ====== KOLOM STATUS ====== -->
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $st['class'] }}">
                                         {{ $st['label'] }} 
                                    </span>
                                </td>

                                <!-- ====== TINDAKAN: SATU TOMBOL VALIDASI JURNAL ====== -->
                                <td class="px-4 py-3 text-center">
                                    <button type="button"
                                            @click="openModal({
                                                action: '{{ route('instruktur.jurnal.update', $jurnal->id) }}',
                                                status: '{{ $jurnal->status_persetujuan }}',
                                                catatan: @js($jurnal->catatan_instruktur),
                                                siswa: @js($jurnal->siswa->name ?? '-'),
                                                tanggal: '{{ \Carbon\Carbon::parse($jurnal->hari_tanggal)->translatedFormat('d M Y') }}'
                                            })"
                                            class="inline-flex items-center justify-center rounded-full px-4 py-1.5 text-xs font-semibold text-white transition {{ $sudahDivalidasi ? 'bg-[#0a0b0d] hover:bg-black' : 'bg-[#0052ff] hover:bg-[#003ecc]' }}">
                                         {{ $sudahDivalidasi ? 'Perbarui' : 'Validasi Jurnal' }} 
                                    </button>
                                </td>

                                <!-- ====== CETAK PER-ORANGAN ====== -->
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('cetak.jurnal', ['siswa_id' => $jurnal->siswa_id, 'jurnal_id' => $jurnal->id]) }}"
                                       target="_blank"
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

                <!-- ====== PAGINATION ====== -->
                <div class="mt-4">
                    {!! $jurnals->links() !!}
                </div>

            </div>
        </div>

        <!-- ====== MODAL / POP-UP VALIDASI JURNAL ====== -->
        <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition.opacity>
            <!-- backdrop -->
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>

            <!-- card -->
            <div class="relative w-full max-w-md rounded-3xl border border-[#dee1e6] bg-white p-6 shadow-xl"
                 @keydown.escape.window="open = false">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-[#0a0b0d]">Validasi Jurnal</h3>
                        <p class="text-xs text-[#7c828a]">
                            <span x-text="siswa"></span> • <span x-text="tanggal"></span>
                        </p>
                    </div>
                    <button type="button" @click="open = false"
                            class="rounded-full p-1 text-[#7c828a] transition hover:bg-[#eef0f3] hover:text-[#0a0b0d]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="action" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Status Persetujuan</label>
                        <select name="status_persetujuan" x-model="status"
                                class="w-full rounded-xl border-[#dee1e6] text-sm focus:border-[#0052ff] focus:ring-[#0052ff]">
                            <option value="pending">Menunggu</option>
                            <option value="disetujui">Setujui</option>
                            <option value="revisi">Revisi</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Catatan / Feedback</label>
                        <textarea name="catatan_instruktur" x-model="catatan" rows="3"
                                  placeholder="Tulis catatan untuk siswa (opsional)..."
                                  class="w-full rounded-xl border-[#dee1e6] text-sm focus:border-[#0052ff] focus:ring-[#0052ff]"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false"
                                class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                            Batal
                        </button>
                        <button type="submit"
                                class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>