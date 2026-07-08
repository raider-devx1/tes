<x-app-layout title="Dashboard">

    @php
        $cards = [
            ['label' => 'Siswa Aktif PKL',     'value' => $siswaAktif],
            ['label' => 'Guru Pembimbing',     'value' => $totalGuru],
            ['label' => 'Instruktur Industri', 'value' => $totalInstruktur],
            ['label' => 'Industri Mitra',      'value' => $totalIndustri],
        ];

        $totalKehadiran = array_sum($kehadiran);
        $persenHadir    = $totalKehadiran > 0 ? round($kehadiran['Hadir'] / $totalKehadiran * 100, 1) : 0;

        $totalJurnal    = array_sum($jurnalStatus);
        $totalCatatan   = array_sum($catatanStatus);
        $totalObservasi = array_sum($observasiStatus);

        $totalSiswaJurusan = $perJurusan->sum();
        $jumlahJurusan     = $perJurusan->count();

        $sudahDinilai = max($totalSiswa - $statusNilai['Belum'], 0);
    @endphp

    {{-- ================= KARTU RINGKASAN ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @foreach($cards as $card)
            <div class="bg-white rounded-2xl shadow-sm border border-blue-200 border-l-4 border-l-blue-600 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">{{ $card['label'] }}</p>
                <h3 class="text-3xl font-extrabold text-black mt-1">{{ $card['value'] }}</h3>
            </div>
        @endforeach
    </div>

    {{-- ================= GRAFIK + POIN INFORMASI ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- ---- Kehadiran Siswa ---- --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Informasi Kehadiran Siswa</h4>
            <canvas id="chartKehadiran" height="160"></canvas>
            <ul class="mt-4 space-y-1.5 text-sm">
                @foreach($kehadiran as $status => $jumlah)
                    <li class="flex items-center justify-between rounded-lg bg-blue-50 px-3 py-2">
                        <span class="font-medium text-black">{{ $status }}</span>
                        <span class="font-bold text-blue-700">{{ $jumlah }}</span>
                    </li>
                @endforeach
                <li class="flex items-center justify-between rounded-lg bg-blue-600 px-3 py-2 text-white">
                    <span class="font-semibold">Total absensi</span>
                    <span class="font-bold">{{ $totalKehadiran }}</span>
                </li>
                <li class="flex items-center justify-between rounded-lg bg-blue-600 px-3 py-2 text-white">
                    <span class="font-semibold">Persentase Hadir</span>
                    <span class="font-bold">{{ $persenHadir }}%</span>
                </li>
            </ul>
        </div>

        {{-- ---- Progres Jurnal ---- --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Progres Jurnal</h4>
            <canvas id="chartJurnal" height="160"></canvas>
            <ul class="mt-4 space-y-1.5 text-sm">
                @foreach($jurnalStatus as $status => $jumlah)
                    <li class="flex items-center justify-between rounded-lg bg-blue-50 px-3 py-2">
                        <span class="font-medium text-black">{{ $status }}</span>
                        <span class="font-bold text-blue-700">{{ $jumlah }} jurnal</span>
                    </li>
                @endforeach
                <li class="flex items-center justify-between rounded-lg bg-blue-600 px-3 py-2 text-white">
                    <span class="font-semibold">Total jurnal</span>
                    <span class="font-bold">{{ $totalJurnal }}</span>
                </li>
            </ul>
        </div>

        {{-- ---- Catatan Kegiatan ---- --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Catatan Kegiatan</h4>
            <canvas id="chartCatatan" height="160"></canvas>
            <ul class="mt-4 space-y-1.5 text-sm">
                @foreach($catatanStatus as $status => $jumlah)
                    <li class="flex items-center justify-between rounded-lg bg-blue-50 px-3 py-2">
                        <span class="font-medium text-black">{{ $status }}</span>
                        <span class="font-bold text-blue-700">{{ $jumlah }} catatan</span>
                    </li>
                @endforeach
                <li class="flex items-center justify-between rounded-lg bg-blue-600 px-3 py-2 text-white">
                    <span class="font-semibold">Total catatan</span>
                    <span class="font-bold">{{ $totalCatatan }}</span>
                </li>
            </ul>
        </div>

        {{-- ---- Observasi ---- --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Observasi</h4>
            <canvas id="chartObservasi" height="160"></canvas>
            <ul class="mt-4 space-y-1.5 text-sm">
                @foreach($observasiStatus as $status => $jumlah)
                    <li class="flex items-center justify-between rounded-lg bg-blue-50 px-3 py-2">
                        <span class="font-medium text-black">{{ $status }}</span>
                        <span class="font-bold text-blue-700">{{ $jumlah }} observasi</span>
                    </li>
                @endforeach
                <li class="flex items-center justify-between rounded-lg bg-blue-600 px-3 py-2 text-white">
                    <span class="font-semibold">Total observasi</span>
                    <span class="font-bold">{{ $totalObservasi }}</span>
                </li>
            </ul>
        </div>

        {{-- ---- Siswa per Jurusan ---- --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Siswa per Jurusan</h4>
            <canvas id="chartJurusan" height="160"></canvas>
            <ul class="mt-4 space-y-1.5 text-sm">
                @forelse($perJurusan as $jurusan => $jumlah)
                    <li class="flex items-center justify-between rounded-lg bg-blue-50 px-3 py-2">
                        <span class="font-medium text-black">{{ $jurusan }}</span>
                        <span class="font-bold text-blue-700">{{ $jumlah }} orang</span>
                    </li>
                @empty
                    <li class="rounded-lg bg-blue-50 px-3 py-2 text-black italic">Belum ada data jurusan.</li>
                @endforelse
                <li class="flex items-center justify-between rounded-lg bg-blue-600 px-3 py-2 text-white">
                    <span class="font-semibold">Total</span>
                    <span class="font-bold">{{ $totalSiswaJurusan }} siswa • {{ $jumlahJurusan }} jurusan</span>
                </li>
            </ul>
        </div>

        {{-- ---- Status Penilaian Siswa ---- --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Status Penilaian Siswa</h4>
            <canvas id="chartNilai" height="160"></canvas>
            <ul class="mt-4 space-y-1.5 text-sm">
                @foreach($statusNilai as $label => $jumlah)
                    <li class="flex items-center justify-between rounded-lg bg-blue-50 px-3 py-2">
                        <span class="font-medium text-black">{{ $label }}</span>
                        <span class="font-bold text-blue-700">{{ $jumlah }} siswa</span>
                    </li>
                @endforeach
                <li class="flex items-center justify-between rounded-lg bg-blue-600 px-3 py-2 text-white">
                    <span class="font-semibold">Dinilai lengkap</span>
                    <span class="font-bold">{{ $sudahDinilai }} siswa</span>
                </li>
            </ul>
        </div>
    </div>

    @push('scripts')
    <script>
        Chart.defaults.color = '#000000';
        Chart.defaults.font.weight = '500';

        const warnaBiru = ['#00ff08', '#fff700', '#ffae00', '#ff0000'];
        const warnaNilai = ['#0067f8', '#2563EB', '#3B82F6', '#fa0000'];
        const warnaJurnal = ['#00ff08', '#fff200', '#fa0000'];
       

        new Chart(document.getElementById('chartKehadiran'), {
            type: 'bar',
            data: {
                labels: @json(array_keys($kehadiran)),
                datasets: [{ label: 'Jumlah', data: @json(array_values($kehadiran)),
                    backgroundColor: warnaBiru, borderRadius: 6 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('chartJurnal'), {
            type: 'bar',
            data: {
                labels: @json(array_keys($jurnalStatus)),
                datasets: [{ label: 'Jurnal', data: @json(array_values($jurnalStatus)),
                    backgroundColor: warnaJurnal, borderRadius: 6 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('chartCatatan'), {
            type: 'bar',
            data: {
                labels: @json(array_keys($catatanStatus)),
                datasets: [{ label: 'Catatan', data: @json(array_values($catatanStatus)),
                    backgroundColor: ['#00ff08', '#fa0000'], borderRadius: 6 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('chartObservasi'), {
            type: 'bar',
            data: {
                labels: @json(array_keys($observasiStatus)),
                datasets: [{ label: 'Observasi', data: @json(array_values($observasiStatus)),
                    backgroundColor: ['#00ff08', '#fa0000'], borderRadius: 6 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('chartJurusan'), {
            type: 'bar',
            data: {
                labels: @json($perJurusan->keys()),
                datasets: [{ label: 'Siswa', data: @json($perJurusan->values()),
                    backgroundColor: '#2563EB', borderRadius: 6 }]
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('chartNilai'), {
            type: 'bar',
            data: {
                labels: @json(array_keys($statusNilai)),
                datasets: [{ label: 'Jumlah', data: @json(array_values($statusNilai)),
                    backgroundColor: warnaNilai, borderRadius: 6 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    </script>
    @endpush

</x-app-layout>