# Database

UniVerse uses SQLite for local development and is ready for PostgreSQL in production. All schema changes live in `database/migrations/2026_11_11_000001_create_platform_tables.php`.

## Tables at a glance

| Table | Purpose | Rows after seeding |
| --- | --- | ---: |
| `tool_categories` | Tool groupings | 12 |
| `tools` | Published tool catalogue and input schemas | 100 |
| `article_categories` | Knowledge subject areas | 10 |
| `articles` | Sourced knowledge articles | 500 |
| `bookmarks` | Optional saved items for future accounts | 0 |
| `tool_usage_events` | Privacy-friendly usage counts | grows with traffic |
| `search_logs` | Search term analytics | grows with traffic |

Laravel's default `users`, `sessions`, `cache`, `jobs` and related tables are also present.

## `tool_categories`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint, PK | |
| `name` | varchar(100) | Display name |
| `slug` | varchar(120), unique | Used in `/tools?category=…` |
| `description` | text, nullable | Category summary |
| `icon` | varchar(80), nullable | Icon key used by the Blade component |
| `sort_order` | smallint | Ascending display order |
| `created_at`, `updated_at` | timestamps | |

Seeded categories: `mathematics`, `text`, `conversion`, `datetime`, `science`, `finance`, `developer`, `education`, `everyday`, plus three empty "coming soon" categories (`images`, `pdf`, `internet`).

## `tools`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint, PK | |
| `tool_category_id` | FK → `tool_categories.id` | `restrictOnDelete` |
| `name` | varchar(150) | |
| `slug` | varchar(180), unique | Route key |
| `description` | text | Max 160 characters in source |
| `icon` | varchar(80), nullable | |
| `keywords` | json, nullable | Search synonyms |
| `inputs` | json | Ordered input schema rendered by Blade |
| `version` | varchar(30) | Default `1.0.0` |
| `status` | varchar(20) | `published` or hidden |
| `featured` | boolean | Home page and related content |
| `usage_count` | unsigned int | Incremented at most once per session per 30 minutes |
| `created_at`, `updated_at` | timestamps | |

Indexes: `(tool_category_id, status)`, `(featured, status)`, `usage_count`.

The `inputs` JSON is the contract between the database, the Blade form renderer and the client runtime. It is copied from `content/tools/*.php` by `ToolSeeder`; edit the source file, not the database.

## `article_categories`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint, PK | |
| `parent_id` | FK → self, nullable | Reserved for future nesting |
| `name` | varchar(120) | |
| `slug` | varchar(150), unique | Used in `/knowledge/{category}` |
| `description` | text, nullable | |
| `icon` | varchar(80), nullable | |
| `sort_order` | smallint | Ascending display order |
| `created_at`, `updated_at` | timestamps | |

Seeded categories and article counts:

| Category | Articles |
| --- | ---: |
| Science | 90 |
| Technology | 70 |
| History | 75 |
| Geography | 65 |
| Mathematics | 50 |
| Economics | 40 |
| Languages | 25 |
| Engineering | 45 |
| Health education | 25 |
| Life skills | 15 |

## `articles`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint, PK | |
| `article_category_id` | FK → `article_categories.id` | `restrictOnDelete` |
| `subcategory` | varchar(120), nullable | Machine key used for topic filters |
| `subcategory_label` | varchar(150), nullable | Human-readable topic label |
| `title` | varchar(250) | |
| `slug` | varchar(280), unique | Globally unique route key |
| `summary` | text | Short introduction |
| `content` | longtext | Plain-text intro extract; paragraphs separated by blank lines |
| `difficulty` | varchar(30) | `beginner`, `intermediate` or `advanced` |
| `status` | varchar(20) | `published` or hidden |
| `language` | varchar(10) | Default `en` |
| `tags` | json, nullable | Search and display tags |
| `reading_time` | smallint | Minutes |
| `word_count` | unsigned int | |
| `source_name` | varchar(120), nullable | e.g. `Wikipedia` |
| `source_url` | text, nullable | Full article URL |
| `license` | varchar(80), nullable | e.g. `CC BY-SA 4.0` |
| `retrieved_at` | date, nullable | Date the source was fetched |
| `published_at` | timestamp, nullable | Used for "latest" ordering |
| `reviewed_at` | timestamp, nullable | Editorial review date |
| `created_at`, `updated_at` | timestamps | |

Indexes: `(article_category_id, status)`, `(status, published_at)`, `difficulty`, `subcategory`.

Article slugs are unique across the whole table even though the public URL includes the category. `ArticleController@show` verifies that the category slug matches the article's category and returns 404 otherwise.

## `bookmarks`

Reserved for optional accounts. A bookmark targets exactly one article or one tool.

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint, PK | |
| `user_id` | FK → `users.id` | `cascadeOnDelete` |
| `article_id` | FK → `articles.id`, nullable | `cascadeOnDelete` |
| `tool_id` | FK → `tools.id`, nullable | `cascadeOnDelete` |
| `created_at`, `updated_at` | timestamps | |

Unique constraints: `(user_id, article_id)` and `(user_id, tool_id)`.

On PostgreSQL the migration adds `bookmarks_single_target`, a `CHECK` constraint requiring exactly one of `article_id` or `tool_id` to be non-null. SQLite cannot add `CHECK` constraints after table creation, so the constraint is skipped there; the application should enforce the same rule.

## `tool_usage_events`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint, PK | |
| `tool_id` | FK → `tools.id` | `cascadeOnDelete` |
| `session_hash` | varchar(64), nullable | SHA-256 of the session ID |
| `created_at` | timestamp | No `updated_at` |

Index: `(tool_id, created_at)`.

No IP address, user agent or input value is stored. The same session is counted once per tool per 30 minutes using the cache.

## `search_logs`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint, PK | |
| `term` | varchar(200) | Truncated search term |
| `result_count` | unsigned int | Number of results returned |
| `created_at` | timestamp | No `updated_at` |

Index: `created_at`.

Terms shorter than two characters are not logged. Logs contain no user identifier.

## Relationships

```
tool_categories 1 ── * tools
article_categories 1 ── * articles
article_categories 1 ── * article_categories (parent/children, reserved)
users 1 ── * bookmarks * ── 0..1 tools
users 1 ── * bookmarks * ── 0..1 articles
tools 1 ── * tool_usage_events
```

## Seeding

`DatabaseSeeder` runs `ToolSeeder` then `ArticleSeeder`. Both are idempotent:

```bash
php artisan db:seed
php artisan db:seed --class=ToolSeeder
php artisan db:seed --class=ArticleSeeder
php artisan migrate:fresh --seed
```

- `ToolSeeder` reads `content/tools/*.php` through `ToolRegistry` and uses `updateOrCreate` by slug.
- `ArticleSeeder` reads `content/articles/data.json`, creates categories by slug and uses `updateOrCreate` by article slug.

## Portability notes

- Column types are standard Laravel types; JSON columns become `json` on PostgreSQL and `text` on SQLite.
- Search uses `LOWER(column) LIKE ?`, which works on both drivers. There is no SQLite-specific FTS dependency.
- The only conditional DDL is the bookmark `CHECK` constraint, guarded by `DB::getDriverName() === 'pgsql'`.
- Foreign keys are enabled by Laravel's SQLite connection by default in current framework versions.

## Backup and retention

- Back up `database/database.sqlite` (or the PostgreSQL database) and `content/` together. Content files are the reproducible source of truth.
- `tool_usage_events` and `search_logs` can be pruned periodically; they are not needed to render the site.
- Wikipedia content is stored with attribution metadata, so re-importing `data.json` is always safe.
