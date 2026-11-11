@extends('layouts.app')

@section('title', 'Knowledge library — UniVerse KnowledgeWorld')
@section('description', 'A searchable library of clear, sourced explanations across science, technology, history, geography, mathematics, economics, languages, engineering, health and life skills.')

@section('content')
    <section class="page-head">
        <div class="container">
            <x-breadcrumbs :items="[['label' => 'Knowledge']]" />
            <p class="eyebrow">KnowledgeWorld</p>
            <h1>A library built for understanding.</h1>
            <p class="page-head__lede">
                {{ number_format($total) }} articles across {{ $categories->count() }} subject areas.
                Every article shows its difficulty, reading time and source, so you know what you are reading and where it came from.
            </p>

            <form class="filter-bar" action="{{ route('search') }}" method="get" role="search">
                <div class="filter-bar__search">
                    <x-icon name="search" :size="17" />
                    <label class="sr-only" for="knowledge-q">Search the knowledge library</label>
                    <input id="knowledge-q" name="q" type="search" placeholder="Search articles and topics…">
                </div>
                <input type="hidden" name="type" value="article">
                <button class="btn btn--secondary btn--sm" type="submit">Search articles</button>
            </form>
        </div>
    </section>

    <section class="section section--tight">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Browse by subject</p>
                    <h2>Knowledge categories</h2>
                </div>
            </div>

            <div class="category-grid category-grid--knowledge">
                @foreach ($categories as $category)
                    <x-category-card :category="$category" type="article" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section--tinted">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Recently added</p>
                    <h2>Latest articles</h2>
                </div>
            </div>

            <div class="article-grid">
                @foreach ($latest as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="sourcing-note">
                <span class="sourcing-note__icon"><x-icon name="book-open" :size="22" /></span>
                <div>
                    <h2>Where this knowledge comes from</h2>
                    <p>
                        The initial library is adapted from Wikipedia under the Creative Commons Attribution-ShareAlike
                        4.0 licence. Each article displays its source link, licence and retrieval date. Articles are
                        reviewed for structure and clarity before publication, and corrections are welcome.
                    </p>
                    <a class="text-link" href="{{ route('pages.about') }}">Read about our editorial approach <x-icon name="arrow-right" :size="16" /></a>
                </div>
            </div>
        </div>
    </section>
@endsection
