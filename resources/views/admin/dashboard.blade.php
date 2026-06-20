<x-app-layout title="Dashboard" :notif="$notifikasi">

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
    @endphp

    {{-- Card statistik (tanpa ikon) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @foreach($cards as [$label, $value])
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 border-l-4 border-l-[#2563EB] p-5">
                <p class="text-xs uppercase tracking-wide text-gray-400"> {{ $label }} </p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1"> {{ $value }} </h3>
            </div>
        @endforeach
    </div>

    {{-- Grafik --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
            <h4 class="font-semibold text-gray-800 mb-4">Grafik Kehadiran Siswa</h4>
            <canvas id="chartKehadiran" height="160"></canvas>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
            <h4 class="font-semibold text-gray-800 mb-4">Progress Jurnal</h4>
            <canvas id="chartJurnal" height="160"></canvas>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
            <h4 class="font-semibold text-gray-800 mb-4">Distribusi Siswa per Jurusan</h4>
            <canvas id="chartJurusan" height="160"></canvas>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
            <h4 class="font-semibold text-gray-800 mb-4">Rata-rata Nilai PKL</h4>
            <canvas id="chartNilai" height="160"></canvas>
        </div>
    </div>

    {{-- Panel notifikasi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold text-gray-800">Notifikasi Sistem</h4>
            <span class="text-xs px-2 py-1 rounded-full bg-[#2563EB] text-white"> {{ count($notifikasi) }} </span>
        </div>
        <div class="space-y-2">
            @forelse($notifikasi as $n)
                <div class="flex items-center gap-3 p-3 rounded-lg bg-blue-50">
                    <span class="w-2 h-2 rounded-full bg-[#2563EB] shrink-0"></span>
                    <span class="text-sm text-gray-700"> {{ $n['text'] }} </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Semua kondisi aman. Tidak ada peringatan.</p>
            @endforelse
        </div>
    </div>

    @push('scripts')
    <script>
        Chart.defaults.color = '#475569';

        new Chart(document.getElementById('chartKehadiran'), {
            type: 'bar',
            data: {
                labels: {{ Js::from(array_keys($kehadiran)) }},
                datasets: [{ label: 'Jumlah', data: {{ Js::from(array_values($kehadiran)) }},
                    backgroundColor: ['#1E3A8A','#2563EB','#3B82F6','#93C5FD'], borderRadius: 6 }]
            },
            options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
        });

        new Chart(document.getElementById('chartJurnal'), {
            type: 'doughnut',
            data: {
                labels: {{ Js::from(array_keys($jurnalStatus)) }},
                datasets: [{ data: {{ Js::from(array_values($jurnalStatus)) }},
                    backgroundColor: ['#1E3A8A','#2563EB','#93C5FD'] }]
            },
            options: { plugins:{legend:{position:'bottom'}} }
        });

        new Chart(document.getElementById('chartJurusan'), {
            type: 'bar',
            data: {
                labels: {{ Js::from($perJurusan->keys()) }},
                datasets: [{ label: 'Siswa', data: {{ Js::from($perJurusan->values()) }},
                    backgroundColor: '#2563EB', borderRadius: 6 }]
            },
            options: { indexAxis:'y', plugins:{legend:{display:false}}, scales:{x:{beginAtZero:true}} }
        });

        new Chart(document.getElementById('chartNilai'), {
            type: 'radar',
            data: {
                labels: {{ Js::from(array_keys($nilaiRata)) }},
                datasets: [{ label: 'Rata-rata (1-5)', data: {{ Js::from(array_values($nilaiRata)) }},
                    backgroundColor: 'rgba(37,99,235,0.2)', borderColor: '#2563EB', pointBackgroundColor: '#2563EB' }]
            },
            options: { scales:{ r:{ suggestedMin:0, suggestedMax:5 } } }
        });
    </script>
    @endpush

</x-app-layout>