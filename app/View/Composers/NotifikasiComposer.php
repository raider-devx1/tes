<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Menyediakan jumlah notifikasi belum dibaca ke layout (badge lonceng). */
class NotifikasiComposer
{
    public function compose(View $view): void
    {
        $jumlah = 0;
        if (Auth::check()) {
            $jumlah = Auth::user()->notifikasis()->belumDibaca()->count();
        }
        $view->with('jumlahNotifikasiBelumDibaca', $jumlah);
    }
}
