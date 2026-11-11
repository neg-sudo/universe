<?php

namespace Database\Seeders;

use App\Models\Tool;
use App\Models\ToolCategory;
use App\Modules\Utilities\ToolRegistry;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = [];

        foreach (ToolRegistry::categories() as $slug => $category) {
            $model = ToolCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $category['name'],
                    'description' => $category['description'] ?? null,
                    'icon' => $category['icon'] ?? null,
                    'sort_order' => $category['sort_order'] ?? 0,
                ],
            );
            $categoryIds[$slug] = $model->id;
        }

        foreach (ToolRegistry::all() as $definition) {
            Tool::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'tool_category_id' => $categoryIds[$definition['category']],
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'icon' => $definition['icon'] ?? null,
                    'keywords' => $definition['keywords'] ?? [],
                    'inputs' => $definition['inputs'],
                    'version' => $definition['version'] ?? '1.0.0',
                    'status' => $definition['status'] ?? 'published',
                    'featured' => (bool) ($definition['featured'] ?? false),
                ],
            );
        }
    }
}
