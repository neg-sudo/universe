<?php
/**
 * Fetches KnowledgeWorld article content from Wikipedia.
 *
 * Usage: php scripts/fetch-wikipedia.php [--refresh]
 *
 * Output: content/articles/data.json
 * Licence: Wikipedia text is available under CC BY-SA 4.0. Every record keeps
 * its source URL, licence and retrieval date so the UI can attribute it.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$refresh = in_array('--refresh', $argv, true);
$topics = require $root . '/content/articles/topics.php';
$cacheDir = $root . '/content/articles/cache';

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0775, true);
}

$planned = [];
foreach ($topics as $categorySlug => $category) {
    foreach ($category['subcategories'] as $subSlug => $sub) {
        foreach ($sub['topics'] as $title) {
            $planned[] = [
                'title' => $title,
                'category' => $categorySlug,
                'subcategory' => $subSlug,
                'subcategory_label' => $sub['label'],
            ];
        }
    }
}

echo 'Planned topics: ' . count($planned) . "\n";

$advanced = [
    'quantum', 'relativity', 'thermodynamics', 'entropy', 'nuclear', 'cryptography',
    'microeconomics', 'macroeconomics', 'monetary', 'molecular', 'semiconductor',
    'electromagnetism', 'plate tectonics', 'regression', 'combinatorics', 'reinforcement',
];

$stopWords = array_flip([
    'the', 'of', 'and', 'in', 'a', 'an', 'to', 'for', 'on', 'with', 'by', 'from', 'as',
    'at', 'is', 'are', 'was', 'were', 'be', 'this', 'that', 'its', 'it', 'or', 'which',
]);

$batches = array_chunk($planned, 20);
$records = [];
$missing = [];

foreach ($batches as $index => $batch) {
    $titles = array_column($batch, 'title');
    $cacheFile = $cacheDir . '/batch-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . '.json';

    if (!$refresh && is_file($cacheFile)) {
        $raw = file_get_contents($cacheFile);
        echo "Batch " . ($index + 1) . '/' . count($batches) . " (cached)\n";
    } else {
        $params = http_build_query([
            'action' => 'query',
            'format' => 'json',
            'formatversion' => '2',
            'redirects' => '1',
            'prop' => 'extracts|pageprops',
            'explaintext' => '1',
            'exintro' => '1',
            'exchars' => '4500',
            'exlimit' => 'max',
            'titles' => implode('|', $titles),
        ]);
        $url = 'https://en.wikipedia.org/w/api.php?' . $params;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_USERAGENT => 'UniVerseKnowledgeBot/1.0 (educational open-knowledge project; contact admin@universe.local)',
            CURLOPT_ENCODING => '',
        ]);
        $raw = false;
        $status = 0;
        $error = '';
        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $raw = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            if ($raw !== false && $status === 200) {
                break;
            }
            fwrite(STDERR, "Batch " . ($index + 1) . " attempt $attempt failed (HTTP $status): $error\n");
            usleep(1500000 * $attempt);
        }

        if ($raw === false || $status !== 200) {
            fwrite(STDERR, "Batch " . ($index + 1) . " failed permanently. Re-run the command to resume from cache.\n");
            exit(1);
        }
        file_put_contents($cacheFile, $raw);
        echo "Batch " . ($index + 1) . '/' . count($batches) . " fetched\n";
        usleep(300000);
    }

    $data = json_decode($raw, true);
    $query = $data['query'] ?? [];
    $pages = $query['pages'] ?? [];

    $aliases = [];
    foreach ($query['normalized'] ?? [] as $n) {
        $aliases[$n['from']] = $n['to'];
    }
    foreach ($query['redirects'] ?? [] as $r) {
        $aliases[$r['from']] = $r['to'];
    }
    $byTitle = [];
    foreach ($pages as $page) {
        $byTitle[$page['title']] = $page;
    }

    foreach ($batch as $item) {
        $title = $item['title'];
        $resolved = $aliases[$title] ?? $title;
        // Follow up to three redirect hops.
        for ($hop = 0; $hop < 3 && isset($aliases[$resolved]); $hop++) {
            $resolved = $aliases[$resolved];
        }
        $page = $byTitle[$resolved] ?? $byTitle[$title] ?? null;

        if (!$page || !empty($page['missing']) || !empty($page['pageprops']['disambiguation']) || empty($page['extract'])) {
            $missing[] = $title;
            continue;
        }

        $text = trim(str_replace("\r\n", "\n", $page['extract']));
        $paragraphs = preg_split('/\n{2,}/', $text) ?: [];
        $paragraphs = array_values(array_filter(array_map('trim', $paragraphs), static fn ($p) => $p !== ''));
        $content = implode("\n\n", $paragraphs);
        if (strlen($content) > 9000) {
            $content = substr($content, 0, 9000);
            $content = substr($content, 0, (int) strrpos($content, ' ')) . '…';
        }

        $first = $paragraphs[0] ?? '';
        $summary = $first;
        if (preg_match('/^(.+?[.!?])(\s|$)/u', $first, $m) && strlen($m[1]) >= 40) {
            $summary = $m[1];
        }
        if (strlen($summary) > 320) {
            $summary = rtrim(substr($summary, 0, 317)) . '…';
        }

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
        $tags = array_slice(array_keys($tagWords), 0, 4);

        $records[] = [
            'title' => $page['title'],
            'slug' => trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($page['title'])) ?? '', '-'),
            'category' => $item['category'],
            'subcategory' => $item['subcategory'],
            'subcategory_label' => $item['subcategory_label'],
            'difficulty' => $difficulty,
            'summary' => $summary,
            'content' => $content,
            'word_count' => $wordCount,
            'reading_time' => $readingTime,
            'tags' => $tags,
            'source_name' => 'Wikipedia',
            'source_url' => 'https://en.wikipedia.org/wiki/' . str_replace('%2F', '/', rawurlencode(str_replace(' ', '_', $page['title']))),
            'source_page_id' => $page['pageid'] ?? null,
            'license' => 'CC BY-SA 4.0',
            'retrieved_at' => gmdate('Y-m-d'),
            'language' => 'en',
        ];
    }
}

// De-duplicate by slug (a redirect may map two planned titles to one page).
$unique = [];
foreach ($records as $record) {
    $unique[$record['slug']] = $record;
}
$records = array_values($unique);
usort($records, static fn ($a, $b) => [$a['category'], $a['subcategory'], $a['title']] <=> [$b['category'], $b['subcategory'], $b['title']]);

file_put_contents(
    $root . '/content/articles/data.json',
    json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
);

$byCategory = [];
foreach ($records as $record) {
    $byCategory[$record['category']] = ($byCategory[$record['category']] ?? 0) + 1;
}

echo "\nFetched " . count($records) . " articles.\n";
foreach ($byCategory as $category => $count) {
    echo "  $category: $count\n";
}
if ($missing !== []) {
    echo "\nMissing or skipped (" . count($missing) . "):\n - " . implode("\n - ", $missing) . "\n";
}
