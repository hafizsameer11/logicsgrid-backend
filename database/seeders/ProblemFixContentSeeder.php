<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Updates only the "problem / how we solve it" rows and a few uneven process steps
 * (database/seeders/data/problem-fix.json)
 * on services and pages. Nothing else on those records changes.
 *
 * Run: php artisan db:seed --class=ProblemFixContentSeeder --force
 */
class ProblemFixContentSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(File::get(database_path('seeders/data/problem-fix.json')), true, flags: JSON_THROW_ON_ERROR);

        foreach ($data['services'] as $slug => $rows) {
            Service::query()->where('slug', $slug)->update(['problems' => json_encode($rows)]);
        }

        foreach ($data['pages'] as $slug => $rows) {
            // A string means "use that service's rows".
            $rows = is_string($rows) ? $data['services'][$rows] : $rows;
            $page = Page::query()->where('slug', $slug)->first();
            if (! $page) {
                continue;
            }
            $page->sections = array_map(
                fn (array $block) => $block['type'] === 'problem_fix'
                    ? ['type' => 'problem_fix', 'data' => array_merge($block['data'], ['rows' => $rows])]
                    : $block,
                $page->sections ?? [],
            );
            $page->save();
        }

        // Balanced step text (similar length per step) for pages whose process steps were uneven.
        foreach ($data['steps'] ?? [] as $slug => $items) {
            $page = Page::query()->where('slug', $slug)->first();
            if (! $page) {
                continue;
            }
            $page->sections = array_map(
                fn (array $block) => $block['type'] === 'steps' && count($block['data']['items'] ?? []) === count($items)
                    ? ['type' => 'steps', 'data' => array_merge($block['data'], ['items' => $items])]
                    : $block,
                $page->sections ?? [],
            );
            $page->save();
        }

        $this->command?->info('Updated problem / solution rows on '.count($data['services']).' services and '.count($data['pages']).' pages.');
    }
}
