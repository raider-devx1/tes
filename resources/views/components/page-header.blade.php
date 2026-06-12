@props(['title', 'subtitle' => null])
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">{{ $title }}</h1>
        @if($subtitle)<p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>@endif
    </div>
    @isset($action)<div class="flex shrink-0 gap-2">{{ $action }}</div>@endisset
</div>
