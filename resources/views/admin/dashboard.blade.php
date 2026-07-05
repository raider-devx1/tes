<x-app-layout title="Dashboard">

    @php
        $cards = [
            ['label' => 'Total Siswa PKL',     'value' => $totalSiswa],
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

    {{-- ================= GRAFIK BATANG ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Grafik 1: Kehadiran Siswa --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Informasi Kehadiran Siswa</h4>
            <canvas id="chartKehadiran" height="160"></canvas>
            <p class="mt-4 text-sm text-black bg-blue-50 rounded-xl p-3">
                Dari <span class="font-bold">{{ $totalKehadiran }}</span> catatan absensi, tingkat kehadiran
                (Hadir) mencapai <span class="font-bold">{{ $persenHadir }}%</span>.
                Izin {{ $kehadiran['Izin'] }}, Sakit {{ $kehadiran['Sakit'] }}, Alpha {{ $kehadiran['Alpha'] }}.
            </p>
        </div>

        {{-- Grafik 2: Progres Jurnal --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Progres Jurnal</h4>
            <canvas id="chartJurnal" height="160"></canvas>
            <p class="mt-4 text-sm text-black bg-blue-50 rounded-xl p-3">
                Total <span class="font-bold">{{ $totalJurnal }}</span> jurnal:
                <span class="font-bold">{{ $jurnalStatus['Disetujui'] }}</span> disetujui,
                <span class="font-bold">{{ $jurnalStatus['Menunggu'] }}</span> menunggu persetujuan,
                <span class="font-bold">{{ $jurnalStatus['Revisi'] }}</span> perlu revisi.
            </p>
        </div>

        {{-- Grafik 3: Catatan Kegiatan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Catatan Kegiatan</h4>
            <canvas id="chartCatatan" height="160"></canvas>
            <p class="mt-4 text-sm text-black bg-blue-50 rounded-xl p-3">
                Dari <span class="font-bold">{{ $totalCatatan }}</span> catatan,
                <span class="font-bold">{{ $catatanStatus['Disetujui'] }}</span> sudah disetujui dan
                <span class="font-bold">{{ $catatanStatus['Belum'] }}</span> belum disetujui.
            </p>
        </div>

        {{-- Grafik 4: Observasi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Observasi</h4>
            <canvas id="chartObservasi" height="160"></canvas>
            <p class="mt-4 text-sm text-black bg-blue-50 rounded-xl p-3">
                Dari <span class="font-bold">{{ $totalObservasi }}</span> observasi,
                <span class="font-bold">{{ $observasiStatus['Disetujui'] }}</span> sudah disetujui dan
                <span class="font-bold">{{ $observasiStatus['Belum'] }}</span> belum disetujui.
            </p>
        </div>

        {{-- Grafik 5: Siswa per Jurusan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Siswa per Jurusan</h4>
            <canvas id="chartJurusan" height="160"></canvas>
            <p class="mt-4 text-sm text-black bg-blue-50 rounded-xl p-3">
                <span class="font-bold">{{ $totalSiswaJurusan }}</span> siswa PKL tersebar di
                <span class="font-bold">{{ $jumlahJurusan }}</span> jurusan.
            </p>
        </div>

        {{-- Grafik 6: Status Penilaian --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
            <h4 class="font-bold text-black mb-4">Status Penilaian Siswa</h4>
            <canvas id="chartNilai" height="160"></canvas>
            <p class="mt-4 text-sm text-black bg-blue-50 rounded-xl p-3">
                Sudah masuk: <span class="font-bold">{{ $statusNilai['Laporan'] }}</span> nilai laporan,
                <span class="font-bold">{{ $statusNilai['Nilai Guru'] }}</span> nilai guru,
                <span class="font-bold">{{ $statusNilai['Instruktur'] }}</span> nilai instruktur.
                Sekitar <span class="font-bold">{{ $sudahDinilai }}</span> siswa dinilai lengkap,
                <span class="font-bold">{{ $statusNilai['Belum'] }}</span> siswa belum dinilai.
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
        Chart.defaults.color = '#000000';
        Chart.defaults.font.weight = '500';

        const warnaBiru = ['#1E3A8A', '#2563EB', '#3B82F6', '#93C5FD'];

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
                    backgroundColor: warnaBiru, borderRadius: 6 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('chartCatatan'), {
            type: 'bar',
            data: {
                labels: @json(array_keys($catatanStatus)),
                datasets: [{ label: 'Catatan', data: @json(array_values($catatanStatus)),
                    backgroundColor: ['#2563EB', '#93C5FD'], borderRadius: 6 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('chartObservasi'), {
            type: 'bar',
            data: {
                labels: @json(array_keys($observasiStatus)),
                datasets: [{ label: 'Observasi', data: @json(array_values($observasiStatus)),
                    backgroundColor: ['#2563EB', '#93C5FD'], borderRadius: 6 }]
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
                    backgroundColor: warnaBiru, borderRadius: 6 }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    </script>
    @endpush

</x-app-layout>