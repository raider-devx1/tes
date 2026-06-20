<x-app-layout title="Monitoring Jurnal">
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Monitoring Jurnal Kegiatan</h2>
            <p class="text-sm text-gray-500">Pantau jurnal harian siswa PKL (hanya-baca).</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Total Jurnal</p>
                <p class="text-2xl font-bold text-gray-800"><?= e($rekap['total']) ?></p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Disetujui</p>
                <p class="text-2xl font-bold text-green-600"><?= e($rekap['disetujui']) ?></p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Menunggu</p>
                <p class="text-2xl font-bold text-amber-500"><?= e($rekap['pending']) ?></p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Revisi</p>
                <p class="text-2xl font-bold text-red-500"><?= e($rekap['revisi']) ?></p>
            </div>
        </div>

        <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Cari siswa</label>
                <input type="text" name="q" value="<?= e($q) ?>" placeholder="Nama / NISN"
                       class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    <option value="disetujui" <?= $status === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="revisi" <?= $status === 'revisi' ? 'selected' : '' ?>>Revisi</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Filter</button>
            <a href="<?= e(route('admin.monitoring.jurnal')) ?>" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
        </form>

        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Unit Kerja</th>
                            <th class="px-4 py-3">Deskripsi Pekerjaan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($jurnal as $j)
                            @php
                                $badge = match($j->status_persetujuan) {
                                    'disetujui' => 'bg-green-50 text-green-700',
                                    'revisi'    => 'bg-red-50 text-red-600',
                                    default     => 'bg-amber-50 text-amber-700',
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-4 py-3 whitespace-nowrap"><?= e(\Carbon\Carbon::parse($j->hari_tanggal)->format('d M Y')) ?></td>
                                <td class="px-4 py-3 font-medium text-gray-800"><?= e($j->siswa->name ?? '-') ?></td>
                                <td class="px-4 py-3"><?= e($j->unit_kerja ?? '-') ?></td>
                                <td class="px-4 py-3 text-gray-600"><?= e(\Illuminate\Support\Str::limit($j->deskripsi_pekerjaan, 80)) ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium <?= e($badge) ?>"><?= e(ucfirst($j->status_persetujuan)) ?></span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Tidak ada data jurnal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div><?= $jurnal->links() ?></div>
    </div>
</x-app-layout>