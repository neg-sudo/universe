@extends('layouts.app')

@section('title', 'Page not found — UniVerse')
@section('description', 'The page you were looking for does not exist.')

@section('content')
    <section class="section">
        <div class="container container--narrow">
            <div class="empty-state empty-state--large">
                <p class="eyebrow">404</p>
                <h1>That page is not here.</h1>
                <p>The link may be out of date, or the page may have moved. Try a search instead.</p>
                <form class="hero-search hero-search--page" action="{{ route('search') }}" method="get" role="search">
                    <label class="sr-only" for="notfound-q">Search UniVerse</label>
                    <x-icon name="search" :size="20" class="hero-search__icon" />
                    <input id="notfound-q" name="q" type="search" placeholder="Search tools and articles…">
                    <button type="submit" class="btn btn--primary">Search</button>
                </form>
                <div class="empty-state__actions">
                    <a class="btn btn--secondary" href="{{ route('home') }}">Go home</a>
                    <a class="btn btn--ghost" href="{{ route('tools.index') }}">Browse tools</a>
                </div>
            </div>
        </div>
    </section>
@endsection
