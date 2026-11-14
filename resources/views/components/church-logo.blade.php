@props([
    'class' => 'h-11 w-11',
    'iconClass' => 'h-6 w-6',
    'rounded' => 'rounded-2xl',
    'padding' => 'p-1',
    'src' => null,
])

@php
    $logoSrc = $src ?? config('church.logo_url');
@endphp

@if ($logoSrc)
    <img src="{{ $logoSrc }}"
         alt="Logo {{ config('church.name') }}"
         class="{{ $class }} {{ $rounded }} {{ $padding }} bg-white object-contain ring-1 ring-slate-200/80 dark:ring-slate-700">
@else
    <span class="inline-flex {{ $class }} items-center justify-center {{ $rounded }} bg-brand-700 text-white shadow-lg shadow-brand-900/20">
        <x-icon name="cross" class="{{ $iconClass }}" />
    </span>
@endif
