@extends('layouts.app')

@section('title', 'UniVerse — Tools for Life. Knowledge for Everyone.')
@section('description', 'Practical tools and reliable knowledge in one place. Explore 100 calculators, converters and generators, plus a searchable library of educational articles.')

@section('content')
    <section class="hero">
        <div class="container hero__inner">
            <p class="eyebrow"><x-icon name="sparkles" :size="15" /> Tools for life. Knowledge for everyone.</p>
            <h1>Everything useful,<br><span class="hero__accent">in one place.</span></h1>
            <p class="hero__lede">
                Discover practical tools, explore reliable knowledge and learn something new every day.
                Free to use, fast on any device, and built to grow.
            </p>

            <form class="hero-search" action="{{ route('search') }}" method="get" role="search">
                <label class="sr-only" for="hero-q">Search tools, articles and topics</label>
                <x-icon name="search" :size="20" class="hero-search__icon" />
                <input id="hero-q" name="q" type="search" placeholder="Search tools, articles and topics…" autocomplete="off">
                <button type="submit" class="btn btn--primary">
                    Search
                    <x-icon name="arrow-right" :size="17" />
                </button>
            </form>

            <div class="hero__quick">
                <span>Popular:</span>
                <a href="{{ route('tools.show', 'percentage-calculator') }}">Percentage</a>
                <a href="{{ route('tools.show', 'length-converter') }}">Unit converter</a>
                <a href="{{ route('tools.show', 'bmi-calculator') }}">BMI</a>
                <a href="{{ route('tools.show', 'word-counter') }}">Word counter</a>
                <a href="{{ route('tools.show', 'loan-payment-calculator') }}">Loan payment</a>
            </div>

            <dl class="hero-stats">
                <div><dt>Tools</dt><dd>{{ number_format($stats['tools']) }}</dd></div>
                <div><dt>Articles</dt><dd>{{ number_format($stats['articles']) }}</dd></div>
                <div><dt>Tool categories</dt><dd>{{ $stats['tool_categories'] }}</dd></div>
                <div><dt>Knowledge categories</dt><dd>{{ $stats['knowledge_categories'] }}</dd></div>
            </dl>
        </div>
    </section>

    <section class="section" aria-labelledby="explore-tools">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">UtilityWorld</p>
                    <h2 id="explore-tools">Explore tools</h2>
                    <p>Calculators, converters and utilities that run instantly in your browser.</p>
                </div>
                <a class="text-link" href="{{ route('tools.index') }}">All tools <x-icon name="arrow-right" :size="16" /></a>
            </div>

            <div class="category-grid">
                @foreach ($toolCategories as $category)
                    <x-category-card :category="$category" type="tool" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section--tinted" aria-labelledby="popular-tools">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Most used</p>
                    <h2 id="popular-tools">Popular tools</h2>
                    <p>The utilities people reach for most often.</p>
                </div>
                <a class="text-link" href="{{ route('tools.index') }}">Browse all <x-icon name="arrow-right" :size="16" /></a>
            </div>

            <div class="tool-grid">
                @foreach ($popularTools as $tool)
                    <x-tool-card :tool="$tool" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="section" aria-labelledby="explore-knowledge">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">KnowledgeWorld</p>
                    <h2 id="explore-knowledge">Explore knowledge</h2>
                    <p>Clear explanations, reviewed and sourced, across ten subject areas.</p>
                </div>
                <a class="text-link" href="{{ route('knowledge.index') }}">Knowledge library <x-icon name="arrow-right" :size="16" /></a>
            </div>

            <div class="category-grid category-grid--knowledge">
                @foreach ($knowledgeCategories as $category)
                    <x-category-card :category="$category" type="article" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section--tinted" aria-labelledby="latest-articles">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Recently added</p>
                    <h2 id="latest-articles">From the knowledge library</h2>
                    <p>Start with a topic and follow the references as far as you want to go.</p>
                </div>
                <a class="text-link" href="{{ route('knowledge.index') }}">All articles <x-icon name="arrow-right" :size="16" /></a>
            </div>

            <div class="article-grid">
                @foreach ($latestArticles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="principle-band">
                <div class="principle-band__text">
                    <p class="eyebrow">Built to last</p>
                    <h2>A platform that grows without getting in your way.</h2>
                    <p>
                        Every tool is registered once and appears everywhere automatically. Every article keeps its
                        sources and review date. One search spans both worlds, so you spend less time looking and more
                        time doing.
                    </p>
                    <a class="btn btn--primary" href="{{ route('pages.about') }}">How UniVerse works <x-icon name="arrow-right" :size="16" /></a>
                </div>
                <ul class="principle-list">
                    <li><x-icon name="shield" :size="18" /> <span><strong>Private by default.</strong> Tool calculations never leave your browser.</span></li>
                    <li><x-icon name="check" :size="18" /> <span><strong>Tested calculations.</strong> Every tool ships with automated tests.</span></li>
                    <li><x-icon name="book-open" :size="18" /> <span><strong>Sourced knowledge.</strong> Articles credit their sources and licence.</span></li>
                    <li><x-icon name="sparkles" :size="18" /> <span><strong>No account needed.</strong> Read and calculate without signing up.</span></li>
                </ul>
            </div>
        </div>
    </section>
@endsection
