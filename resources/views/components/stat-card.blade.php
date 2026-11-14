@props(['label', 'value', 'hint' => null, 'icon' => 'dashboard', 'tone' => 'brand'])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
        'sky' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300',
        'pink' => 'bg-pink-50 text-pink-700 dark:bg-pink-500/10 dark:text-pink-300',
        'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        'violet' => 'bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300',
    ];
@endphp

<div class="card card-pad">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="mt-2.5 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
            @endif
        </div>
        <span class="stat-icon {{ $tones[$tone] ?? $tones['brand'] }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    </div>
</div>
