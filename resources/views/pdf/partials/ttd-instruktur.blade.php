@php
    $statusField = $statusField ?? 'status';
    $isDisetujui = ($data->{$statusField} ?? null) === 'disetujui';
    $tglValidasi = $data->validated_at ? \Carbon\Carbon::parse($data->validated_at)->translatedFormat('d F Y') : '-';
@endphp

<div style="margin-top:30px; text-align:center; width:250px;">
    <p style="margin:0;">Instruktur Industri,</p>

    @if ($isDisetujui)
        <div style="margin:8px auto; padding:8px 10px; border:1.5px solid #16a34a;
                    border-radius:8px; background:#f0fdf4; color:#166534;
                    font-size:10px; line-height:1.4; max-width:240px;">
            <strong>DISETUJUI OLEH INSTRUKTUR</strong><br>
            TERVERIFIKASI SISTEM<br>
            <span style="font-size:9px;">
                (Divalidasi oleh Guru Pembimbing pada {{ $tglValidasi }})
            </span>
        </div>
        <p style="margin:0; font-weight:bold;">
            {{ $data->catatan_instruktur ? '' : '' }} 
            ( ...................... )
        </p>
    @else
        <br><br><br>
        <p style="margin:0;">( .................................... )</p>
        <p style="margin:0; font-size:10px;">Tanda tangan &amp; nama instruktur</p>
    @endif
</div>