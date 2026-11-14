@props(['paginator'])

@if ($paginator->hasPages())
    @php
        $start = max(1, $paginator->currentPage() - 1);
        $end = min($paginator->lastPage(), $paginator->currentPage() + 1);
    @endphp

    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col gap-3 border-t border-slate-100 px-4 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Menampilkan
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-slate-700 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $paginator->total() }}</span>
            data
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-300 dark:text-slate-600" aria-disabled="true">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white" rel="prev" aria-label="Halaman sebelumnya">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                </a>
            @endif

            @if ($start > 1)
                <a href="{{ $paginator->url(1) }}" class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white">1</a>
                @if ($start > 2)
                    <span class="px-1 text-xs text-slate-400">…</span>
                @endif
            @endif

            @foreach ($paginator->getUrlRange($start, $end) as $page => $url)
                @if ($page === $paginator->currentPage())
                    <span aria-current="page" class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-brand-700 px-2 text-xs font-bold text-white">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white">{{ $page }}</a>
                @endif
            @endforeach

            @if ($end < $paginator->lastPage())
                @if ($end < $paginator->lastPage() - 1)
                    <span class="px-1 text-xs text-slate-400">…</span>
                @endif
                <a href="{{ $paginator->url($paginator->lastPage()) }}" class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white">{{ $paginator->lastPage() }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white" rel="next" aria-label="Halaman berikutnya">
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </a>
            @else
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-300 dark:text-slate-600" aria-disabled="true">
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
