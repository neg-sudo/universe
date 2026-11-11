@props(['name' => 'sparkles', 'size' => 20, 'stroke' => 1.8])

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="{{ $stroke }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
    {{ $attributes->merge(['class' => 'icon']) }}
>{!! \App\Support\Icons::path($name) !!}</svg>
