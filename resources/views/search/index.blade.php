@extends('layouts.app')

@section('title', $term !== '' ? 'Search: '.$term.' — UniVerse' : 'Search UniVerse')
@section('description', 'Search tools, articles and topics across UtilityWorld and KnowledgeWorld.')

@section('content')
    <section class="page-head page-head--compact">
        <div class="container">
            <x-breadcrumbs :items="[['label' => 'Search']]" />
            <p class="eyebrow">Universal search</p>
            <h1>Find a tool or an explanation.</h1>
            <p class="page-head__lede">One search covers calculators, converters, articles and topics.</p>

            <form class="hero-search hero-search--page" action="{{ route('search') }}" method="get" role="search">
                <label class="sr-only" for="search-q">Search tools, articles and topics</label>
                <x-icon name="search" :size="20" class="hero-search__icon" />
                <input id="search-q" name="q" type="search" value="{{ $term }}" placeholder="Try “percentage”, “gravity” or “loan”…" autofocus>
                <button type="submit" class="btn btn--primary">
                    Search
                    <x-icon name="arrow-right" :size="17" />
                </button>
            </form>
        </div>
    </section>

    <section class="section section--tight">
        <div class="container">
            @if ($term !== '' && mb_strlen($term) < 2)
                <div class="notice">Enter at least two characters to search.</div>
            @endif

            @if ($term !== '' && mb_strlen($term) >= 2)
                <div class="search-summary">
                    <p>
                        <strong>{{ number_format($counts['all']) }}</strong>
                        {{ \Illuminate\Support\Str::plural('result', $counts['all']) }} for
                        <strong>“{{ $term }}”</strong>
                    </p>
                    <nav class="chip-row" aria-label="Filter results by type">
                        <a class="chip {{ $type === 'all' ? 'is-active' : '' }}" href="{{ route('search', ['q' => $term, 'type' => 'all']) }}">
                            All <span class="chip__count">{{ $counts['all'] }}</span>
                        </a>
                        <a class="chip {{ $type === 'tool' ? 'is-active' : '' }}" href="{{ route('search', ['q' => $term, 'type' => 'tool']) }}">
                            Tools <span class="chip__count">{{ $counts['tool'] }}</span>
                        </a>
                        <a class="chip {{ $type === 'article' ? 'is-active' : '' }}" href="{{ route('search', ['q' => $term, 'type' => 'article']) }}">
                            Articles <span class="chip__count">{{ $counts['article'] }}</span>
                        </a>
                    </nav>
                </div>

                @if (count($results))
                    <div class="result-list">
                        @foreach ($results as $result)
                            <a class="result-item" href="{{ $result['url'] }}">
                                <span class="result-item__icon" aria-hidden="true">
                                    <x-icon :name="$result['icon'] ?? 'sparkles'" :size="21" />
                                </span>
                                <span class="result-item__body">
                                    <span class="result-item__meta">
                                        <span class="pill pill--soft">{{ $result['type_label'] }}</span>
                                        @if ($result['category'])
                                            <span>{{ $result['category'] }}</span>
                                        @endif
                                    </span>
                                    <span class="result-item__title">{{ $result['title'] }}</span>
                                    <span class="result-item__description">{{ $result['description'] }}</span>
                                </span>
                                <x-icon name="arrow-up-right" :size="17" class="result-item__arrow" />
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <x-icon name="search" :size="28" />
                        <h2>No results for “{{ $term }}”.</h2>
                        <p>Try a shorter term, a different spelling, or browse a category below.</p>
                        <div class="empty-state__actions">
                            <a class="btn btn--secondary" href="{{ route('tools.index') }}">Browse tools</a>
                            <a class="btn btn--ghost" href="{{ route('knowledge.index') }}">Browse knowledge</a>
                        </div>
                    </div>
                @endif
            @elseif ($term === '')
                <div class="search-start">
                    <div class="search-start__main">
                        <h2>Start with something practical</h2>
                        <p>These tools are a good place to begin. Search also understands topics, so “photosynthesis” finds the article and “percentage” finds the calculator.</p>
                        <div class="tool-grid">
                            @foreach ($suggestedTools as $tool)
                                <x-tool-card :tool="$tool" />
                            @endforeach
                        </div>
                    </div>

                    <aside class="search-start__aside">
                        <h2>Search tips</h2>
                        <ul class="tip-list">
                            <li><strong>Be specific.</strong> “compound interest” beats “money”.</li>
                            <li><strong>Search both worlds.</strong> Results are labelled Tool or Article.</li>
                            <li><strong>Filter after searching.</strong> Narrow results to tools or articles.</li>
                            <li><strong>No account needed.</strong> Search is open to everyone.</li>
                        </ul>
                    </aside>
                </div>
            @endif
        </div>
    </section>
@endsection
