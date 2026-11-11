@props(['tool'])

<a class="tool-card" href="{{ route('tools.show', $tool->slug) }}">
    <span class="tool-card__icon" aria-hidden="true">
        <x-icon :name="$tool->icon ?? 'sparkles'" :size="21" />
    </span>
    <span class="tool-card__body">
        <span class="tool-card__title">{{ $tool->name }}</span>
        <span class="tool-card__description">{{ $tool->description }}</span>
        @if ($tool->category)
            <span class="tool-card__meta">{{ $tool->category->name }}</span>
        @endif
    </span>
    <x-icon name="arrow-up-right" :size="17" class="tool-card__arrow" />
</a>
