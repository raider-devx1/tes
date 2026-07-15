<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Rekap &amp; Penilaian (Guru Pembimbing)</h2>
            <a href="{{ route('guru.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Siswa</p>
                    <p class="mt-1 text-3xl font-bold text-black">{{ $rekap['total'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sudah Dinilai (Lengkap)</p>
                    <p class="mt-1 text-3xl font-bold text-black">{{ $rekap['sudah_dinilai'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Belum Dinilai</p>
                    <p class="mt-1 text-3xl font-bold text-black">{{ $rekap['belum_dinilai'] ?? 0 }}</p>
                </div>
            </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 border-b-2 border-[#0047d6]/15">
                            <tr>
                                <th class="px-4 py-4 font-bold text-black">No</th>
                                <th class="px-4 py-4 font-bold text-black">Nama Siswa</th>
                                <th class="px-4 py-4 font-bold text-black">Status PKL</th>
                                <th class="px-4 py-4 font-bold text-black text-center">Rata-rata</th>
                                <th class="px-4 py-4 font-bold text-black text-center">Status Penilaian</th>
                                <th class="px-4 py-4 font-bold text-black text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($siswa as $index => $s)
                                @php
                                    $daftarSkor = [
                                        optional($s->nilai)->skor_soft_skill,
                                        optional($s->nilai)->skor_hard_skill,
                                        optional($s->nilai)->skor_pengembangan,
                                        optional($s->nilai)->skor_kewirausahaan,
                                        optional($s->nilai)->skor_laporan,
                                        optional($s->nilai)->skor_presentasi,
                                    ];
                                    $nilaiLengkap = $s->nilai && ! in_array(null, $daftarSkor, true);
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-4 py-3 text-[#5b616e]">{{ $siswa->firstItem() + $index }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-black">{{ $s->name ?? '' }}</div>
                                        <div class="text-xs text-[#5b616e]">{{ $s->nisn ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(($s->status_pkl ?? '') === 'aktif')
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Aktif PKL</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Selesai/Belum</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($nilaiLengkap)
                                            <span class="font-bold text-black">{{ $s->nilai->nilai_akhir }}</span>
                                        @else
                                            <span class="text-[#5b616e] italic">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($nilaiLengkap)
                                            <span class="inline-flex items-center rounded-md bg-[#0047d6]/10 px-2 py-1 text-xs font-bold text-[#0047d6]">Lengkap</span>
                                        @elseif($s->nilai)
                                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">Belum Lengkap</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Belum Dinilai</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2" x-data="{ openModal: false }">

                                            <a href="{{ route('cetak.nilai.template', $s->id) }}" target="_blank" title="Cetak Template Kosong untuk Instruktur"
                                               class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700 transition hover:bg-gray-200">
                                                Template Kosong
                                            </a>

                                            <button @click="openModal = true" type="button"
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                                Beri Nilai
                                            </button>

                                            @if($nilaiLengkap)
                                                <a href="{{ route('cetak.nilai.guru', $s->id) }}" target="_blank" title="Cetak Format Penilaian Guru"
                                                   class="inline-flex items-center gap-1.5 rounded-lg bg-[#0047d6]/10 px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                    PDF Guru
                                                </a>
                                            @endif

                                            <div x-cloak x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
                                                <div x-show="openModal" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="openModal = false"></div>

                                                <div x-show="openModal"
                                                     x-transition:enter="transition ease-out duration-300"
                                                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                     x-transition:leave="transition ease-in duration-200"
                                                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                                     class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl text-left overflow-hidden flex flex-col max-h-[90vh]">

                                                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                                                        <h3 class="text-lg font-bold text-black">Penilaian PKL: {{ $s->name ?? '' }}</h3>
                                                        <button @click="openModal = false" class="text-gray-400 hover:text-gray-500">
                                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <div class="px-6 py-4 overflow-y-auto text-left">
                                                        <form action="{{ route('guru.nilai.store') }}" method="POST" id="form-nilai-{{ $s->id }}" enctype="multipart/form-data">
                                                            @csrf
                                                            <input type="hidden" name="user_id" value="{{ $s->id }}">
                                                            <input type="hidden" name="guru_id" value="{{ auth()->id() }}">

                                                            <div class="space-y-6">

                                                                {{-- ===== UPLOAD FOTO LEMBAR INSTRUKTUR ===== --}}
                                                                <div class="p-4 bg-[#0047d6]/5 rounded-lg border border-[#0047d6]/20">
                                                                    <label class="block text-sm font-bold text-black mb-1">Foto Lembar Penilaian Instruktur</label>
                                                                    <p class="text-xs text-[#5b616e] mb-2">Unggah foto lembar penilaian yang sudah diisi &amp; diparaf instruktur (JPG/PNG, maks 2 MB).</p>
                                                                    <input type="file" name="foto_lembar_instruktur" accept="image/*"
                                                                           class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#0047d6] file:px-4 file:py-2 file:text-white file:font-bold">
                                                                    @if(optional($s->nilai)->foto_lembar_instruktur)
                                                                        <p class="text-xs mt-2">
                                                                            <a href="{{ asset('storage/'.$s->nilai->foto_lembar_instruktur) }}" target="_blank" class="font-bold text-[#0047d6] underline">Lihat foto yang sudah diunggah</a>
                                                                            <span class="text-[#5b616e]"> (kosongkan bila tidak ingin mengganti)</span>
                                                                        </p>
                                                                    @endif
                                                                </div>

                                                                {{-- ===== BAGIAN A: NILAI DARI INSTRUKTUR ===== --}}
                                                                <h4 class="text-sm font-bold text-[#0047d6] uppercase tracking-wide">A. Nilai dari Instruktur (salin dari lembar instruktur)</h4>

                                                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                                                    <label class="block text-sm font-bold text-black mb-1">1. Internalisasi dan penerapan soft skill (0-100)</label>
                                                                    <input type="number" name="skor_soft_skill" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_soft_skill ?? '' }}" required>
                                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                                                    <textarea name="deskripsi_soft_skill" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_soft_skill ?? 'Menunjukkan kemampuan komunikasi, kerja sama tim, disiplin, tanggung jawab, etika kerja, dan kemampuan beradaptasi yang sangat baik dalam lingkungan kerja. Aktif berinisiatif serta mampu menyelesaikan tugas secara mandiri.' }}</textarea>
                                                                </div>

                                                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                                                    <label class="block text-sm font-bold text-black mb-1">2. Penerapan hard skill (0-100)</label>
                                                                    <input type="number" name="skor_hard_skill" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_hard_skill ?? '' }}" required>
                                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                                                    <textarea name="deskripsi_hard_skill" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_hard_skill ?? 'Mampu menerapkan kompetensi keahlian sesuai bidang PKL dengan sangat baik, teliti, dan mandiri sesuai standar kerja industri.' }}</textarea>
                                                                </div>

                                                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                                                    <label class="block text-sm font-bold text-black mb-1">3. Peningkatan dan pengembangan hard skill (0-100)</label>
                                                                    <input type="number" name="skor_pengembangan" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_pengembangan ?? '' }}" required>
                                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                                                    <textarea name="deskripsi_pengembangan" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_pengembangan ?? 'Menunjukkan perkembangan kompetensi yang sangat signifikan, cepat memahami keterampilan baru, serta mampu meningkatkan kualitas kerja secara mandiri.' }}</textarea>
                                                                </div>

                                                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                                                    <label class="block text-sm font-bold text-black mb-1">4. Penyiapan dan kemandirian kewirausahaan (0-100)</label>
                                                                    <input type="number" name="skor_kewirausahaan" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_kewirausahaan ?? '' }}" required>
                                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                                                    <textarea name="deskripsi_kewirausahaan" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_kewirausahaan ?? 'Menunjukkan sikap mandiri dan tanggung jawab yang sangat baik serta mulai memahami peluang dan budaya kerja kewirausahaan.' }}</textarea>
                                                                </div>

                                                                {{-- ===== BAGIAN B: NILAI DARI GURU ===== --}}
                                                                <h4 class="text-sm font-bold text-[#05b169] uppercase tracking-wide">B. Nilai dari Guru Pembimbing</h4>

                                                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                                                    <label class="block text-sm font-bold text-black mb-1">5. Penulisan laporan (0-100)</label>
                                                                    <input type="number" name="skor_laporan" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_laporan ?? '' }}" required>
                                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                                                    <textarea name="deskripsi_laporan" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_laporan ?? 'Penulisan laporan sangat rapi dan sistematis sesuai dengan pedoman penulisan laporan PKL. Tata bahasa yang digunakan baku dan mudah dipahami.' }}</textarea>
                                                                </div>

                                                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                                                    <label class="block text-sm font-bold text-black mb-1">6. Pemaparan presentasi (0-100)</label>
                                                                    <input type="number" name="skor_presentasi" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_presentasi ?? '' }}" required>
                                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                                                    <textarea name="deskripsi_presentasi" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_presentasi ?? 'Mampu memaparkan hasil PKL dengan percaya diri, sistematis, dan komunikatif serta menjawab pertanyaan dengan baik saat presentasi.' }}</textarea>
                                                                </div>

                                                                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                                                    <label class="block text-sm font-bold text-blue-900 mb-1">Catatan Akhir Penilaian</label>
                                                                    <textarea name="catatan_guru" rows="4" class="block w-full rounded-lg border-blue-300 shadow-sm focus:ring-[#0047d6] sm:text-sm">{{ optional($s->nilai)->catatan_guru ?? 'SANGAT BAIK. Terus pertahaman dan tingkatkan kemampuan Softskill dan Hardskill secara konsisten terutama pada pengetahuan dan keterampilan yang baru sehingga dapat bersaing di wirausaha maupun dunia industri.' }}</textarea>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>

                                                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 shrink-0">
                                                        <button @click="openModal = false" type="button"
                                                                class="rounded-xl px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-4 focus:ring-gray-100">
                                                            Batal
                                                        </button>
                                                        <button type="submit" form="form-nilai-{{ $s->id }}"
                                                                class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                                                            Simpan
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data siswa PKL yang Anda bimbing / cocok dengan pencarian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 p-4">
                    {!! $siswa->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout> 