@props(['category', 'type' => 'tool'])

@php
    $url = $type === 'tool'
        ? route('tools.index', ['category' => $category->slug])
        : route('knowledge.category', $category->slug);
    $count = $type === 'tool' ? $category->tools_count : $category->articles_count;
    $label = $type === 'tool' ? 'tool' : 'article';
@endphp

<a class="category-card" href="{{ $url }}">
    <span class="category-card__icon" aria-hidden="true">
        <x-icon :name="$category->icon ?? 'sparkles'" :size="22" />
    </span>
    <span class="category-card__title">{{ $category->name }}</span>
    <span class="category-card__description">{{ $category->description }}</span>
    <span class="category-card__meta">{{ $count }} {{ \Illuminate\Support\Str::plural($label, $count) }}</span>
</a>
