<?php

namespace App\Modules\Utilities;

/**
 * Loads the declarative tool catalog from content/tools/*.php.
 * The catalog is the single source of truth for routing, search and the UI.
 */
final class ToolRegistry
{
    /** @var array<string, array>|null */
    private static ?array $tools = null;

    /** @var array<string, array>|null */
    private static ?array $categories = null;

    public static function all(): array
    {
        if (self::$tools !== null) {
            return self::$tools;
        }

        $tools = [];
        foreach (glob(base_path('content/tools/*.php')) ?: [] as $file) {
            if (str_ends_with($file, 'categories.php')) {
                continue;
            }
            $definitions = require $file;
            if (! is_array($definitions)) {
                continue;
            }
            foreach ($definitions as $definition) {
                if (isset($definition['slug'])) {
                    $tools[$definition['slug']] = $definition;
                }
            }
        }

        ksort($tools);

        return self::$tools = $tools;
    }

    public static function find(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    public static function categories(): array
    {
        if (self::$categories !== null) {
            return self::$categories;
        }

        $categories = require base_path('content/tools/categories.php');
        uasort($categories, static fn (array $a, array $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        return self::$categories = $categories;
    }

    public static function activeCategories(): array
    {
        return array_filter(self::categories(), static fn (array $category) => empty($category['planned']));
    }

    public static function countByCategory(): array
    {
        $counts = [];
        foreach (self::all() as $tool) {
            $counts[$tool['category']] = ($counts[$tool['category']] ?? 0) + 1;
        }

        return $counts;
    }
}
