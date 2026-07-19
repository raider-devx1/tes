

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// ===== Kompresi gambar di sisi client (Canvas API, tanpa library) =====
// Menyusutkan foto mentah HP (3-7 MB) menjadi < ~1 MB sebelum dikirim ke server.
window.kompresGambar = async function (file, opsi = {}) {
    const {
        maxUkuran   = 1600,          // sisi terpanjang maksimum (px)
        kualitas    = 0.7,           // kualitas awal JPEG (0-1)
        targetBytes = 1024 * 1024,   // target akhir < 1 MB
    } = opsi;

    // Lewati kalau bukan gambar atau memang sudah kecil
    if (!file || !file.type.startsWith('image/') || file.size <= targetBytes) {
        return file;
    }

    // Muat file jadi <img>
    const dataUrl = await new Promise((res, rej) => {
        const fr = new FileReader();
        fr.onload = () => res(fr.result);
        fr.onerror = rej;
        fr.readAsDataURL(file);
    });
    const img = await new Promise((res, rej) => {
        const im = new Image();
        im.onload = () => res(im);
        im.onerror = rej;
        im.src = dataUrl;
    });

    // Hitung dimensi baru sambil menjaga rasio
    let { width, height } = img;
    if (width > height && width > maxUkuran) {
        height = Math.round((height * maxUkuran) / width);
        width  = maxUkuran;
    } else if (height >= width && height > maxUkuran) {
        width  = Math.round((width * maxUkuran) / height);
        height = maxUkuran;
    }

    // Gambar ulang ke canvas
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(img, 0, 0, width, height);

    // Turunkan kualitas bertahap sampai di bawah target
    let q = kualitas;
    let blob = await new Promise((r) => canvas.toBlob(r, 'image/jpeg', q));
    while (blob && blob.size > targetBytes && q > 0.4) {
        q -= 0.1;
        blob = await new Promise((r) => canvas.toBlob(r, 'image/jpeg', q));
    }
    if (!blob) return file; // kalau gagal, pakai file asli

    const namaBaru = file.name.replace(/\.[^.]+$/, '') + '.jpg';
    return new File([blob], namaBaru, { type: 'image/jpeg', lastModified: Date.now() });
};

// ===== Auto-kompres untuk <input type="file"> gambar yang dikirim langsung =====
// Berlaku otomatis untuk Jurnal (Foto Dokumentasi), Profil, dsb.
// Beri atribut  data-no-compress  pada input yang TIDAK ingin diproses di sini.
document.addEventListener('change', async function (e) {
    const input = e.target;
    if (!(input instanceof HTMLInputElement)) return;
    if (input.type !== 'file' || input.hasAttribute('data-no-compress')) return;
    if (!input.files || !input.files.length) return;

    const files = Array.from(input.files);
    if (!files.some((f) => f.type.startsWith('image/'))) return;

    // Nonaktifkan tombol submit selama proses supaya tidak terkirim mentah
    const tombol = input.form ? input.form.querySelector('[type="submit"]') : null;
    if (tombol) tombol.disabled = true;

    try {
        const dt = new DataTransfer();
        for (const f of files) {
            dt.items.add(f.type.startsWith('image/') ? await window.kompresGambar(f) : f);
        }
        input.files = dt.files; // ganti file asli dengan versi terkompres
    } catch (err) {
        console.error('Gagal kompres gambar:', err);
    } finally {
        if (tombol) tombol.disabled = false;
    }
});

