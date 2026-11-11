# Architecture

UniVerse is a modular monolith built on Laravel. The two product areas — UtilityWorld and KnowledgeWorld — share one public shell, one search service and one database.

## Goals

1. Keep tools fast, private and usable without an account.
2. Keep knowledge content attributable and easy to re-import.
3. Share navigation, search and design across both worlds.
4. Stay deployable on a single server with SQLite locally and PostgreSQL in production.
5. Avoid a front-end build pipeline; assets are plain CSS and ES modules.

## High-level structure

```
Browser
  │
  ├── Blade-rendered pages (HTML)
  ├── public/assets/css/app.css
  ├── public/assets/js/app.js            shared UI behaviour
  └── public/assets/js/tool-runner.js    per-tool calculator runtime
            │
            └── tools.generated.js         compiled compute functions
  │
  ▼
Laravel routes
  ├── Web controllers  → Blade views
  └── API controllers  → JSON
        │
        ├── App\Modules\Utilities\ToolRegistry   reads content/tools/*.php
        ├── App\Modules\Search\SearchService     ranks tools + articles
        └── Eloquent models                      SQLite / PostgreSQL
```

## Request flow

### Pages

1. `routes/web.php` maps each URL to a controller.
2. Controllers query Eloquent models and pass data to Blade views.
3. `layouts/app.blade.php` renders the shared header, navigation, footer, meta tags and asset links.
4. `SecurityHeaders` middleware adds CSP, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` and `Permissions-Policy` to every response.

### Tool execution

Tool pages are server-rendered from the database, but calculations happen in the browser:

1. `ToolSeeder` imports `content/tools/*.php` through `ToolRegistry` into the `tools` table.
2. `ToolController@show` loads the tool and renders its inputs from the stored JSON schema.
3. `tool-runner.js` looks up the tool slug in `TOOLS` from `tools.generated.js`.
4. On submit or input, the runner collects form values, calls the compiled function with helpers (`h.n`, `h.round`, `h.clamp`, `h.pct`, …) and renders result cards.
5. Results are written to the URL query string, so a calculation can be bookmarked or shared.
6. The first interaction posts to `/tools/{slug}/visit`; the controller increments `usage_count` at most once per session per 30 minutes.

Inputs never leave the browser. The visit endpoint receives no field values.

### Search

`SearchService` queries tools and articles separately with portable `LOWER(column) LIKE ?` predicates, scores each match in PHP, merges the two collections and returns the highest-scoring results. Exact title matches rank highest, followed by title prefixes, title substrings, keywords/tags, summaries and body text.

The service is deliberately simple and database-portable. It can be replaced with PostgreSQL full-text search or an external index without changing the controllers, because the controllers only depend on `search()` and `log()`.

## Modules

### `App\Modules\Utilities\ToolRegistry`

Reads every `content/tools/*.php` file, normalises category metadata and exposes:

- `ToolRegistry::all()` — every tool definition, sorted by slug.
- `ToolRegistry::categories()` — category names, descriptions, icons and sort order.
- `ToolRegistry::find(string $slug)` — a single definition.

The registry is the source of truth for seeding. It is not used to execute calculations at runtime.

### `App\Modules\Search\SearchService`

Provides universal search and search logging. Ranking weights are documented in the class and covered by feature tests.

## Data model

The schema has four content tables (`tool_categories`, `tools`, `article_categories`, `articles`) and three supporting tables (`bookmarks`, `tool_usage_events`, `search_logs`). See [DATABASE.md](DATABASE.md).

## Content pipeline

### Tools

```
content/tools/*.php
  │  php scripts/build-tools.php
  ├── validates schema, slugs, categories, input types and test cases
  ├── public/assets/js/tools.generated.js   compute functions
  └── public/assets/data/tools.json         public catalogue
        │  php artisan db:seed --class=ToolSeeder
        └── tools table
```

`scripts/test-tools.mjs` imports the generated engine and runs every declared test case. The build fails if a tool has fewer than two tests, a duplicate slug, an unsupported input type or an unknown category.

### Articles

```
content/articles/topics.php          planned categories, subcategories and titles
  │  php scripts/fetch-wikipedia.php
  ├── Wikipedia API (intro extracts, CC BY-SA 4.0)
  ├── content/articles/cache/        resumable response cache
  └── content/articles/data.json     article records
        │  php artisan db:seed --class=ArticleSeeder
        └── articles table
```

`ArticleSeeder` is idempotent: it updates records by slug and creates missing categories. Article slugs are globally unique; the article route still includes the category slug for readable URLs and redirects mismatched category/slug pairs to 404.

## Front-end

- **No bundler.** `public/assets/css/app.css` is a hand-written design system using CSS custom properties. `public/assets/js/*.js` are native ES modules or classic scripts.
- **`app.js`** handles the mobile navigation, sticky-header shadow, article reading progress, copy-link/copy-citation buttons and resetting tool URL state.
- **`tool-runner.js`** is the only module that imports `tools.generated.js`. It renders results with DOM APIs and `textContent`, not HTML injection.
- **Progressive enhancement.** Forms and content remain readable without JavaScript; a `<noscript>` notice explains that calculations require it.

## Security

- Content Security Policy allows only same-origin scripts and styles (inline styles are permitted for the small number of dynamic values).
- No inline `<script>` blocks; behaviour is attached through `data-*` attributes.
- All database access goes through Eloquent or parameterised `whereRaw` bindings.
- Usage tracking stores a SHA-256 hash of the session ID, never an IP address or user agent.
- Search logs contain only the term, result count and timestamp.
- CSRF protection is enabled for the tracking endpoint; the token is read from the layout meta tag.

## Accessibility and responsiveness

- Skip link, landmark elements, visible focus styles and `aria-expanded` on the mobile menu.
- Semantic headings, descriptive link text, labelled form fields and `aria-live` result regions.
- `prefers-reduced-motion` disables animations and smooth scrolling.
- Print styles hide navigation, sidebars and actions.
- Layout breakpoints at 1020 px, 860 px and 620 px; the navigation collapses to a toggle at 860 px.

## Extension points

- **Add a tool:** add a definition to `content/tools/<category>.php`, rebuild, test and reseed. No PHP controller changes are needed.
- **Add a knowledge category:** add it to `content/articles/topics.php`, fetch, then reseed.
- **Add a page:** create a view under `resources/views/pages/` and add the slug to the page route loop in `routes/web.php`.
- **Change ranking:** edit `SearchService` only; controllers and views consume the same result shape.
- **Move to PostgreSQL:** change `DB_CONNECTION` and rerun migrations. The only driver-specific code is the bookmark `CHECK` constraint.
