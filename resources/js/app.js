

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// ===== Batas ukuran file foto (dipakai di seluruh aplikasi) =====
// Maksimal 3 MB. Meskipun sudah dikompres, kalau tetap > 3 MB file DITOLAK.
window.MAKS_UKURAN_FOTO = 3 * 1024 * 1024; // 3 MB dalam byte

// Format ukuran byte -> teks yang enak dibaca (mis. "3.4 MB")
window.formatUkuran = function (bytes) {
    if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    if (bytes >= 1024) return Math.round(bytes / 1024) + ' KB';
    return bytes + ' B';
};

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

// ===== Validasi batas 3 MB + kompres =====
// Mengembalikan { ok, file, pesan }.
// - Kalau bukan gambar: diteruskan apa adanya (ok = true).
// - Kalau gambar & file ASLI > 3 MB: langsung ditolak (ok = false + pesan).
// - Kalau gambar & file ASLI <= 3 MB: tetap dikompres, lalu ok = true.
window.prosesFotoMaks3MB = async function (file) {
    if (!file) return { ok: false, file: null, pesan: 'Tidak ada file yang dipilih.' };

    // File non-gambar tidak diproses di sini
    if (!file.type.startsWith('image/')) {
        return { ok: true, file };
    }

    // Tolak langsung kalau file ASLI sudah melebihi 3 MB (sebelum dikompres)
    if (file.size > window.MAKS_UKURAN_FOTO) {
        return {
            ok: false,
            file: null,
            pesan: 'Ukuran foto ' + window.formatUkuran(file.size)
                + ' melebihi batas maksimal 3 MB. '
                + 'Silakan pilih foto lain yang ukurannya di bawah 3 MB.',
        };
    }

    // File ≤ 3 MB -> tetap dikompres untuk menghemat ukuran
    let hasil = file;
    try {
        hasil = await window.kompresGambar(file);
    } catch (err) {
        console.error('Gagal kompres gambar:', err);
        hasil = file; // fallback ke file asli (tetap ≤ 3 MB)
    }

    return { ok: true, file: hasil };
};

// ===== Auto-kompres + validasi untuk <input type="file"> gambar biasa =====
// Berlaku otomatis untuk semua input file gambar yang dikirim langsung
// (Jurnal, Profil, Absensi, Catatan, Observasi, Penilaian, dsb).
// Beri atribut  data-no-compress  pada input yang ditangani manual (mis. picker khusus).
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
            if (f.type.startsWith('image/')) {
                const hasil = await window.prosesFotoMaks3MB(f);
                if (!hasil.ok) {
                    // Foto terlalu besar -> tolak, kosongkan input, beri peringatan.
                    alert(hasil.pesan);
                    input.value = '';
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    return;
                }
                dt.items.add(hasil.file);
            } else {
                dt.items.add(f);
            }
        }
        input.files = dt.files; // ganti file asli dengan versi terkompres
    } catch (err) {
        console.error('Gagal memproses gambar:', err);
    } finally {
        if (tombol) tombol.disabled = false;
    }
});
