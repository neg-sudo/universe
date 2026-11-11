@props(['article'])

<article class="article-card">
    <div class="article-card__meta">
        <span class="pill pill--soft">{{ $article->category->name }}</span>
        <span class="dot" aria-hidden="true"></span>
        <span>{{ $article->reading_time }} min read</span>
    </div>
    <h3 class="article-card__title">
        <a href="{{ route('knowledge.show', [$article->category->slug, $article->slug]) }}">{{ $article->title }}</a>
    </h3>
    <p class="article-card__summary">{{ $article->summary }}</p>
    <div class="article-card__footer">
        <span class="difficulty difficulty--{{ $article->difficulty }}">{{ ucfirst($article->difficulty) }}</span>
        @if ($article->subcategory_label)
            <span class="article-card__topic">{{ $article->subcategory_label }}</span>
        @endif
    </div>
</article>
