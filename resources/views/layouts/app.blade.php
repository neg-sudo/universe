<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'UniVerse — Tools for Life. Knowledge for Everyone.')</title>
    <meta name="description" content="@yield('description', 'Practical tools and reliable knowledge in one place. Calculators, converters, generators and a searchable educational library.')">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="UniVerse">
    <meta property="og:title" content="@yield('title', 'UniVerse')">
    <meta property="og:description" content="@yield('description', 'Practical tools and reliable knowledge in one place.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="theme-color" content="#ffffff">

    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%232563eb'/%3E%3Ctext x='32' y='45' font-family='system-ui,sans-serif' font-size='36' font-weight='700' fill='white' text-anchor='middle'%3EU%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @stack('head')
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<div class="reading-progress" data-reading-progress hidden></div>

<header class="site-header" data-site-header>
    <div class="container header-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="UniVerse home">
            <span class="brand-mark" aria-hidden="true">U</span>
            <span class="brand-name">UniVerse</span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" data-nav-toggle>
            <x-icon name="menu" :size="22" />
            <span class="sr-only">Toggle navigation</span>
        </button>

        <nav id="primary-nav" class="site-nav" aria-label="Primary" data-nav>
            <a href="{{ route('home') }}" @class(['is-active' => request()->routeIs('home')])>Home</a>
            <a href="{{ route('tools.index') }}" @class(['is-active' => request()->routeIs('tools.*')])>Tools</a>
            <a href="{{ route('knowledge.index') }}" @class(['is-active' => request()->routeIs('knowledge.*')])>Knowledge</a>
            <a href="{{ route('search') }}" @class(['is-active' => request()->routeIs('search')])>Search</a>
            <a href="{{ route('pages.about') }}" @class(['is-active' => request()->routeIs('pages.about')])>About</a>
        </nav>

        <form class="header-search" action="{{ route('search') }}" method="get" role="search">
            <label class="sr-only" for="header-q">Search UniVerse</label>
            <x-icon name="search" :size="17" class="header-search__icon" />
            <input id="header-q" name="q" type="search" placeholder="Search" autocomplete="off" value="{{ request()->routeIs('search') ? request('q') : '' }}">
            <button type="submit" class="header-search__submit">
                <span class="sr-only">Search</span>
                <x-icon name="arrow-right" :size="16" />
            </button>
        </form>
    </div>
</header>

<main id="main">
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-mark" aria-hidden="true">U</span>
                <span class="brand-name">UniVerse</span>
            </a>
            <p>Tools for life. Knowledge for everyone. Free, fast and built to stay useful as it grows.</p>
            <p class="footer-note">
                <x-icon name="shield" :size="15" />
                Calculations run in your browser. We collect as little as possible.
            </p>
        </div>

        <div class="footer-column">
            <h2>Explore</h2>
            <a href="{{ route('tools.index') }}">All tools</a>
            <a href="{{ route('knowledge.index') }}">Knowledge library</a>
            <a href="{{ route('search') }}">Universal search</a>
            <a href="{{ route('tools.index', ['category' => 'mathematics']) }}">Mathematics</a>
            <a href="{{ route('tools.index', ['category' => 'conversion']) }}">Unit conversion</a>
        </div>

        <div class="footer-column">
            <h2>Knowledge</h2>
            <a href="{{ route('knowledge.category', 'science') }}">Science</a>
            <a href="{{ route('knowledge.category', 'technology') }}">Technology</a>
            <a href="{{ route('knowledge.category', 'history') }}">History</a>
            <a href="{{ route('knowledge.category', 'mathematics') }}">Mathematics</a>
            <a href="{{ route('knowledge.category', 'life-skills') }}">Life skills</a>
        </div>

        <div class="footer-column">
            <h2>Platform</h2>
            <a href="{{ route('pages.about') }}">About</a>
            <a href="{{ route('pages.privacy') }}">Privacy</a>
            <a href="{{ route('pages.terms') }}">Terms</a>
            <a href="{{ route('pages.accessibility') }}">Accessibility</a>
            <a href="{{ route('search') }}">Contact &amp; feedback</a>
        </div>
    </div>

    <div class="container footer-bottom">
        <p>&copy; {{ date('Y') }} UniVerse. Built as an open educational platform.</p>
        <p class="footer-license">
            Knowledge articles adapted from
            <a href="https://www.wikipedia.org" rel="noopener">Wikipedia</a>
            under <a href="https://creativecommons.org/licenses/by-sa/4.0/" rel="noopener">CC BY-SA 4.0</a>.
        </p>
    </div>
</footer>

<script src="{{ asset('assets/js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
