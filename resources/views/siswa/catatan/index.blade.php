<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Catatan Kegiatan</h2>
            <a href="{{ route('siswa.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                 Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <style>[x-cloak]{display:none!important}</style>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                <div class="flex flex-col sm:flex-row sm:flex-wrap sm:justify-between gap-3 mb-6">
                    <a href="{{ route('siswa.catatan.create') }}"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        Tambah Catatan
                    </a>
                    <a href="{{ route('cetak.catatan') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl border-2 border-[#0047d6] bg-white px-6 py-3.5 text-base font-bold text-[#0047d6] shadow-sm transition hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        Cetak Semua (PDF)
                    </a>
                </div>

                @if(session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                         {{ session('success') }} 
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 rounded-xl border-2 border-red-500 bg-red-500/10 px-4 py-3 text-sm font-semibold text-black">
                         {{ session('error') }} 
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 rounded-xl border-2 border-red-500 bg-red-500/10 px-4 py-3 text-sm font-semibold text-black">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li> {{ $error }} </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="GET" action="{{ route('siswa.catatan.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
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
                    <a href="{{ route('siswa.catatan.index') }}"
                       class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                </form>

                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[960px] text-left text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                                <th class="px-4 py-3 font-bold w-40">Nama Pekerjaan</th>
                                <th class="px-4 py-3 font-bold w-[20%]">Perencanaan</th>
                                <th class="px-4 py-3 font-bold w-[20%]">Pelaksanaan / Hasil</th>
                                <th class="px-4 py-3 font-bold w-[16%]">Catatan Instruktur</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($catatan as $item)
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black"> {{ $loop->iteration + ($catatan->firstItem() - 1) }} </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                         {{ $item->created_at->translatedFormat('d M Y') }} 
                                    </td>
                                    <td class="px-4 py-3 font-bold text-black break-words"> {{ $item->nama_pekerjaan }} </td>
                                    <td class="px-4 py-3 font-medium text-black break-words"> {{ $item->perencanaan_kegiatan }} </td>
                                    <td class="px-4 py-3 font-medium text-black break-words"> {{ $item->pelaksanaan_kegiatan }} </td>
                                    <td class="px-4 py-3 font-medium text-black break-words">
                                        @if($item->catatan_instruktur)
                                             {{ $item->catatan_instruktur }} 
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item->status === 'disetujui')
                                            <span class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white">Disetujui</span>
                                        @elseif($item->status === 'diajukan')
                                            <span class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1 text-xs font-bold text-white">Diajukan</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white">Draft</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-stretch gap-1.5">
                                            <a href="{{ route('cetak.catatan', ['catatan_id' => $item->id]) }}" target="_blank"
                                               class="inline-flex items-center justify-center rounded-xl bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa] focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">
                                                Cetak Draf PDF
                                            </a>

                                            @if($item->status !== 'disetujui')
                                                <div x-data="{ openAjukan: false }" class="w-full">
                                                    <button type="button" @click="openAjukan = true"
                                                            class="w-full inline-flex items-center justify-center rounded-xl bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049458] focus:outline-none focus:ring-2 focus:ring-[#05b169]/30">
                                                         {{ $item->status === 'diajukan' ? 'Ajukan Ulang' : 'Ajukan' }} 
                                                    </button>

                                                    <div x-show="openAjukan" x-cloak
                                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                                         @keydown.escape.window="openAjukan = false">
                                                        <div @click.outside="openAjukan = false" x-transition
                                                             class="w-full max-w-lg rounded-2xl bg-white shadow-xl text-left">
                                                            <div class="flex items-center justify-between border-b px-5 py-3">
                                                                <h3 class="font-bold text-black">Ajukan Bukti Fisik</h3>
                                                                <button type="button" @click="openAjukan = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                                                            </div>

                                                            <form action="{{ route('siswa.catatan.ajukan', $item->id) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                @method('PUT')

                                                                <div class="max-h-[65vh] overflow-auto p-5 space-y-4">
                                                                    <p class="rounded-lg bg-[#0047d6]/5 p-3 text-xs font-medium text-[#5b616e]">
                                                                        Cetak draf, minta paraf/catatan instruktur di lembar fisik, foto lembar tersebut, lalu unggah di sini.
                                                                    </p>

                                                                    <div>
                                                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                                                            Catatan / Nilai dari Instruktur <span class="text-red-500">*</span>
                                                                        </label>
                                                                        <textarea name="catatan_instruktur" rows="3" required
                                                                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"
                                                                                  placeholder="Ketik ulang catatan/nilai yang ditulis instruktur...">{{ old('catatan_instruktur', $item->catatan_instruktur) }}</textarea>
                                                                    </div>

                                                                    <div>
                                                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                                                            Foto Bukti Fisik (lembar berparaf) <span class="text-red-500">*</span>
                                                                        </label>
                                                                        <input type="file" name="foto_bukti"
                                                                               accept="image/*" capture="environment" required
                                                                               @change="preview = URL.createObjectURL($event.target.files[0])"
                                                                               class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#0047d6] file:text-white hover:file:bg-[#0038aa] file:cursor-pointer">
                                                                        <p class="mt-1 text-xs text-[#5b616e]">Di HP, kamera belakang akan langsung aktif. Maks 2MB (jpg/png).</p>

                                                                        <template x-if="preview">
                                                                            <img :src="preview" class="mt-3 h-40 rounded-lg border object-cover" alt="Preview bukti">
                                                                        </template>

                                                                        @if($item->foto_bukti)
                                                                            <div class="mt-3">
                                                                                <p class="text-xs text-[#5b616e] mb-1">Bukti sebelumnya:</p>
                                                                                <img src="{{ asset('storage/' . $item->foto_bukti) }}" class="h-32 rounded-lg border object-cover" alt="Bukti lama">
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <div class="flex justify-end gap-2 border-t px-5 py-3">
                                                                    <button type="button" @click="openAjukan = false"
                                                                            class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                                                                    <button type="submit"
                                                                            class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white hover:bg-[#049458]">Kirim Pengajuan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <a href="{{ route('siswa.catatan.edit', $item->id) }}"
                                                   class="inline-flex items-center justify-center rounded-xl bg-[#d98200] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#b56d00] focus:outline-none focus:ring-2 focus:ring-[#d98200]/30">
                                                    Edit
                                                </a>

                                                <form method="POST" action="{{ route('siswa.catatan.destroy', $item) }}"
                                                      onsubmit="return confirm('Hapus catatan ini? Data yang dihapus tidak dapat dikembalikan.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="w-full text-xs px-3 py-1.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 font-bold">Hapus</button>
                                                </form>
                                            @else
                                                <span class="text-center text-xs italic text-[#5b616e]">Terkunci (disetujui)</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada catatan kegiatan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {!! $catatan->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>