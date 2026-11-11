# Contributing to UniVerse

Thanks for helping improve UniVerse. This guide covers local setup, content changes, coding conventions and the checks expected before a change is merged.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

The project uses SQLite by default. PostgreSQL is supported; see [DATABASE.md](docs/DATABASE.md).

## Before you start

- Search existing issues and pull requests.
- Keep changes focused. One tool, article batch or bug fix per pull request is easier to review.
- Do not commit `.env`, `vendor/`, `node_modules/`, database files or generated caches.

## Adding or changing a tool

Tool definitions live in `content/tools/*.php`; see [TOOL_SCHEMA.md](docs/TOOL_SCHEMA.md) for the full format.

```bash
php scripts/build-tools.php
node scripts/test-tools.mjs
php artisan db:seed --class=ToolSeeder
```

Requirements:

- At least two test cases covering normal and edge behaviour.
- Clear error messages for invalid input (zero denominators, impossible dates, out-of-range values).
- Inputs limited to the eight supported types.
- Results use stable labels; tests match on labels.
- The tool works without network access once the page has loaded.

Do not edit `public/assets/js/tools.generated.js` or `public/assets/data/tools.json` by hand.

## Adding or changing knowledge content

Article topics are listed in `content/articles/topics.php`. Content is fetched from Wikipedia:

```bash
php scripts/fetch-wikipedia.php
php artisan db:seed --class=ArticleSeeder
```

Requirements:

- Keep the source URL, licence and retrieval date on every record.
- Use CC BY-SA 4.0-compatible sources only.
- Do not paste copyrighted text from other sites.
- Keep summaries neutral and factual.
- Use the existing category and subcategory slugs where possible.

## Adding a page

1. Create a Blade view under `resources/views/pages/`.
2. Add the slug to the page route loop in `routes/web.php`.
3. Add navigation or footer links only if the page belongs there.
4. Include a page title and meta description via the layout sections.

## Coding conventions

- Follow Laravel conventions and the existing PSR-12 style. Run `vendor/bin/pint` before committing.
- Prefer Eloquent relationships and query scopes over raw SQL.
- Keep SQL portable between SQLite and PostgreSQL.
- Use `LOWER(column) LIKE ?` with bindings for case-insensitive matching.
- Validate and authorise at the boundary; do not trust request data.
- Return typed responses where practical.
- No inline `<script>` blocks. Attach behaviour through `data-*` attributes and files under `public/assets/js/`.
- Use `textContent` or DOM APIs for user-visible dynamic text; do not inject HTML strings.
- CSS belongs in `public/assets/css/app.css` and should reuse the existing custom properties.

## Accessibility

- Every interactive control needs an accessible name.
- Preserve keyboard access and visible focus states.
- Use semantic landmarks and heading order.
- Announce dynamic results with `aria-live`.
- Test at 320 px width and with reduced motion enabled.
- Do not remove the skip link or the `sr-only` labels.

## Tests

```bash
php artisan test
node scripts/test-tools.mjs
```

Add feature tests for new routes, API shapes and search behaviour. Formula coverage belongs in each tool's `tests` array, not in PHPUnit.

## Commit messages

Use a short imperative subject and explain the reason in the body when it is not obvious:

```
Add loan affordability calculator

Covers repayment-to-income and total interest, with three test cases.
```

## Reporting issues

Include:

- The URL or tool slug.
- The inputs used and the result you expected.
- Browser and device.
- A screenshot for layout or accessibility problems.
- The exact error text for server errors.

## Licence

By contributing code, you agree that it is released under the MIT licence. By contributing knowledge content, you confirm it can be distributed under CC BY-SA 4.0 and that attribution metadata is preserved.
