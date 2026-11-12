# UniVerse

**Tools for life. Knowledge for everyone.**

UniVerse is a single, fast, account-free web platform that combines two products:

- **UtilityWorld** — 100 interactive tools: calculators, converters, generators, text utilities and developer helpers.
- **KnowledgeWorld** — 500 sourced educational articles adapted from Wikipedia, grouped into ten subject areas.

Everything is searchable from one place. Calculators run in the browser, so sensitive numbers never leave the device.

```
100 tools · 500 articles · 9 tool categories · 10 knowledge categories
```

## Highlights

- **No account required.** Every tool and article is public.
- **Private by design.** Tool inputs are processed client-side; the server never receives them.
- **One search index.** Universal search ranks tools and articles together by relevance.
- **Sourced knowledge.** Every article records its Wikipedia source, licence (CC BY-SA 4.0) and retrieval date.
- **Accessible and responsive.** Keyboard-friendly navigation, skip link, reduced-motion support, semantic markup and a mobile layout.
- **Portable database.** SQLite for local development, PostgreSQL-ready schema and portable SQL.
- **Tested calculations.** Every tool ships with at least two test cases; 749 assertions currently pass.
- **No front-end build step.** Plain CSS and ES modules are served directly from `public/assets`.

## Requirements

- PHP 8.3 or newer (built and tested on PHP 8.5)
- Composer 2
- Node.js 20+ (only for the tool test runner)
- SQLite (default) or PostgreSQL

## Quick start

```bash
git clone <repository-url> UniVerse
cd UniVerse
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Open <http://localhost:8000>.

The seeders import the tool catalogue and all 500 articles, so the first seed takes a few seconds.

### Rebuilding from scratch

```bash
php artisan migrate:fresh --seed
```

### Using PostgreSQL

Set the connection in `.env` and create the database:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=universe
DB_USERNAME=universe
DB_PASSWORD=secret
```

Then run `php artisan migrate --seed`. The schema uses standard Laravel column types and adds a `CHECK` constraint on `bookmarks` when the driver is PostgreSQL.

## Deploy for free

A production `Dockerfile`, Render Blueprint and step-by-step guide are included:

```
GitHub → Render free web service (Docker) → Neon free PostgreSQL
```

See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for the complete walkthrough, environment variables and free-tier caveats. For a quick demo, a free Cloudflare Tunnel can expose the local server without deploying anything.

## Content pipeline

Tool definitions live in `content/tools/*.php`. After editing them, rebuild the client engine and run the calculation tests:

```bash
php scripts/build-tools.php     # validate + compile tools.generated.js and tools.json
node scripts/test-tools.mjs     # run every declared test case
php artisan db:seed --class=ToolSeeder
```

Articles are fetched from the Wikipedia API and stored in `content/articles/data.json`:

```bash
php scripts/fetch-wikipedia.php            # resumable; caches API responses
php scripts/fetch-wikipedia.php --refresh  # ignore the cache
php artisan db:seed --class=ArticleSeeder
```

The fetcher keeps the source URL, licence and retrieval date with every record. See [docs/TOOL_SCHEMA.md](docs/TOOL_SCHEMA.md) for the tool definition format.

## Testing

```bash
php artisan test          # application feature tests
node scripts/test-tools.mjs
```

The feature suite uses an in-memory SQLite database and seeds the real catalogue.

## Project layout

```
app/
  Http/Controllers/          Web and API controllers
  Models/                    Eloquent models
  Modules/Search/            Universal search scoring
  Modules/Utilities/         Tool registry
content/
  tools/                     Declarative tool definitions (source of truth)
  articles/                  Topic list and fetched Wikipedia data
database/
  migrations/                Platform schema
  seeders/                   Idempotent tool and article import
public/assets/
  css/app.css                Design system
  js/app.js                  Navigation, progress and copy helpers
  js/tool-runner.js          Client-side calculator runtime
  js/tools.generated.js      Compiled tool engine (generated)
  data/tools.json            Public tool catalogue (generated)
scripts/
  build-tools.php            Validate and compile tools
  test-tools.mjs             Run tool test cases
  fetch-wikipedia.php        Fetch and cache article content
docs/                        Architecture, database, API and schema docs
```

## Documentation

- [Deployment (free)](docs/DEPLOYMENT.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Database](docs/DATABASE.md)
- [API](docs/API.md)
- [Tool schema](docs/TOOL_SCHEMA.md)
- [Contributing](CONTRIBUTING.md)

## Privacy

UniVerse does not require accounts and does not store tool inputs. Tool usage is counted with a one-way session hash for at most 30 minutes; search terms are logged without IP addresses or user identifiers. See the in-app [privacy page](http://localhost:8000/privacy) for the full policy.

## Licence

Application code is released under the MIT licence.

Knowledge article text is adapted from Wikipedia and is available under the [Creative Commons Attribution-ShareAlike 4.0 International licence](https://creativecommons.org/licenses/by-sa/4.0/). Each article page links to its source and records the retrieval date.
