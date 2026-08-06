@props([
    'name'   => 'ttd_instruktur',
    'label'  => 'Tanda Tangan / Paraf Instruktur',
    'tinggi' => 190,
    'wajib'  => true,
])

{{--
    Kanvas tanda tangan digital (tanpa library eksternal / tanpa npm build).
    - Mendukung sentuhan HP (pointer + touch) dan mouse di laptop.
    - Lebar mengikuti wadahnya (responsif) dan otomatis diukur ulang saat modal dibuka.
    - Hasil goresan dipangkas rapi lalu disimpan ke input hidden sebagai PNG base64.
--}}

<div class="ttd-pad" data-ttd data-ttd-wajib="{{ $wajib ? '1' : '0' }}">
    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">
        {{ $label }}@if($wajib)<span class="text-[#cf202f]">*</span>@endif
    </label>

    <div class="ttd-pad__box" data-ttd-box style="height: {{ (int) $tinggi }}px">
        <canvas class="ttd-pad__canvas" data-ttd-canvas></canvas>
        <span class="ttd-pad__hint" data-ttd-hint>Tanda tangan di area ini</span>
    </div>

    <input type="hidden" name="{{ $name }}" data-ttd-input value="">

    <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
        <p class="text-[11px] font-medium text-[#5b616e]" data-ttd-status>Belum ada tanda tangan.</p>
        <button type="button" data-ttd-clear
                class="inline-flex items-center gap-1 rounded-xl border-2 border-[#cf202f]/30 bg-white px-3 py-1.5 text-[11px] font-bold text-[#cf202f] transition hover:bg-[#cf202f]/5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M20 9A8 8 0 0 0 5.6 6.6M4 15a8 8 0 0 0 14.4 2.4"/>
            </svg>
            Ulangi
        </button>
    </div>
</div>

