@extends('layouts.app')

@section('title', $category->name.' — UniVerse Knowledge')
@section('description', $category->description)

@section('content')
    <section class="page-head page-head--compact">
        <div class="container">
            <x-breadcrumbs :items="[
                ['label' => 'Knowledge', 'url' => route('knowledge.index')],
                ['label' => $category->name],
            ]" />

            <div class="collection-head">
                <div class="collection-head__icon"><x-icon :name="$category->icon ?? 'book-open'" :size="26" /></div>
                <div>
                    <h1>{{ $category->name }}</h1>
                    <p>{{ $category->description }}</p>
                    <p class="collection-head__count">{{ number_format($category->articles_count) }} articles</p>
                </div>
            </div>

            @if ($subcategories->count())
                <nav class="chip-row" aria-label="Topics in {{ $category->name }}">
                    <a class="chip {{ ! $activeSubcategory ? 'is-active' : '' }}"
                       href="{{ route('knowledge.category', $category->slug) }}">All topics</a>
                    @foreach ($subcategories as $subcategory)
                        <a class="chip {{ $activeSubcategory === $subcategory->subcategory ? 'is-active' : '' }}"
                           href="{{ route('knowledge.category', ['category' => $category->slug, 'topic' => $subcategory->subcategory]) }}">
                            {{ $subcategory->subcategory_label }}
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
    </section>

    <section class="section section--tight">
        <div class="container">
            @if ($articles->count())
                <div class="article-grid">
                    @foreach ($articles as $article)
                        <x-article-card :article="$article" />
                    @endforeach
                </div>

                {{ $articles->links('partials.pagination') }}
            @else
                <div class="empty-state">
                    <x-icon name="book-open" :size="28" />
                    <h2>No articles here yet.</h2>
                    <p>Try another topic or return to the full knowledge library.</p>
                    <a class="btn btn--secondary" href="{{ route('knowledge.index') }}">Browse knowledge</a>
                </div>
            @endif
        </div>
    </section>
@endsection
