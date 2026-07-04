@php 
    $s = $siswa ?? null; 
@endphp

@if ($errors->any())
    <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-600 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-5">

    <!-- Identitas Siswa -->
    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Identitas Siswa</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $s?->name) }}" required
                       class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NISN <span class="text-gray-400 font-normal">(dipakai untuk login)</span></label>
                <input type="text" name="nisn" value="{{ old('nisn', $s?->nisn) }}" required
                       class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <option value="">— Pilih —</option>
                    <option value="L" {{ old('jenis_kelamin', $s?->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin', $s?->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $s?->no_hp) }}"
                       class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                <input type="text" name="kelas" value="{{ old('kelas', $s?->kelas) }}" placeholder="cth: XII RPL 1"
                       class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan</label>
                <input type="text" name="jurusan" value="{{ old('jurusan', $s?->jurusan) }}" placeholder="cth: Rekayasa Perangkat Lunak"
                       class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            </div>
        </div>
    </div>

    <!-- Foto Siswa -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Foto Siswa</label>
        @if($s && $s->foto)
            <img src="{{ asset('storage/' . $s->foto) }}" alt="foto" class="w-16 h-16 rounded-lg object-cover mb-2">
        @endif
        <input type="file" name="foto" accept="image/*"
               class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100">
        <p class="text-xs text-gray-400 mt-1">Format JPG/PNG, maks 2MB. Kosongkan jika tidak ingin mengubah.</p>
    </div>

    <!-- Pemetaan PKL -->
    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Pemetaan PKL</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode PKL</label>
                <select name="periode_id" class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <option value="">— Pilih Periode —</option>
                    @foreach($periodeList as $p)
                        <option value="{{ $p->id }}" {{ old('periode_id', $s?->periode_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}{{ $p->is_active ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status PKL</label>
                <select name="status_pkl" required class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <option value="belum" {{ old('status_pkl', $s?->status_pkl) === 'belum' ? 'selected' : '' }}>Belum</option>
                    <option value="aktif" {{ old('status_pkl', $s?->status_pkl) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="selesai" {{ old('status_pkl', $s?->status_pkl) === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Perusahaan / Tempat PKL</label>
                <select name="perusahaan_id" class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <option value="">— Pilih Perusahaan —</option>
                    @foreach($perusahaanList as $p)
                        <option value="{{ $p->id }}" {{ old('perusahaan_id', $s?->perusahaan_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_perusahaan }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Guru Pembimbing</label>
                <select name="guru_id" class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <option value="">— Pilih Guru —</option>
                    @foreach($guruList as $g)
                        <option value="{{ $g->id }}" {{ old('guru_id', $s?->guru_id) == $g->id ? 'selected' : '' }}>
                            {{ $g->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Instruktur Industri</label>
                <select name="instruktur_id" class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <option value="">— Pilih Instruktur —</option>
                    @foreach($instrukturList as $it)
                        <option value="{{ $it->id }}" {{ old('instruktur_id', $s?->instruktur_id) == $it->id ? 'selected' : '' }}>
                            {{ $it->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Akun Login -->
    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Akun Login</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password"
                       class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                @if($s && $s->exists)
                    <p class="text-xs text-gray-400 mt-1">Kosongkan jika tetap.</p>
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation"
                       class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            </div>
        </div>
    </div>

</div>