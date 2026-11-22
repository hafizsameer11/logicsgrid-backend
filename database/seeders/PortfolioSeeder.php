<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Imports the portfolio projects (database/seeders/data/portfolio.json) with their screenshots
 * (database/seeders/data/portfolio/<slug>/*.jpg). Images are copied to the public disk under
 * case-studies/<slug>/. Safe to re-run: projects are matched by slug and updated.
 *
 * Run: php artisan db:seed --class=PortfolioSeeder --force
 */
class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $projects = json_decode(
            File::get(database_path('seeders/data/portfolio.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $disk = Storage::disk('public');

        foreach ($projects as $i => $p) {
            $copy = function (string $file) use ($disk, $p): string {
                $target = "case-studies/{$p['slug']}/{$file}";
                $disk->put($target, File::get(database_path("seeders/data/portfolio/{$p['slug']}/{$file}")));

                return $target;
            };

            $gallery = array_map($copy, $p['gallery']);

            CaseStudy::query()->updateOrCreate(['slug' => $p['slug']], [
                'name' => $p['name'],
                'client_type' => $p['clientType'],
                'category' => $p['category'],
                'platform' => $p['platform'],
                'summary' => $p['summary'],
                'challenge' => $p['challenge'],
                'solution' => $p['solution'],
                'features' => $p['features'],
                'scope' => $p['scope'],
                'stack' => $p['stack'],
                'services' => $p['services'],
                'project_url' => $p['projectUrl'],
                'cover_image' => $copy($p['cover']),
                'gallery' => $gallery,
                'og_image' => $copy($p['cover']),
                'sort_order' => $i,
                'is_published' => true,
                'seo_title' => "{$p['name']} case study",
                'seo_description' => $p['summary'],
            ]);
        }

        $this->command?->info('Imported '.count($projects).' portfolio projects.');
    }
}
