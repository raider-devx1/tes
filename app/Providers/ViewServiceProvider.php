<?php

namespace App\Providers;

use App\View\Composers\NotifikasiComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Sediakan badge notifikasi di semua layout aplikasi
        View::composer('layouts.app', NotifikasiComposer::class);
    }
}
