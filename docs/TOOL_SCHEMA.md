# Tool schema

Every UtilityWorld tool is declared in `content/tools/<category>.php`. The files return a PHP array of tool definitions. They are the source of truth: the database catalogue and the client-side engine are both generated from them.

## Workflow

```bash
# 1. Edit content/tools/*.php
php scripts/build-tools.php     # validate + regenerate public assets
node scripts/test-tools.mjs     # run every declared test case
php artisan db:seed --class=ToolSeeder
```

`php scripts/build-tools.php` writes:

- `public/assets/js/tools.generated.js` — compiled compute functions (`TOOLS` map).
- `public/assets/data/tools.json` — public catalogue with inputs and tests.

Do not edit generated files by hand; they are overwritten on every build.

## Top-level definition

| Key | Required | Type | Rules |
| --- | --- | --- | --- |
| `slug` | yes | string | Lowercase kebab-case, globally unique: `^[a-z0-9]+(?:-[a-z0-9]+)*$` |
| `name` | yes | string | Display title |
| `category` | yes | string | One of the nine category slugs below |
| `description` | yes | string | 160 characters or fewer |
| `icon` | yes | string | Icon key available in `x-icon` |
| `featured` | no | bool | Promoted on the home page |
| `keywords` | no | string[] | Search synonyms |
| `inputs` | yes | array | 1–8 input definitions |
| `compute` | yes | string | JavaScript function body (heredoc recommended) |
| `tests` | yes | array | At least two test cases |
| `version` | no | string | Stored by the seeder, default `1.0.0` |
| `status` | no | string | `published` by default |

Valid category slugs: `mathematics`, `text`, `conversion`, `datetime`, `science`, `finance`, `developer`, `education`, `everyday`.

Category display names, descriptions and icons live in `app/Modules/Utilities/ToolRegistry.php`.

## Input definition

| Key | Required | Notes |
| --- | --- | --- |
| `name` | yes | Unique within the tool; used as the form field and compute key |
| `label` | yes | Visible label |
| `type` | yes | `number`, `text`, `textarea`, `select`, `checkbox`, `date`, `time` or `color` |
| `default` | no | Initial value; also merged into test inputs |
| `placeholder` | no | Input hint |
| `hint` | no | Help text under the field |
| `required` | no | Adds the HTML `required` attribute |
| `min`, `max`, `step` | no | Passed through to number inputs |
| `options` | select only | Array of `['value' => …, 'label' => …]` |
| `showIf` | no | Map of input name → allowed value(s) |

### Conditional fields

`showIf` hides a field until another input has an allowed value. It is evaluated on load and on every `change`:

```php
[
    'name' => 'custom_rate',
    'label' => 'Custom rate (%)',
    'type' => 'number',
    'showIf' => ['rate_mode' => ['custom']],
]
```

The value can be a scalar or an array of accepted values. Hidden fields remain in the form and are still sent to `compute`; guard against irrelevant values in the function body.

## Compute body

`compute` is the body of an async arrow function `(v, h) => { … }`. It has access to:

- `v` — object of raw form values. Checkboxes are booleans; everything else is a string (or the declared default).
- `h` — helper object:

| Helper | Behaviour |
| --- | --- |
| `h.n(value)` | Parse a number, strip commas, reject empty/non-finite input with a clear error |
| `h.round(value, decimals = 2)` | Symmetric rounding |
| `h.clamp(value, min, max)` | Clamp to a range |
| `h.pct(part, whole)` | Percentage, throwing when `whole` is zero |
| `h.splitLines(text)` | Trimmed non-empty lines |
| `h.words(text)` | Whitespace-separated words |
| `h.escapeHtml(text)` | Escape HTML entities (rarely needed; results are rendered with `textContent`) |

The function may be async (`await` is supported) and must return either one result object or an array of result objects.

### Result object

| Key | Required | Notes |
| --- | --- | --- |
| `label` | yes | Card heading; also the key used by tests |
| `value` | yes | Number or string |
| `unit` | no | Rendered next to the value |
| `decimals` | no | Number of decimal places for numeric values |
| `hint` | no | Secondary explanatory line |

