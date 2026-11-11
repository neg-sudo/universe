@extends('layouts.app')

@section('title', $page['title'].' — UniVerse')
@section('description', $page['description'])

@section('content')
    <section class="page-head page-head--compact">
        <div class="container container--narrow">
            <x-breadcrumbs :items="[['label' => $page['title']]]" />
            <h1>{{ $page['title'] }}</h1>
            <p class="page-head__lede">{{ $page['intro'] }}</p>
            <p class="page-meta">Last updated {{ $page['updated'] }}</p>
        </div>
    </section>

    <section class="section section--tight">
        <div class="container container--narrow">
            <div class="prose prose--page">
                @foreach ($page['sections'] as $section)
                    <h2>{{ $section['heading'] }}</h2>
                    @foreach ($section['body'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                @endforeach

                <div class="feedback-box">
                    <h2>Questions or corrections?</h2>
                    <p>UniVerse is maintained as an open project. Feedback about accuracy, accessibility and privacy is always welcome.</p>
                    <a class="text-link" href="{{ route('search') }}">Search the platform <x-icon name="arrow-right" :size="16" /></a>
                </div>
            </div>
        </div>
    </section>
@endsection
