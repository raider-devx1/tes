<x-app-layout title="Dashboard">

    @php
        $cards = [
            ['Total Siswa PKL', $stats['siswa']],
            ['Guru Pembimbing', $stats['guru']],
            ['Instruktur Industri', $stats['instruktur']],
            ['Industri Mitra', $stats['industri']],
            ['Total Jurnal PKL', $stats['jurnal']],
            ['Jurnal Menunggu', $stats['jurnalPending']],
            ['Total Dokumen', $stats['dokumen']],
            ['Total Observasi', $stats['observasi']],
        ];
        $totalKehadiran = array_sum($kehadiran);
    @endphp

    {{-- ===== KARTU STATISTIK ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @foreach($cards as [$label, $value])
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 border-l-4 border-l-[#2563EB] p-5">
                <p class="text-xs uppercase tracking-wide text-gray-400">{{ $label }}</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $value }}</h3>
            </div>
        @endforeach
    </div>

    {{-- ===== GRAFIK & ANALISIS STATISTIK ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- ----- Grafik Kehadiran + Informasi Ringkas ----- --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
            <h4 class="font-semibold text-gray-800 mb-4">Grafik Kehadiran Siswa</h4>
            <canvas id="chartKehadiran" height="160"></canvas>

            <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($kehadiran as $label => $jumlah)
                    @php $persen = $totalKehadiran > 0 ? round($jumlah / $totalKehadiran * 100, 1) : 0; @endphp
                    <div class="rounded-xl bg-blue-50 p-3 text-center">
                        <p class="text-xs text-gray-500">{{ $label }}</p>
                        <p class="text-xl font-bold text-gray-800">{{ $jumlah }}</p>
                        <p class="text-[11px] text-gray-400">{{ $persen }}%</p>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-gray-500">
                Total rekap kehadiran: <span class="font-semibold text-gray-700">{{ $totalKehadiran }}</span> entri.
            </p>
        </div>

        {{-- ----- Grafik Progress Jurnal ----- --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
            <h4 class="font-semibold text-gray-800 mb-4">Progress Jurnal</h4>
            <canvas id="chartJurnal" height="160"></canvas>

            <div class="mt-5 grid grid-cols-3 gap-3">
                @foreach($jurnalStatus as $label => $jumlah)
                    <div class="rounded-xl bg-blue-50 p-3 text-center">
                        <p class="text-xs text-gray-500">{{ $label }}</p>
                        <p class="text-xl font-bold text-gray-800">{{ $jumlah }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ----- Grafik Distribusi per Jurusan ----- --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
            <h4 class="font-semibold text-gray-800 mb-4">Distribusi Siswa per Jurusan</h4>
            <canvas id="chartJurusan" height="160"></canvas>
        </div>

        {{-- ----- Grafik Rata-rata Nilai ----- --}}
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
            <h4 class="font-semibold text-gray-800 mb-4">Rata-rata Nilai PKL</h4>
            <canvas id="chartNilai" height="160"></canvas>

            <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($nilaiRata as $label => $nilai)
                    <div class="rounded-xl bg-blue-50 p-3 text-center">
                        <p class="text-xs text-gray-500">{{ $label }}</p>
                        <p class="text-xl font-bold text-gray-800">{{ $nilai }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        Chart.defaults.color = '#475569';

        new Chart(document.getElementById('chartKehadiran'), {
            type: 'bar',
            data: {
                labels: @json(array_keys($kehadiran)),
                datasets: [{ label: 'Jumlah', data: @json(array_values($kehadiran)),
                    backgroundColor: ['#1E3A8A','#2563EB','#3B82F6','#93C5FD'], borderRadius: 6 }]
            },
            options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });

        new Chart(document.getElementById('chartJurnal'), {
            type: 'bar',
            data: {
                labels: @json(array_keys($jurnalStatus)),
                datasets: [{ label: 'Jurnal', data: @json(array_values($jurnalStatus)),
                    backgroundColor: ['#1E3A8A','#2563EB','#93C5FD'], borderRadius: 6 }]
            },
            options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });

        new Chart(document.getElementById('chartJurusan'), {
            type: 'bar',
            data: {
                labels: @json($perJurusan->keys()),
                datasets: [{ label: 'Siswa', data: @json($perJurusan->values()),
                    backgroundColor: '#2563EB', borderRadius: 6 }]
            },
            options: { indexAxis:'y', plugins:{legend:{display:false}}, scales:{x:{beginAtZero:true}} }
        });

        new Chart(document.getElementById('chartNilai'), {
            type: 'bar',
            data: {
                labels: @json(array_keys($nilaiRata)),
                datasets: [{ label: 'Rata-rata (1-5)', data: @json(array_values($nilaiRata)),
                    backgroundColor: '#2563EB', borderRadius: 6 }]
            },
            options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true, suggestedMax:5}} }
        });
    </script>
    @endpush

</x-app-layout>