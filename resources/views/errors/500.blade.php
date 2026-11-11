@extends('layouts.app')

@section('title', 'Something went wrong — UniVerse')
@section('description', 'An unexpected error occurred.')

@section('content')
    <section class="section">
        <div class="container container--narrow">
            <div class="empty-state empty-state--large">
                <p class="eyebrow">500</p>
                <h1>Something went wrong on our side.</h1>
                <p>The problem has been logged. Please try again, or use search to find what you need.</p>
                <div class="empty-state__actions">
                    <a class="btn btn--secondary" href="{{ route('home') }}">Go home</a>
                    <a class="btn btn--ghost" href="{{ route('search') }}">Search</a>
                </div>
            </div>
        </div>
    </section>
@endsection
