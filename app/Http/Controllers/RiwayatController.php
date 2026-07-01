<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from'); // tanggal mulai (Y-m-d)
        $to   = $request->get('to');   // tanggal akhir (Y-m-d)

        $logs = ActivityLog::with('user')
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.riwayat.index', compact('logs', 'from', 'to'));
    }
}