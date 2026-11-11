@extends('layouts.app')

@section('title', $article->title.' — UniVerse Knowledge')
@section('description', $article->summary)

@section('content')
    <article data-article>
        <section class="page-head page-head--compact">
            <div class="container">
                <x-breadcrumbs :items="[
                    ['label' => 'Knowledge', 'url' => route('knowledge.index')],
                    ['label' => $article->category->name, 'url' => route('knowledge.category', $article->category->slug)],
                    ['label' => $article->title],
                ]" />

                <div class="article-heading">
                    <div class="meta-row">
                        <a class="pill" href="{{ route('knowledge.category', $article->category->slug) }}">{{ $article->category->name }}</a>
                        @if ($article->subcategory_label)
                            <span class="meta-item">{{ $article->subcategory_label }}</span>
                        @endif
                        <span class="difficulty difficulty--{{ $article->difficulty }}">{{ ucfirst($article->difficulty) }}</span>
                        <span class="meta-item"><x-icon name="clock" :size="14" /> {{ $article->reading_time }} min read</span>
                    </div>

                    <h1>{{ $article->title }}</h1>
                    <p class="article-heading__summary">{{ $article->summary }}</p>

                    <div class="article-heading__actions">
                        <button class="btn btn--ghost btn--sm" type="button" data-copy-link>
                            <x-icon name="copy" :size="15" /> Copy link
                        </button>
                        <span class="article-heading__updated">
                            <x-icon name="file-check" :size="15" />
                            Reviewed {{ $article->reviewed_at?->format('j F Y') ?? $article->retrieved_at?->format('j F Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="section section--tight">
            <div class="container article-layout">
                <div class="article-body prose">
                    @foreach ($article->paragraphs() as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach

                    @if (! empty($article->tags))
                        <div class="tag-row">
                            @foreach ($article->tags as $tag)
                                <span class="tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="source-box">
                        <div class="source-box__head">
                            <x-icon name="book-open" :size="19" />
                            <h2>Source and licence</h2>
                        </div>
                        <p>
                            This article is adapted from
                            <a href="{{ $article->source_url }}" rel="noopener noreferrer" target="_blank">{{ $article->source_name }}: {{ $article->title }}</a>,
                            retrieved on {{ $article->retrieved_at?->format('j F Y') }}.
                            It is available under the
                            <a href="https://creativecommons.org/licenses/by-sa/4.0/" rel="noopener noreferrer" target="_blank">{{ $article->license }}</a>
                            licence. Changes may have been made for length, structure and clarity.
                        </p>
                        <a class="btn btn--secondary btn--sm" href="{{ $article->source_url }}" rel="noopener noreferrer" target="_blank">
                            Read the full article on {{ $article->source_name }}
                            <x-icon name="external-link" :size="15" />
                        </a>
                    </div>

                    <div class="feedback-box">
                        <h2>Found something that could be clearer?</h2>
                        <p>Corrections are reviewed and credited. Include the article title and the section you are referring to.</p>
                        <a class="text-link" href="{{ route('pages.about') }}">How to contribute <x-icon name="arrow-right" :size="16" /></a>
                    </div>
                </div>

                <aside class="article-sidebar">
                    <div class="info-card">
                        <h2>Key facts</h2>
                        <dl>
                            <div><dt>Subject</dt><dd><a href="{{ route('knowledge.category', $article->category->slug) }}">{{ $article->category->name }}</a></dd></div>
                            @if ($article->subcategory_label)
                                <div><dt>Topic</dt><dd>{{ $article->subcategory_label }}</dd></div>
                            @endif
                            <div><dt>Difficulty</dt><dd>{{ ucfirst($article->difficulty) }}</dd></div>
                            <div><dt>Reading time</dt><dd>{{ $article->reading_time }} minutes</dd></div>
                            <div><dt>Words</dt><dd>{{ number_format($article->word_count) }}</dd></div>
                            <div><dt>Reviewed</dt><dd>{{ $article->reviewed_at?->format('j M Y') ?? '—' }}</dd></div>
                        </dl>
                    </div>

                    @if ($relatedTools->count())
                        <div class="info-card">
                            <h2>Try a related tool</h2>
                            <ul class="link-list">
                                @foreach ($relatedTools as $tool)
                                    <li>
                                        <a href="{{ route('tools.show', $tool->slug) }}">
                                            <x-icon :name="$tool->icon ?? 'sparkles'" :size="16" />
                                            <span>{{ $tool->name }}</span>
                                            <x-icon name="chevron-right" :size="14" />
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="info-card">
                        <h2>Cite this page</h2>
                        <p class="cite-text" data-citation>UniVerse. “{{ $article->title }}”. Retrieved {{ now()->format('j F Y') }}. {{ route('knowledge.show', [$article->category->slug, $article->slug]) }}</p>
                        <button class="btn btn--ghost btn--sm" type="button" data-copy-citation>
                            <x-icon name="copy" :size="15" /> Copy citation
                        </button>
                    </div>
                </aside>
            </div>
        </section>

        @if ($related->count())
            <section class="section section--tinted">
                <div class="container">
                    <div class="section-head">
                        <div>
                            <p class="eyebrow">Continue reading</p>
                            <h2>Related articles</h2>
                        </div>
                        <a class="text-link" href="{{ route('knowledge.category', $article->category->slug) }}">
                            More {{ $article->category->name }} <x-icon name="arrow-right" :size="16" />
                        </a>
                    </div>
                    <div class="article-grid">
                        @foreach ($related as $relatedArticle)
                            <x-article-card :article="$relatedArticle" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>
@endsection
