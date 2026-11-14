@props(['name' => '', 'src' => null, 'size' => 'h-10 w-10', 'text' => 'text-xs'])

@php
    $words = preg_split('/\s+/', trim((string) $name)) ?: [];
    $initials = collect($words)
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

@if ($src)
    <img src="{{ $src }}"
         alt="{{ $name }}"
         {{ $attributes->merge(['class' => $size.' shrink-0 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700']) }}>
@else
    <span {{ $attributes->merge(['class' => 'avatar '.$size.' '.$text]) }}>
        {{ $initials !== '' ? $initials : '?' }}
    </span>
@endif