Throw `new Error('message')` for invalid input. The runner catches it and displays the message in the results panel with `role="alert"`. Never return `NaN` or `Infinity`; `h.n` and the renderer reject non-finite numbers.

Results are rendered with DOM APIs, so returned strings are safe by default. Do not return HTML.

## Tests

Each test supplies input overrides and expected result values keyed by result label:

```php
'tests' => [
    ['input' => ['mode' => 'of', 'x' => 20, 'y' => 150], 'expect' => ['Result' => 30]],
    ['input' => ['mode' => 'change', 'x' => 50, 'y' => 75], 'expect' => ['Percentage change' => 50]],
],
```

- Declared defaults are merged with `input`, so tests only override what matters.
- Numeric expectations allow a relative tolerance of `1e-6`.
- String expectations are compared with `String(...)`.
- A missing result label is a failure.
- At least two tests are required per tool.

## Complete example

```php
<?php
/** UtilityWorld — Mathematics */

return [
    [
        'slug' => 'percentage-calculator',
        'name' => 'Percentage Calculator',
        'category' => 'mathematics',
        'description' => 'Find a percentage of a number, percentage change, or a reverse percentage.',
        'icon' => 'percent',
        'featured' => true,
        'keywords' => ['percentage', 'percent', 'increase', 'decrease', 'discount'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Calculation',
                'type' => 'select',
                'default' => 'of',
                'options' => [
                    ['value' => 'of', 'label' => 'What is X% of Y?'],
                    ['value' => 'change', 'label' => 'Percentage change from X to Y'],
                    ['value' => 'reverse', 'label' => 'Y is X% of what number?'],
                ],
            ],
            ['name' => 'x', 'label' => 'Value X', 'type' => 'number', 'default' => 20, 'step' => 'any', 'required' => true],
            ['name' => 'y', 'label' => 'Value Y', 'type' => 'number', 'default' => 150, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const x = h.n(v.x);
            const y = h.n(v.y);
            if (v.mode === 'of') {
                return { label: 'Result', value: (x / 100) * y, hint: `${x}% of ${y}` };
            }
            if (v.mode === 'reverse') {
                if (x === 0) throw new Error('The percentage cannot be zero.');
                return { label: 'Whole value', value: y / (x / 100) };
            }
            if (x === 0) throw new Error('The starting value cannot be zero.');
            const change = ((y - x) / Math.abs(x)) * 100;
            return { label: 'Percentage change', value: change, unit: '%' };
        JS,
        'tests' => [
            ['input' => ['mode' => 'of', 'x' => 20, 'y' => 150], 'expect' => ['Result' => 30]],
            ['input' => ['mode' => 'change', 'x' => 50, 'y' => 75], 'expect' => ['Percentage change' => 50]],
            ['input' => ['mode' => 'reverse', 'x' => 20, 'y' => 30], 'expect' => ['Whole value' => 150]],
        ],
    ],
];
```

## Validation checklist

The build fails when any of these are true:

- A required key is missing.
- A slug is malformed or duplicated.
- The category is unknown.
- The description exceeds 160 characters.
- There are fewer than one or more than eight inputs.
- An input lacks a name or label, reuses a name, or uses an unsupported type.
- A `select` has no options.
- `showIf` is not an array.
- There are fewer than two tests, or a test lacks `input`/`expect`.
- `compute` is empty.

Run `node scripts/test-tools.mjs` as well; the build validates structure, while the test runner verifies behaviour.

## Adding a new tool

1. Add the definition to the matching `content/tools/*.php` file.
2. Run `php scripts/build-tools.php`.
3. Run `node scripts/test-tools.mjs` and fix any failing expectations.
4. Run `php artisan db:seed --class=ToolSeeder`.
5. Load `/tools/<slug>` and confirm the form, live results and URL state.
6. Add the tool to the feature test list only if it changes shared behaviour; individual formulas are covered by the Node suite.