@once
@push('scripts')
<style>
    .ttd-pad__box{
        position:relative; width:100%;
        border:2px dashed rgba(0,71,214,.35); border-radius:14px;
        background:#f8fafc; overflow:hidden;
    }
    .ttd-pad--terisi .ttd-pad__box{ border-style:solid; border-color:#05b169; background:#fff; }
    .ttd-pad__canvas{
        display:block; width:100%; height:100%;
        touch-action:none;              /* penting: layar HP tidak ikut ter-scroll saat menandatangani */
        -ms-touch-action:none;
        cursor:crosshair;
    }
    .ttd-pad__hint{
        position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
        pointer-events:none; font-size:12px; font-weight:600; color:#94a3b8;
    }
</style>
<script>
(function () {
    const dpr = () => Math.max(1, Math.min(3, window.devicePixelRatio || 1));

    function siapkan(wrap) {
        if (wrap.__ttdSiap) return;

        const box    = wrap.querySelector('[data-ttd-box]');
        const canvas = wrap.querySelector('[data-ttd-canvas]');
        const input  = wrap.querySelector('[data-ttd-input]');
        const hint   = wrap.querySelector('[data-ttd-hint]');
        const status = wrap.querySelector('[data-ttd-status]');
        const tombol = wrap.querySelector('[data-ttd-clear]');
        if (!box || !canvas || !input) return;

        wrap.__ttdSiap = true;
        const ctx = canvas.getContext('2d');
        let menggambar = false, adaGoresan = false, xAkhir = 0, yAkhir = 0, wCss = 0, hCss = 0;

        function gayaGaris() {
            ctx.lineWidth = 2.2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#111827';
        }

        function ukurUlang() {
            const w = Math.round(box.clientWidth), h = Math.round(box.clientHeight);
            if (w < 1 || h < 1 || (w === wCss && h === hCss)) return;

            const cadangan = adaGoresan ? canvas.toDataURL('image/png') : null;
            wCss = w; hCss = h;
            const r = dpr();
            canvas.width  = Math.round(w * r);
            canvas.height = Math.round(h * r);
            canvas.style.width  = w + 'px';
            canvas.style.height = h + 'px';
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(r, r);
            gayaGaris();

            if (cadangan) {
                const img = new Image();
                img.onload = function () { ctx.drawImage(img, 0, 0, w, h); };
                img.src = cadangan;
            }
        }

        function titik(e) {
            const kotak = canvas.getBoundingClientRect();
            const p = (e.touches && e.touches[0]) ? e.touches[0] : e;
            return { x: p.clientX - kotak.left, y: p.clientY - kotak.top };
        }

        function mulai(e) {
            if (e.button !== undefined && e.button !== 0) return;
            e.preventDefault();
            menggambar = true;
            const p = titik(e);
            xAkhir = p.x; yAkhir = p.y;
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            ctx.lineTo(p.x + 0.1, p.y + 0.1);
            ctx.stroke();
            tandai();
        }

        function gerak(e) {
            if (!menggambar) return;
            e.preventDefault();
            const p = titik(e);
            ctx.beginPath();
            ctx.moveTo(xAkhir, yAkhir);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            xAkhir = p.x; yAkhir = p.y;
        }

        function selesai() {
            if (!menggambar) return;
            menggambar = false;
            simpan();
        }

        function tandai() {
            adaGoresan = true;
            if (hint) hint.style.display = 'none';
            wrap.classList.add('ttd-pad--terisi');
        }

        function simpan() {
            input.value = adaGoresan ? pangkas() : '';
            if (status) status.textContent = adaGoresan
                ? 'Tanda tangan siap dikirim.'
                : 'Belum ada tanda tangan.';
        }

        function bersihkan() {
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.scale(dpr(), dpr());
            gayaGaris();
            adaGoresan = false;
            input.value = '';
            if (hint) hint.style.display = '';
            if (status) status.textContent = 'Belum ada tanda tangan.';
            wrap.classList.remove('ttd-pad--terisi');
        }

        /* Buang area kosong di sekeliling goresan supaya paraf terlihat besar & rapi di PDF. */
        function pangkas() {
            try {
                const w = canvas.width, h = canvas.height;
                const data = ctx.getImageData(0, 0, w, h).data;
                let xMin = w, yMin = h, xMax = -1, yMax = -1;

                for (let y = 0; y < h; y++) {
                    for (let x = 0; x < w; x++) {
                        if (data[(y * w + x) * 4 + 3] > 8) {
                            if (x < xMin) xMin = x;
                            if (x > xMax) xMax = x;
                            if (y < yMin) yMin = y;
                            if (y > yMax) yMax = y;
                        }
                    }
                }
                if (xMax < 0) return canvas.toDataURL('image/png');

                const sisa = Math.round(10 * dpr());
                xMin = Math.max(0, xMin - sisa); yMin = Math.max(0, yMin - sisa);
                xMax = Math.min(w - 1, xMax + sisa); yMax = Math.min(h - 1, yMax + sisa);

                const lebar = xMax - xMin + 1, tinggi = yMax - yMin + 1;
                const tmp = document.createElement('canvas');
                tmp.width = lebar; tmp.height = tinggi;
                tmp.getContext('2d').drawImage(canvas, xMin, yMin, lebar, tinggi, 0, 0, lebar, tinggi);
                return tmp.toDataURL('image/png');
            } catch (err) {
                return canvas.toDataURL('image/png');
            }
        }

        if (window.PointerEvent) {
            canvas.addEventListener('pointerdown', mulai);
            canvas.addEventListener('pointermove', gerak);
            canvas.addEventListener('pointerup', selesai);
            canvas.addEventListener('pointercancel', selesai);
            window.addEventListener('pointerup', selesai);
        } else {
            canvas.addEventListener('touchstart', mulai, { passive: false });
            canvas.addEventListener('touchmove', gerak, { passive: false });
            canvas.addEventListener('touchend', selesai);
            canvas.addEventListener('mousedown', mulai);
            canvas.addEventListener('mousemove', gerak);
            window.addEventListener('mouseup', selesai);
        }

        if (tombol) tombol.addEventListener('click', bersihkan);

        /* Modal awalnya display:none -> ukuran 0. ResizeObserver menangkap saat modal dibuka. */
        if (window.ResizeObserver) {
            new ResizeObserver(ukurUlang).observe(box);
        } else {
            document.addEventListener('click', function () { setTimeout(ukurUlang, 350); }, true);
            window.addEventListener('orientationchange', function () { setTimeout(ukurUlang, 350); });
        }
        window.addEventListener('resize', ukurUlang);

        wrap.__ttdUkurUlang = ukurUlang;
        ukurUlang();
    }

    function siapkanSemua() {
        document.querySelectorAll('[data-ttd]').forEach(siapkan);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', siapkanSemua);
    } else {
        siapkanSemua();
    }

    /* Kanvas yang berada di dalam <template x-teleport> (mis. modal Validasi pada
       halaman absensi guru) baru muncul di DOM setelah Alpine selesai memasang
       komponen. Pindaian ulang di bawah ini membuat kanvas seperti itu tetap aktif.
       Fungsi siapkan() punya penjaga __ttdSiap sehingga aman dipanggil berkali-kali. */
    document.addEventListener('alpine:initialized', siapkanSemua);
    window.ttdPadSiapkan = siapkanSemua;

    /* Tanda tangan wajib: cegah kirim bila kanvas masih kosong. */
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;

        /* Satu form bisa memuat lebih dari satu kanvas
           (mis. paraf guru pembimbing + paraf instruktur pada lembar observasi). */
        const pads = form.querySelectorAll('[data-ttd][data-ttd-wajib="1"]');
        if (!pads.length) return;

        let wrap = null;
        for (let i = 0; i < pads.length; i++) {
            const isi = pads[i].querySelector('[data-ttd-input]');
            if (!isi || !isi.value) { wrap = pads[i]; break; }
        }
        if (!wrap) return;   // semua kanvas wajib sudah terisi

        e.preventDefault();
        e.stopPropagation();

        const labelEl = wrap.querySelector('label');
        const label = ((labelEl ? labelEl.textContent : '') || '').replace(/\*/g, '').trim() || 'Tanda tangan';

        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Tanda tangan belum ada',
                text: label + ' masih kosong. Mohon tanda tangani pada kotak yang tersedia sebelum pengajuan dikirim.',
                confirmButtonColor: '#0047d6',
            });
        } else {
            alert(label + ' masih kosong.');
        }

        try { wrap.scrollIntoView({ block: 'center', behavior: 'smooth' }); } catch (err) {}

        if (wrap.__ttdUkurUlang) wrap.__ttdUkurUlang();
    }, true);
})();
</script>
@endpush
@endonce
