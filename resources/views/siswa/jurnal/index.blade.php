<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Jurnal Kegiatan Harian</h2>
            <a href="{{ route('siswa.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                 Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <style>[x-cloak]{display:none!important;}</style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fotoBuktiPicker', () => ({
                previewUrl: null,
                fileName: '',
                pilih(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    // Salin file ke input utama yang bernama "foto_bukti"
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    this.$refs.finalInput.files = dt.files;
                    this.fileName = file.name;
                    if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                    this.previewUrl = URL.createObjectURL(file);
                },
            }));
        });
    </script>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                <div class="flex flex-col sm:flex-row sm:flex-wrap sm:justify-between sm:items-center gap-3 mb-6">
                    <h3 class="text-lg font-bold tracking-tight text-black">Riwayat Jurnal Saya</h3>
                    <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                        <a href="{{ route('siswa.jurnal.create') }}"
                           class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                             Tambah Jurnal
                        </a>
                        <a href="{{ route('cetak.jurnal.semua') }}" target="_blank"
                           class="inline-flex items-center justify-center gap-1.5 rounded-xl border-2 border-[#0047d6] bg-white px-6 py-3.5 text-base font-bold text-[#0047d6] shadow-sm transition hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                            Cetak Semua PDF
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                         {{ session('success') }} 
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">
                         {{ session('error') }} 
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li> {{ $error }} </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="GET" action="{{ route('siswa.jurnal.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                               class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                        <select name="status"
                                class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            <option value="">Semua Status</option>
                            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                            <option value="diajukan" @selected(request('status') === 'diajukan')>Diajukan</option>
                            <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                    <a href="{{ route('siswa.jurnal.index') }}"
                       class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                </form>

                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[960px] text-left text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                                <th class="px-4 py-3 font-bold w-[34%]">Unit Kerja</th>
                                <th class="px-4 py-3 font-bold w-1/5">Catatan Instruktur</th>
                                <th class="px-4 py-3 font-bold w-32">Foto Kegiatan</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-52">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse($jurnals as $jurnal)
                            <tr class="align-top transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 text-center font-semibold text-black"> {{ $jurnals->firstItem() + $loop->index }} </td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                     {{ $jurnal->hari_tanggal->format('d/m/Y') }} 
                                </td>

                                <td class="px-4 py-3 text-black break-words">
                                    @php $items = $jurnal->items; @endphp
                                    @if($items->count())
                                        <div x-data="{ open: false }">
                                            <div class="flex items-start gap-1.5">
                                                <span class="font-bold text-[#0047d6]">1.</span>
                                                <span class="font-medium break-words"> {{ $items->first()->unit_kerja }} </span>
                                            </div>

                                            @if($items->count() > 1)
                                                <button type="button" @click="open = !open"
                                                        class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20 focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">
                                                    <span x-show="!open">+ {{ $items->count() - 1 }} pekerjaan lainnya</span>
                                                    <span x-show="open" style="display:none;">Sembunyikan</span>
                                                    <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                         stroke-width="2.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>

                                                <ol start="2" x-show="open" x-cloak x-transition
                                                    class="mt-2 list-decimal list-inside space-y-0.5 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                    @foreach($items->slice(1) as $it)
                                                        <li class="break-words"> {{ $it->unit_kerja }} </li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[#5b616e]">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-black break-words">
                                    @if($jurnal->catatan_instruktur)
                                        <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-xs font-medium italic text-black">
                                             {{ $jurnal->catatan_instruktur }} 
                                        </div>
                                    @else
                                        <span class="text-[#5b616e]">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @php $fotos = $jurnal->items->whereNotNull('dokumentasi')->values(); @endphp
                                    @if($fotos->count())
                                        <div class="flex flex-col gap-1.5">
                                            @foreach($fotos as $k => $it)
                                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                                    <span class="text-xs font-semibold text-black">Foto {{ $k + 1 }} </span>
                                                    <a href="{{ asset('storage/'.$it->dokumentasi) }}" target="_blank"
                                                       class="inline-flex items-center rounded-full bg-[#0047d6] px-2.5 py-1 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                        Lihat
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-sm text-[#5b616e]">Tidak ada</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($jurnal->status === 'disetujui')
                                        <span class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white">Disetujui</span>
                                    @elseif($jurnal->status === 'diajukan')
                                        <span class="inline-flex items-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white">Diajukan</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-[#5b616e] px-3 py-1 text-xs font-bold text-white">Draft</span>
                                    @endif

                                    @if($jurnal->foto_bukti)
                                        <a href="{{ asset('storage/'.$jurnal->foto_bukti) }}" target="_blank"
                                           class="mt-1.5 inline-flex items-center text-[11px] font-bold text-[#0047d6] hover:underline">
                                            Lihat Bukti Fisik
                                        </a>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-center gap-2">

                                        <a href="{{ route('cetak.jurnal', $jurnal->id) }}" target="_blank"
                                           class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                             {{ $jurnal->status === 'disetujui' ? 'PDF Final' : 'Cetak Draf' }} 
                                        </a>

                                        @if($jurnal->status !== 'disetujui')
                                            <a href="{{ route('siswa.jurnal.edit', $jurnal->id) }}"
                                               class="inline-flex items-center rounded-full bg-[#0047d6]/10 px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('siswa.jurnal.destroy', $jurnal->id) }}"
                                                  data-confirm="Hapus jurnal {{ $jurnal->hari_tanggal->format('d/m/Y') }} ?"
                                                  data-confirm-text="Data jurnal yang dihapus tidak dapat dikembalikan."
                                                  data-confirm-yes="Ya, hapus">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-medium">Hapus</button>
                                            </form>
                                        @endif

                                        @if($jurnal->status === 'draft')
                                            <div x-data="{ openAjukan: false }" class="inline">
                                                <button type="button" @click="openAjukan = true"
                                                        class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049a5b]">
                                                    Ajukan
                                                </button>

                                                <div x-show="openAjukan" x-cloak
                                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                                     @keydown.escape.window="openAjukan = false">
                                                    <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl text-left"
                                                         @click.outside="openAjukan = false">

                                                        <div class="flex items-center justify-between border-b-2 border-[#0047d6]/15 px-5 py-3">
                                                            <h3 class="text-base font-bold text-black">
                                                                Ajukan Jurnal — {{ $jurnal->hari_tanggal->format('d/m/Y') }} 
                                                            </h3>
                                                            <button type="button" @click="openAjukan = false"
                                                                    class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                                        </div>

                                                        <form method="POST" action="{{ route('siswa.jurnal.ajukan', $jurnal->id) }}"
                                                              enctype="multipart/form-data" class="space-y-4 p-5">
                                                            @csrf
                                                            @method('PUT')

                                                            <div>
                                                                <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                                                    Catatan / Nilai dari Instruktur
                                                                </label>
                                                                <textarea name="catatan_instruktur" rows="3" required
                                                                    class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"
                                                                    placeholder="Ketik ulang catatan/nilai manual dari instruktur...">{{ old('catatan_instruktur') }}</textarea>
                                                            </div>

                                                            <div x-data="fotoBuktiPicker">
                                                                <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                                                    Foto Bukti Fisik (lembar berparaf)
                                                                </label>

                                                                <input type="file" name="foto_bukti" x-ref="finalInput" accept="image/*" class="hidden">
                                                                <input type="file" x-ref="kamera" accept="image/*" capture="environment" class="hidden" @change="pilih($event)">
                                                                <input type="file" x-ref="galeri" accept="image/*" class="hidden" @change="pilih($event)">

                                                                <div class="flex flex-wrap gap-2">
                                                                    <button type="button" @click="$refs.kamera.click()"
                                                                            class="inline-flex items-center gap-1.5 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#0038aa]">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.66-.9l.82-1.2A2 2 0 0110.07 4h3.86a2 2 0 011.66.9l.82 1.2a2 2 0 001.66.9H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                        </svg>
                                                                        Ambil Foto
                                                                    </button>
                                                                    <button type="button" @click="$refs.galeri.click()"
                                                                            class="inline-flex items-center gap-1.5 rounded-xl border-2 border-[#0047d6] bg-white px-4 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                        </svg>
                                                                        Pilih dari Galeri
                                                                    </button>
                                                                </div>

                                                                <p class="mt-1 text-[11px] font-medium text-[#5b616e]">Format: jpg/jpeg/png, maks 2MB.</p>
                                                                <p x-show="fileName" x-cloak class="mt-1 text-xs font-semibold text-[#05b169]" x-text="'Terpilih: ' + fileName"></p>

                                                                <template x-if="previewUrl">
                                                                    <img :src="previewUrl" alt="Preview bukti"
                                                                         class="mt-2 max-h-48 rounded-xl border-2 border-[#0047d6]/15 object-contain">
                                                                </template>
                                                            </div>

                                                            <div class="flex justify-end gap-2 pt-2">
                                                                <button type="button" @click="openAjukan = false"
                                                                        class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">
                                                                    Batal
                                                                </button>
                                                                <button type="submit"
                                                                        class="rounded-xl bg-[#05b169] px-5 py-2 text-sm font-bold text-white hover:bg-[#049a5b]">
                                                                    Kirim Pengajuan
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada jurnal yang diisi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {!! $jurnals->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>