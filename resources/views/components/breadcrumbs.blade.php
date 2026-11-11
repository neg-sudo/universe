@props(['items' => []])

<nav class="breadcrumbs" aria-label="Breadcrumb">
    <ol>
        @foreach ($items as $item)
            <li>
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    <x-icon name="chevron-right" :size="14" />
                @else
                    <span aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
