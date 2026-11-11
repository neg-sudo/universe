@extends('layouts.app')

@section('title', 'All tools — UniVerse UtilityWorld')
@section('description', 'Browse 100 practical calculators, converters, generators and developer utilities. Free, fast and private.')

@section('content')
    <section class="page-head">
        <div class="container">
            <x-breadcrumbs :items="[['label' => 'Tools']]" />
            <p class="eyebrow">UtilityWorld</p>
            <h1>Practical tools, ready when you need them.</h1>
            <p class="page-head__lede">
                {{ number_format($tools->total()) }} tools across {{ $categories->where('tools_count', '>', 0)->count() }} categories.
                Everything runs in your browser — no account, no waiting.
            </p>

            <form class="filter-bar" action="{{ route('tools.index') }}" method="get" role="search">
                @if ($activeCategory)
                    <input type="hidden" name="category" value="{{ $activeCategory->slug }}">
                @endif
                <div class="filter-bar__search">
                    <x-icon name="search" :size="17" />
                    <label class="sr-only" for="tool-q">Filter tools</label>
                    <input id="tool-q" name="q" type="search" value="{{ $search }}" placeholder="Filter tools by name or description…">
                </div>
                <button class="btn btn--secondary btn--sm" type="submit">Filter</button>
            </form>

            <nav class="chip-row" aria-label="Tool categories">
                <a class="chip {{ ! $activeCategory ? 'is-active' : '' }}" href="{{ route('tools.index') }}">All</a>
                @foreach ($categories as $category)
                    @if ($category->tools_count > 0)
                        <a class="chip {{ $activeCategory?->id === $category->id ? 'is-active' : '' }}"
                           href="{{ route('tools.index', ['category' => $category->slug]) }}">
                            {{ $category->name }}
                            <span class="chip__count">{{ $category->tools_count }}</span>
                        </a>
                    @endif
                @endforeach
                @foreach ($categories as $category)
                    @if ($category->tools_count === 0)
                        <span class="chip chip--soon" title="Planned for a future release">
                            {{ $category->name }} <span class="chip__count">Soon</span>
                        </span>
                    @endif
                @endforeach
            </nav>
        </div>
    </section>

    <section class="section section--tight">
        <div class="container">
            @if ($activeCategory)
                <div class="collection-head">
                    <div class="collection-head__icon"><x-icon :name="$activeCategory->icon ?? 'sparkles'" :size="22" /></div>
                    <div>
                        <h2>{{ $activeCategory->name }}</h2>
                        <p>{{ $activeCategory->description }}</p>
                    </div>
                </div>
            @endif

            @if ($tools->count())
                <div class="tool-grid">
                    @foreach ($tools as $tool)
                        <x-tool-card :tool="$tool" />
                    @endforeach
                </div>

                {{ $tools->links('partials.pagination') }}
            @else
                <div class="empty-state">
                    <x-icon name="search" :size="28" />
                    <h2>No tools matched that filter.</h2>
                    <p>Try a shorter search term or browse another category.</p>
                    <a class="btn btn--secondary" href="{{ route('tools.index') }}">Clear filters</a>
                </div>
            @endif
        </div>
    </section>
@endsection
