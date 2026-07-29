<?php

namespace App\Support;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Pembersih HTML untuk konten yang ditulis lewat editor (Informasi & Panduan PKL).
 *
 * Konten Informasi sebelumnya dirender mentah dengan {!! !!}. Karena akun admin
 * bisa diberikan ke banyak guru (fitur "jadikan admin"), satu akun yang disalahgunakan
 * bisa menyisipkan <script> yang berjalan di browser SELURUH siswa (pencurian session).
 *
 * Kelas ini memakai DOMDocument (ekstensi dom, bawaan PHP) sehingga tidak butuh
 * paket Composer tambahan dan tetap aman dipakai di shared hosting.
 *
 * Strategi: allowlist (hanya yang terdaftar yang boleh lolos), bukan blocklist.
 */
class HtmlSanitizer
{
    /** Tag yang diizinkan. Sengaja dibatasi ke kebutuhan formatting artikel. */
    private const TAG_AMAN = [
        'p', 'br', 'hr', 'div', 'span',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup', 'small',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
        'a', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
    ];

    /** Atribut yang diizinkan per tag. Atribut on* (onclick, onerror, ...) selalu dibuang. */
    private const ATRIBUT_AMAN = [
        'a'  => ['href', 'title', 'target', 'rel'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    /** Skema URL yang boleh dipakai pada href. javascript: & data: diblokir. */
    private const SKEMA_AMAN = ['http', 'https', 'mailto', 'tel'];

    /**
     * Bersihkan HTML dan kembalikan versi yang aman dirender dengan {!! !!}.
     */
    public static function bersihkan(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument('1.0', 'UTF-8');

        // Matikan warning HTML tidak valid; konten editor sering tidak rapi.
        $sebelumnya = libxml_use_internal_errors(true);

        // Bungkus supaya DOMDocument tidak menambah <html><body> ke output,
        // dan paksa interpretasi UTF-8 agar huruf beraksen tidak rusak.
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><div id="__akar__">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );

        libxml_clear_errors();
        libxml_use_internal_errors($sebelumnya);

        $akar = $doc->getElementById('__akar__');

        if (! $akar) {
            return e($html); // Gagal parse -> escape total, tetap aman.
        }

        self::hapusTagBerbahaya($doc);
        self::bersihkanElemen($akar);

        $keluaran = '';
        foreach (iterator_to_array($akar->childNodes) as $anak) {
            $keluaran .= $doc->saveHTML($anak);
        }

        return $keluaran;
    }

    /** Buang total elemen eksekutif beserta isinya (script, iframe, dsb). */
    private static function hapusTagBerbahaya(DOMDocument $doc): void
    {
        $xpath = new DOMXPath($doc);
        $query = '//script|//style|//iframe|//object|//embed|//form|//input|//button|//link|//meta|//base|//svg';

        foreach (iterator_to_array($xpath->query($query) ?: []) as $node) {
            $node->parentNode?->removeChild($node);
        }
    }

    /** Telusuri pohon DOM: buang tag tak dikenal, sisakan teksnya; saring atribut. */
    private static function bersihkanElemen(DOMElement $induk): void
    {
        foreach (iterator_to_array($induk->childNodes) as $node) {
            if (! $node instanceof DOMElement) {
                continue; // Node teks & komentar aman (komentar tidak dieksekusi).
            }

            $tag = strtolower($node->nodeName);

            if (! \in_array($tag, self::TAG_AMAN, true)) {
                // Tag tidak diizinkan: angkat anak-anaknya ke induk, lalu buang tagnya.
                self::bersihkanElemen($node);

                while ($node->firstChild) {
                    $induk->insertBefore($node->firstChild, $node);
                }

                $induk->removeChild($node);

                continue;
            }

            self::saringAtribut($node, $tag);
            self::bersihkanElemen($node);
        }
    }

    /** Hapus semua atribut kecuali yang di-allowlist untuk tag tersebut. */
    private static function saringAtribut(DOMElement $el, string $tag): void
    {
        $diizinkan = self::ATRIBUT_AMAN[$tag] ?? [];

        foreach (iterator_to_array($el->attributes ?? []) as $atribut) {
            /** @var DOMAttr $atribut */
            $nama = strtolower($atribut->nodeName);

            if (! \in_array($nama, $diizinkan, true)) {
                $el->removeAttribute($atribut->nodeName);

                continue;
            }

            if ($nama === 'href' && ! self::urlAman($atribut->nodeValue)) {
                $el->removeAttribute('href');
            }
        }

        // Link keluar dibuka aman (cegah tabnabbing).
        if ($tag === 'a' && $el->getAttribute('target') === '_blank') {
            $el->setAttribute('rel', 'noopener noreferrer');
        }
    }

    /** Tolak javascript:, data:, vbscript: dan skema aneh lainnya. */
    private static function urlAman(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '') {
            return false;
        }

        // Relatif atau anchor -> aman.
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        $skema = parse_url($url, PHP_URL_SCHEME);

        if ($skema === null) {
            return true; // Contoh: "contoh.com/halaman"
        }

        return \in_array(strtolower($skema), self::SKEMA_AMAN, true);
    }
}
