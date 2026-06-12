<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifikasis = Auth::user()->notifikasis()->paginate(20);
        // tandai semua sudah dibaca saat halaman dibuka
        Auth::user()->notifikasis()->belumDibaca()->update(['dibaca_pada' => now()]);
        return view('notifikasi.index', compact('notifikasis'));
    }

    public function baca(Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === Auth::id(), 403);
        $notifikasi->update(['dibaca_pada' => now()]);
        return $notifikasi->link ? redirect($notifikasi->link) : back();
    }
}
