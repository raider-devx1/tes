<x-app-layout>
    <x-page-header title="Notifikasi" />
    <div class="divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        @forelse($notifikasis as $n)
            <a href="{{ route('notifikasi.baca', $n) }}" class="flex items-start gap-3 px-5 py-4 hover:bg-slate-50 {{ $n->dibaca_pada ? '' : 'bg-indigo-50/40' }}">
                <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $n->dibaca_pada ? 'bg-slate-300' : 'bg-indigo-500' }}"></span>
                <div><p class="text-sm font-medium text-slate-800">{{ $n->judul }}</p><p class="text-sm text-slate-500">{{ $n->pesan }}</p><p class="mt-1 text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</p></div>
            </a>
        @empty
            <p class="px-5 py-10 text-center text-slate-400">Tidak ada notifikasi.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $notifikasis->links() }}</div>
</x-app-layout>
