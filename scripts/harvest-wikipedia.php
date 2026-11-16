<?php
/**
 * Harvests the KnowledgeWorld catalogue from Wikipedia.
 *
 * Usage:
 *   php scripts/harvest-wikipedia.php                 # full 10,000-article build
 *   php scripts/harvest-wikipedia.php --refresh       # ignore caches and rebuild
 *   php scripts/harvest-wikipedia.php --plan-only     # build the title plan only
 *   php scripts/harvest-wikipedia.php --only=science/physics --plan-cap=40 --target=20
 *
 * Output:
 *   content/articles/data/part-XXXX.jsonl   article records (one JSON object per line)
 *   content/articles/data/manifest.json     counts, shard list and attribution metadata
 *
 * Wikipedia text is available under CC BY-SA 4.0. Every record keeps its source
 * URL, licence and retrieval date so the UI can attribute it.
 */

declare(strict_types=1);

$root = dirname(__DIR__);

$options = [];
foreach (array_slice($argv, 1) as $arg) {
    if (!str_starts_with($arg, '--')) {
        continue;
    }
    $parts = explode('=', substr($arg, 2), 2);
    $options[$parts[0]] = $parts[1] ?? true;
}

$refresh = isset($options['refresh']);
$planOnly = isset($options['plan-only']);
$target = max(1, (int) ($options['target'] ?? 10000));
$quota = max(1, (int) ($options['quota'] ?? 280));
$planCap = max($target, (int) ($options['plan-cap'] ?? 12000));
$minChars = max(80, (int) ($options['min-chars'] ?? 300));
$delayMs = max(250, (int) ($options['delay'] ?? 1500));
$only = isset($options['only']) && is_string($options['only']) ? trim($options['only'], '/') : null;
$shardSize = max(100, (int) ($options['shard-size'] ?? 1000));

$cacheDir = $root . '/content/articles/cache';
$apiCacheDir = $cacheDir . '/api';
$extractCacheDir = $cacheDir . '/extract';
$dataDir = $root . '/content/articles/data';
$planFile = $cacheDir . '/plan.json';

