# API

A small read-only JSON API is available under `/api/v1`. It exposes the same published catalogue as the website and requires no authentication.

All responses use `Content-Type: application/json`. Errors use Laravel's standard JSON error shape (`404` for unknown records).

Base URL locally: `http://localhost:8000/api/v1`

## Endpoints

| Method | Path | Description |
| --- | --- | --- |
| `GET` | `/tools` | Paginated tool catalogue |
| `GET` | `/tools/{slug}` | One tool, including its input schema |
| `GET` | `/articles` | Paginated article catalogue |
| `GET` | `/articles/{slug}` | One article, including full text |
| `GET` | `/categories` | Tool and article categories with counts |
| `GET` | `/search?q=…` | Universal search across tools and articles |

## `GET /tools`

Query parameters:

| Parameter | Type | Description |
| --- | --- | --- |
| `category` | string | Tool category slug, e.g. `mathematics` |
| `q` | string | Case-insensitive name/description filter |
| `page` | integer | Page number (50 per page) |

```json
{
  "data": [
    {
      "slug": "percentage-calculator",
      "name": "Percentage Calculator",
      "description": "Find a percentage of a number, percentage change, or a reverse percentage.",
      "category": "mathematics",
      "category_name": "Mathematics",
      "icon": "percent",
      "keywords": ["percentage", "percent", "increase", "decrease", "discount"],
      "version": "1.0.0",
      "featured": true,
      "url": "http://localhost:8000/tools/percentage-calculator"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 4,
    "per_page": 50,
    "total": 200
  }
}
```

## `GET /tools/{slug}`

Returns the same resource plus `inputs`, `run_in_browser` and `engine`.

```json
{
  "data": {
    "slug": "percentage-calculator",
    "name": "Percentage Calculator",
    "inputs": [
      {
        "name": "mode",
        "label": "Calculation",
        "type": "select",
        "default": "of",
        "options": [
          { "value": "of", "label": "What is X% of Y?" }
        ]
      },
      { "name": "x", "label": "Value X", "type": "number", "default": 20, "required": true }
    ],
    "run_in_browser": true,
    "engine": "http://localhost:8000/assets/js/tools.generated.js"
  }
}
```

`inputs` is an ordered array. Supported types are `number`, `text`, `textarea`, `select`, `checkbox`, `date`, `time` and `color`. Optional keys include `placeholder`, `hint`, `min`, `max`, `step`, `required`, `default` and `showIf`.

The API describes tools; it does **not** execute them. Import `engine` as an ES module in a browser to use the compiled `TOOLS` map, or reimplement the formula from the description. The public catalogue without compute code is also available at `/assets/data/tools.json`.

## `GET /articles`

Query parameters:

| Parameter | Type | Description |
| --- | --- | --- |
| `category` | string | Article category slug, e.g. `science` |
| `difficulty` | string | `beginner`, `intermediate` or `advanced` |
| `q` | string | Case-insensitive title/summary filter |
| `page` | integer | Page number (20 per page) |

```json
{
  "data": [
    {
      "slug": "tariff",
      "title": "Tariff",
      "summary": "A tariff, or import tax, is a duty imposed …",
      "category": "economics",
      "category_name": "Economics",
      "subcategory": "trade",
      "difficulty": "intermediate",
      "reading_time": 1,
      "word_count": 192,
      "tags": ["tariff"],
      "source": {
        "name": "Wikipedia",
        "url": "https://en.wikipedia.org/wiki/Tariff",
        "license": "CC BY-SA 4.0",
        "retrieved_at": "2026-11-11"
      },
      "url": "http://localhost:8000/knowledge/economics/tariff"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 500,
    "per_page": 20,
    "total": 10000
  }
}
```

## `GET /articles/{slug}`

Returns the article resource plus `content` (plain text) and `paragraphs` (array of strings).

Article slugs are globally unique. The website URL also contains the category slug, but the API accepts the article slug alone.

## `GET /categories`

```json
{
  "data": {
    "tool_categories": [
      {
        "slug": "mathematics",
        "name": "Mathematics",
        "description": "Arithmetic, algebra, geometry and statistics calculators.",
        "icon": "calculator",
        "count": 12,
        "url": "http://localhost:8000/tools?category=mathematics"
      }
    ],
    "article_categories": [
      {
        "slug": "science",
        "name": "Science",
        "description": "Physics, chemistry, biology, astronomy and Earth science.",
        "icon": "atom",
        "count": 90,
        "url": "http://localhost:8000/knowledge/science"
      }
    ]
  }
}
```

## `GET /search`

| Parameter | Type | Description |
| --- | --- | --- |
| `q` | string | Search term; at least two characters |
| `type` | string | `all` (default), `tool` or `article` |

```json
{
  "data": [
    {
      "type": "tool",
      "title": "Percentage Calculator",
      "description": "Find a percentage of a number, percentage change, or a reverse percentage.",
      "url": "http://localhost:8000/tools/percentage-calculator",
      "category": "Mathematics",
      "type_label": "Tool",
      "icon": "percent",
      "score": 270
    },
    {
      "type": "article",
      "title": "Inflation",
      "description": "…snippet around the match…",
      "url": "http://localhost:8000/knowledge/economics/inflation",
      "category": "Economics",
      "type_label": "Article",
      "icon": "book-open",
      "score": 35
    }
  ],
  "meta": {
    "term": "percentage",
    "total": 12
  }
}
```

Terms shorter than two characters return an empty `data` array with `total: 0`.

Ranking is described in [ARCHITECTURE.md](ARCHITECTURE.md#search). The API returns at most 50 results.

## Caching and limits

- Responses are generated per request; there is no public cache header yet.
- The API is read-only and currently unauthenticated. Put it behind a reverse-proxy rate limit before high-traffic public deployment.
- Pagination links are not included in the payload; construct `?page=N` from `meta.last_page`.

## CORS

No permissive CORS headers are sent. Browser clients on other origins must proxy requests through their own server, or CORS must be enabled explicitly in `config/cors.php` (not currently published).

## Examples

```bash
curl http://localhost:8000/api/v1/tools?category=mathematics
curl http://localhost:8000/api/v1/tools/percentage-calculator
curl "http://localhost:8000/api/v1/articles?category=science&difficulty=beginner"
curl "http://localhost:8000/api/v1/search?q=compound%20interest&type=tool"
curl http://localhost:8000/api/v1/categories
```
