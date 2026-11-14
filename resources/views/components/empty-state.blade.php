@props(['title', 'description' => null, 'icon' => 'search', 'action' => null])

<div class="empty">
    <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>
    <h3 class="mt-4 text-sm font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-sm text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endif
    @if ($action)
        <div class="mt-5">{{ $action }}</div>
    @endif
</div>