foreach ([$apiCacheDir, $extractCacheDir, $dataDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

$topics = require $root . '/content/articles/topics.php';
$sources = require $root . '/content/articles/sources.php';

$userAgent = 'UniVerseKnowledgeBot/1.0 (https://github.com/neg-sudo/universe; educational open-knowledge project)';
$lastRequestAt = 0.0;

/** Throttle every network request so Wikipedia does not rate-limit us. */
function throttle(float &$lastRequestAt, int $delayMs): void
{
    $now = microtime(true);
    $wait = $lastRequestAt + ($delayMs / 1000) - $now;
    if ($wait > 0) {
        usleep((int) ($wait * 1_000_000));
    }
    $lastRequestAt = microtime(true);
}

/**
 * Replace invalid UTF-8 byte sequences so json_encode() never fails.
 */
function cleanUtf8(string $value): string
{
    if (mb_check_encoding($value, 'UTF-8')) {
        return $value;
    }

    return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
}

/**
 * GET a URL and return [status, body, headers].
 *
 * @return array{0:int,1:string,2:array<string,string>}
 */
function httpGet(string $url, string $userAgent): array
{
    $ch = curl_init($url);
    $headers = [];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 90,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_ENCODING => '',
        CURLOPT_HEADERFUNCTION => static function ($ch, string $line) use (&$headers): int {
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
            return strlen($line);
        },
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    if ($body === false) {
        fwrite(STDERR, "Network error for $url: $error\n");
        return [0, '', $headers];
    }

    return [$status, (string) $body, $headers];
}

/**
 * Fetch a MediaWiki API response, with disk caching and 429/5xx backoff.
 *
 * @param array<string,mixed> $params
 * @return array<string,mixed>|null
 */
function wikiApi(array $params, bool $refresh, string $cacheDir, string $kind, string $userAgent, float &$lastRequestAt, int $delayMs): ?array
{
    $params = array_merge([
        'action' => 'query',
        'format' => 'json',
        'formatversion' => '2',
    ], $params);

    $cacheKey = sha1($kind . '|' . json_encode($params));
    $cacheFile = $cacheDir . '/' . $kind . '-' . $cacheKey . '.json';

    if (!$refresh && is_file($cacheFile)) {
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $url = 'https://en.wikipedia.org/w/api.php?' . http_build_query($params);

    for ($attempt = 1; $attempt <= 6; $attempt++) {
        throttle($lastRequestAt, $delayMs);
        [$status, $body, $headers] = httpGet($url, $userAgent);

        if ($status === 200) {
            $data = json_decode($body, true);
            if (is_array($data)) {
                file_put_contents($cacheFile, $body);
                return $data;
            }
            fwrite(STDERR, "Invalid JSON for $url\n");
            return null;
        }

        if ($status === 429) {
            $retryAfter = (int) ($headers['retry-after'] ?? 0);
            $wait = $retryAfter > 0 ? $retryAfter : min(60, 5 * $attempt);
            fwrite(STDERR, "HTTP 429 rate limit; sleeping {$wait}s\n");
            sleep($wait);
            continue;
        }

        if ($status === 0 || $status >= 500) {
            $wait = min(30, 2 * $attempt);
            fwrite(STDERR, "HTTP $status; retrying in {$wait}s\n");
            sleep($wait);
            continue;
        }

        fwrite(STDERR, "HTTP $status for $url\n");
        return null;
    }

    fwrite(STDERR, "Giving up on $url\n");
    return null;
}

/**
 * Return the first page (up to 500) of a category's members.
 *
 * @return string[]
 */
function categoryMembers(string $category, string $type, bool $refresh, string $cacheDir, string $userAgent, float &$lastRequestAt, int $delayMs): array
{
    $params = [
        'list' => 'categorymembers',
        'cmtitle' => 'Category:' . $category,
        'cmtype' => $type,
        'cmlimit' => '500',
    ];
    if ($type === 'page') {
        $params['cmnamespace'] = '0';
    }

    $data = wikiApi($params, $refresh, $cacheDir, 'members', $userAgent, $lastRequestAt, $delayMs);
    if ($data === null) {
        return [];
    }

    $members = [];
    foreach ($data['query']['categorymembers'] ?? [] as $member) {
        $title = (string) ($member['title'] ?? '');
        if ($title === '') {
            continue;
        }
        if ($type === 'subcat') {
            $title = preg_replace('/^Category:/', '', $title) ?? $title;
        }
        $members[] = $title;
    }

    return $members;
}

$badTitle = '/^(List of|Lists of|Outline of|Index of|Timeline of|Glossary of|Bibliography of|Category:|Portal:|Template:|Wikipedia:|Help:|File:|Draft:|Module:|Talk:|User:|Book:)|\b(Lectureship|Oration|Colloquium|Symposium|Newsletter|Bulletin)\b/i';

/** @param array<string,array<string,array<string,mixed>>> $topics */
function subcategoryLabel(array $topics, string $category, string $subcategory): string
{
    return (string) ($topics[$category]['subcategories'][$subcategory]['label'] ?? $subcategory);
}

// ---------------------------------------------------------------------------
// 1. Build the title plan.
// ---------------------------------------------------------------------------

$plan = [];
$seenTitles = [];
$planCounts = [];
$curatedCount = 0;

/**
 * Add a title to the plan if it is new, valid and we still have room.
 *
 * @param array<int,array<string,string>> $plan
 * @param array<string,bool> $seenTitles
 * @param array<string,int> $planCounts
 */
function addPlannedTitle(array &$plan, array &$seenTitles, array &$planCounts, string $title, string $category, string $subcategory, string $label, int $planCap, string $badTitle): bool
{
    $title = trim($title);
    if ($title === '' || count($plan) >= $planCap) {
        return false;
    }
    if (preg_match($badTitle, $title) || str_contains(strtolower($title), '(disambiguation)')) {
        return false;
    }
    $key = strtolower($title);
    if (isset($seenTitles[$key])) {
        return false;
    }
    $seenTitles[$key] = true;
    $planCounts[$category . '/' . $subcategory] = ($planCounts[$category . '/' . $subcategory] ?? 0) + 1;
    $plan[] = [
        'title' => $title,
        'category' => $category,
        'subcategory' => $subcategory,
        'subcategory_label' => $label,
    ];

    return true;
}

// Curated topics always come first: they are hand-picked and high quality.
foreach ($topics as $categorySlug => $category) {
    foreach ($category['subcategories'] as $subcategorySlug => $subcategory) {
        if ($only !== null && $only !== $categorySlug . '/' . $subcategorySlug) {
            continue;
        }
        foreach ($subcategory['topics'] as $title) {
            if (addPlannedTitle($plan, $seenTitles, $planCounts, (string) $title, $categorySlug, $subcategorySlug, (string) $subcategory['label'], $planCap, $badTitle)) {
                $curatedCount++;
            }
        }
    }
}

echo 'Curated titles: ' . $curatedCount . "\n";

$planFromCache = false;
if (!$refresh && $only === null && is_file($planFile)) {
    $cachedPlan = json_decode((string) file_get_contents($planFile), true);
    if (is_array($cachedPlan) && ($cachedPlan['plan'] ?? []) !== [] && (int) ($cachedPlan['quota'] ?? 0) === $quota && (int) ($cachedPlan['plan_cap'] ?? 0) === $planCap) {
        $plan = $cachedPlan['plan'];
        $planCounts = $cachedPlan['counts'] ?? [];
        $seenTitles = [];
        foreach ($plan as $entry) {
            $seenTitles[strtolower($entry['title'])] = true;
        }
        $curatedCount = (int) ($cachedPlan['curated'] ?? $curatedCount);
        $planFromCache = true;
        echo 'Loaded plan from cache: ' . count($plan) . " titles\n";
    }
}

if (!$planFromCache) {
    foreach ($topics as $categorySlug => $category) {
        foreach ($category['subcategories'] as $subcategorySlug => $subcategory) {
            if ($only !== null && $only !== $categorySlug . '/' . $subcategorySlug) {
                continue;
            }

            $key = $categorySlug . '/' . $subcategorySlug;
            $label = (string) $subcategory['label'];
            $roots = $sources[$categorySlug][$subcategorySlug] ?? [];
            $full = static function () use (&$planCounts, &$plan, $key, $quota, $planCap): bool {
                return ($planCounts[$key] ?? 0) >= $quota || count($plan) >= $planCap;
            };

            // Prefer subcategory members: they are usually the core topics,
            // while a root category's direct members are often awards or institutes.
            foreach ($roots as $rootCategory) {
                if ($full()) {
                    break;
                }
                echo "Plan $key ← Category:$rootCategory\n";

                $subcategories = categoryMembers($rootCategory, 'subcat', $refresh, $apiCacheDir, $userAgent, $lastRequestAt, $delayMs);
                foreach (array_slice($subcategories, 0, 12) as $childCategory) {
                    if ($full()) {
                        break;
                    }
                    foreach (categoryMembers($childCategory, 'page', $refresh, $apiCacheDir, $userAgent, $lastRequestAt, $delayMs) as $title) {
                        addPlannedTitle($plan, $seenTitles, $planCounts, $title, $categorySlug, $subcategorySlug, $label, $planCap, $badTitle);
                        if ($full()) {
                            break;
                        }
                    }
                }

                if ($full()) {
                    break;
                }

                // Fall back to direct members when the subcategories are sparse.
                foreach (categoryMembers($rootCategory, 'page', $refresh, $apiCacheDir, $userAgent, $lastRequestAt, $delayMs) as $title) {
                    addPlannedTitle($plan, $seenTitles, $planCounts, $title, $categorySlug, $subcategorySlug, $label, $planCap, $badTitle);
                    if ($full()) {
                        break;
                    }
                }
            }

            // Deeper traversal for subcategories that are still short.
            if (!$full()) {
                foreach ($roots as $rootCategory) {
                    if ($full()) {
                        break;
                    }
                    $subcategories = categoryMembers($rootCategory, 'subcat', $refresh, $apiCacheDir, $userAgent, $lastRequestAt, $delayMs);
                    foreach (array_slice($subcategories, 0, 8) as $childCategory) {
                        if ($full()) {
                            break;
                        }
                        $grandchildren = categoryMembers($childCategory, 'subcat', $refresh, $apiCacheDir, $userAgent, $lastRequestAt, $delayMs);
                        foreach (array_slice($grandchildren, 0, 6) as $grandchild) {
                            if ($full()) {
                                break;
                            }
                            foreach (categoryMembers($grandchild, 'page', $refresh, $apiCacheDir, $userAgent, $lastRequestAt, $delayMs) as $title) {
                                addPlannedTitle($plan, $seenTitles, $planCounts, $title, $categorySlug, $subcategorySlug, $label, $planCap, $badTitle);
                                if ($full()) {
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    file_put_contents($planFile, json_encode([
        'generated_at' => gmdate('c'),
        'quota' => $quota,
        'plan_cap' => $planCap,
        'curated' => $curatedCount,
        'counts' => $planCounts,
        'plan' => $plan,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
}

echo 'Planned titles: ' . count($plan) . "\n";
foreach ($planCounts as $key => $count) {
    echo '  ' . str_pad($key, 32) . $count . "\n";
}

if ($planOnly) {
    echo "Plan written to content/articles/cache/plan.json\n";
    exit(0);
}

if ($plan === []) {
    fwrite(STDERR, "No titles planned. Check content/articles/sources.php and your network connection.\n");
    exit(1);
}

// ---------------------------------------------------------------------------
// 2. Fetch extracts in batches and build article records.
// ---------------------------------------------------------------------------

$advanced = [
    'quantum', 'relativity', 'thermodynamics', 'entropy', 'nuclear', 'cryptography',
    'microeconomics', 'macroeconomics', 'monetary', 'molecular', 'semiconductor',
    'electromagnetism', 'plate tectonics', 'regression', 'combinatorics', 'reinforcement',
];

$stopWords = array_flip([
    'the', 'of', 'and', 'in', 'a', 'an', 'to', 'for', 'on', 'with', 'by', 'from', 'as',
    'at', 'is', 'are', 'was', 'were', 'be', 'this', 'that', 'its', 'it', 'or', 'which',
]);

$batches = array_chunk($plan, 20);
$valid = [];
$seenSlugs = [];
$seenPageIds = [];
$skipped = 0;
$missing = [];
$retrievedAt = gmdate('Y-m-d');

foreach ($batches as $index => $batch) {
    $titles = array_column($batch, 'title');

    $data = wikiApi([
        'redirects' => '1',
        'prop' => 'extracts|pageprops',
        'explaintext' => '1',
        'exintro' => '1',
        'exchars' => '4500',
        'exlimit' => 'max',
        'titles' => implode('|', $titles),
    ], $refresh, $extractCacheDir, 'extract', $userAgent, $lastRequestAt, $delayMs);

    if ($data === null) {
        fwrite(STDERR, 'Batch ' . ($index + 1) . '/' . count($batches) . " failed. Re-run to resume from cache.\n");
        exit(1);
    }

    $query = $data['query'] ?? [];
    $pages = $query['pages'] ?? [];

    $aliases = [];
    foreach ($query['normalized'] ?? [] as $normalized) {
        $aliases[$normalized['from']] = $normalized['to'];
    }
    foreach ($query['redirects'] ?? [] as $redirect) {
        $aliases[$redirect['from']] = $redirect['to'];
    }
    $byTitle = [];
    foreach ($pages as $page) {
        $byTitle[$page['title']] = $page;
    }

    foreach ($batch as $item) {
        $title = $item['title'];
        $resolved = $aliases[$title] ?? $title;
        for ($hop = 0; $hop < 3 && isset($aliases[$resolved]); $hop++) {
            $resolved = $aliases[$resolved];
        }
        $page = $byTitle[$resolved] ?? $byTitle[$title] ?? null;

        if (!$page || !empty($page['missing']) || !empty($page['pageprops']['disambiguation']) || empty($page['extract'])) {
            $missing[] = $title;
            $skipped++;
            continue;
        }

        $text = cleanUtf8(trim(str_replace("\r\n", "\n", (string) $page['extract'])));
        $paragraphs = preg_split('/\n{2,}/', $text) ?: [];
        $paragraphs = array_values(array_filter(array_map('trim', $paragraphs), static fn ($paragraph) => $paragraph !== ''));
        $content = implode("\n\n", $paragraphs);

        if (strlen($content) < $minChars) {
            $missing[] = $title;
            $skipped++;
            continue;
        }

        if (strlen($content) > 9000) {
            $content = substr($content, 0, 9000);
            $content = substr($content, 0, (int) strrpos($content, ' ')) . '…';
        }

        $first = $paragraphs[0] ?? '';
        $summary = $first;
        if (preg_match('/^(.+?[.!?])(\s|$)/u', $first, $matches) && strlen($matches[1]) >= 40) {
            $summary = $matches[1];
        }
        if (strlen($summary) > 320) {
            $summary = rtrim(substr($summary, 0, 317)) . '…';
        }

        $summary = cleanUtf8($summary);
        $plain = preg_replace('/\s+/', ' ', $content) ?? '';
        $wordCount = str_word_count($plain);
        $readingTime = max(1, (int) ceil($wordCount / 220));

        $lowerTitle = strtolower($title);
        $difficulty = 'beginner';
        foreach ($advanced as $needle) {
            if (str_contains($lowerTitle, $needle)) {
                $difficulty = 'advanced';
                break;
            }
        }
        if ($difficulty !== 'advanced') {
            $difficulty = in_array($item['category'], ['science', 'mathematics', 'engineering', 'economics'], true)
                ? 'intermediate'
                : 'beginner';
        }

        $tagWords = [];
        foreach (preg_split('/[^a-z0-9]+/', $lowerTitle) ?: [] as $word) {
            if ($word === '' || isset($stopWords[$word]) || strlen($word) < 3) {
                continue;
            }
            $tagWords[$word] = true;
        }

        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $page['title'])) ?? '', '-');
        $pageId = isset($page['pageid']) ? (int) $page['pageid'] : null;

        if ($pageId !== null && isset($seenPageIds[$pageId])) {
            continue;
        }
        if ($slug === '') {
            $slug = 'article-' . ($pageId ?? substr(sha1($title), 0, 8));
        }
        if (isset($seenSlugs[$slug])) {
            $slug .= '-' . ($pageId ?? substr(sha1($title), 0, 6));
        }
        if (isset($seenSlugs[$slug])) {
            continue;
        }

        $seenSlugs[$slug] = true;
        if ($pageId !== null) {
            $seenPageIds[$pageId] = true;
        }

        $valid[] = [
            'title' => cleanUtf8((string) $page['title']),
            'slug' => $slug,
            'category' => $item['category'],
            'subcategory' => $item['subcategory'],
            'subcategory_label' => $item['subcategory_label'],
            'difficulty' => $difficulty,
            'summary' => $summary,
            'content' => $content,
            'word_count' => $wordCount,
            'reading_time' => $readingTime,
            'tags' => array_slice(array_keys($tagWords), 0, 4),
            'source_name' => 'Wikipedia',
            'source_url' => 'https://en.wikipedia.org/wiki/' . str_replace('%2F', '/', rawurlencode(str_replace(' ', '_', (string) $page['title']))),
            'source_page_id' => $pageId,
            'license' => 'CC BY-SA 4.0',
            'retrieved_at' => $retrievedAt,
            'language' => 'en',
        ];
    }

    if (($index + 1) % 25 === 0 || $index + 1 === count($batches)) {
        echo 'Extracts: ' . ($index + 1) . '/' . count($batches) . ' batches, ' . count($valid) . " valid articles\n";
    }
}

echo 'Valid articles before balancing: ' . count($valid) . ' (skipped ' . $skipped . ")\n";

// ---------------------------------------------------------------------------
// 3. Select a balanced catalogue across subcategories and write shards.
// ---------------------------------------------------------------------------

$bySubcategory = [];
foreach ($valid as $record) {
    $key = $record['category'] . '/' . $record['subcategory'];
    $bySubcategory[$key][] = $record;
}

$keys = array_keys($bySubcategory);
sort($keys);
$positions = array_fill_keys($keys, 0);
$selected = [];

while (count($selected) < $target) {
    $added = false;
    foreach ($keys as $key) {
        if ($positions[$key] < count($bySubcategory[$key])) {
            $selected[] = $bySubcategory[$key][$positions[$key]++];
            $added = true;
            if (count($selected) >= $target) {
                break;
            }
        }
    }
    if (!$added) {
        break;
    }
}

// Sort by the curated category/subcategory order, then title.
$categoryOrder = array_flip(array_keys($topics));
$subcategoryOrder = [];
foreach ($topics as $categorySlug => $category) {
    $subcategoryOrder[$categorySlug] = array_flip(array_keys($category['subcategories']));
}
usort($selected, static function (array $a, array $b) use ($categoryOrder, $subcategoryOrder): int {
    return [
        $categoryOrder[$a['category']] ?? 999,
        $subcategoryOrder[$a['category']][$a['subcategory']] ?? 999,
        $a['title'],
    ] <=> [
        $categoryOrder[$b['category']] ?? 999,
        $subcategoryOrder[$b['category']][$b['subcategory']] ?? 999,
        $b['title'],
    ];
});

foreach (glob($dataDir . '/part-*.jsonl') ?: [] as $oldShard) {
    @unlink($oldShard);
}

$shards = [];
foreach (array_chunk($selected, $shardSize) as $index => $chunk) {
    $file = 'part-' . str_pad((string) $index, 4, '0', STR_PAD_LEFT) . '.jsonl';
    $handle = fopen($dataDir . '/' . $file, 'wb');
    if ($handle === false) {
        fwrite(STDERR, "Cannot write $file\n");
        exit(1);
    }
    $written = 0;
    foreach ($chunk as $record) {
        $line = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($line === false) {
            fwrite(STDERR, 'Skipping unencodable record: ' . ($record['title'] ?? 'unknown') . "\n");
            continue;
        }
        fwrite($handle, $line . "\n");
        $written++;
    }
    fclose($handle);
    $shards[] = ['file' => $file, 'count' => $written];
}

$categoryCounts = [];
$subcategoryCounts = [];
foreach ($selected as $record) {
    $categoryCounts[$record['category']] = ($categoryCounts[$record['category']] ?? 0) + 1;
    $subcategoryCounts[$record['category'] . '/' . $record['subcategory']] = ($subcategoryCounts[$record['category'] . '/' . $record['subcategory']] ?? 0) + 1;
}
ksort($categoryCounts);
ksort($subcategoryCounts);

$manifest = [
    'generated_at' => gmdate('c'),
    'source' => 'Wikipedia',
    'source_url' => 'https://en.wikipedia.org/',
    'license' => 'CC BY-SA 4.0',
    'language' => 'en',
    'count' => array_sum(array_column($shards, 'count')),
    'planned' => count($plan),
    'skipped' => $skipped,
    'shard_size' => $shardSize,
    'shards' => $shards,
    'categories' => $categoryCounts,
    'subcategories' => $subcategoryCounts,
];

file_put_contents(
    $dataDir . '/manifest.json',
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
);

echo "\nWrote " . count($selected) . ' articles to ' . count($shards) . " shards.\n";
foreach ($categoryCounts as $category => $count) {
    echo '  ' . str_pad($category, 16) . $count . "\n";
}

if (count($selected) < $target) {
    fwrite(STDERR, "\nWarning: only " . count($selected) . " valid articles were available for a target of $target.\n");
    fwrite(STDERR, "Add more categories to content/articles/sources.php or raise --quota, then re-run.\n");
    exit(1);
}

if ($missing !== []) {
    file_put_contents(
        $cacheDir . '/skipped-titles.json',
        json_encode($missing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
    );
    echo 'Skipped title list: content/articles/cache/skipped-titles.json (' . count($missing) . ")\n";
}

echo "Done.\n";
