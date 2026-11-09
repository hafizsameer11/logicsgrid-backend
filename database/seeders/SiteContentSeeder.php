<?php

namespace Database\Seeders;

use App\Models\CarePlan;
use App\Models\CaseStudy;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\TechGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Imports the website's current content (exported from src/data by cms/scripts/export-site-data.mjs,
 * plus database/seeders/data/pages.json) so the CMS starts as an exact copy of the live site.
 * Safe to re-run: records are matched by slug/name and updated.
 */
class SiteContentSeeder extends Seeder
{
    private function json(string $name): array
    {
        return json_decode(file_get_contents(database_path("seeders/data/{$name}.json")), true, flags: JSON_THROW_ON_ERROR);
    }

    public function run(): void
    {
        $site = $this->json('site');
        $deployment = $this->json('deployment');
        $services = $this->json('services');

        SiteSetting::query()->updateOrCreate(['id' => 1], [
            'company_name' => $site['name'],
            'tagline' => $site['tagline'],
            'email' => $site['email'],
            'phone' => $site['phone'],
            'whatsapp' => '923290112060',
            'location' => $site['location'],
            'hours' => $site['hours'],
            'hours_note' => $site['hoursNote'],
            'stats' => $site['stats'],
            'trust_points' => ['Based in Faisalabad, Pakistan', 'UK, US & Gulf overlap hours', 'Design to deployment', 'Long-term support'],
            'social_links' => [
                ['platform' => 'linkedin', 'url' => 'https://www.linkedin.com'],
                ['platform' => 'instagram', 'url' => 'https://www.instagram.com'],
            ],
            'header_links' => [
                ['label' => 'Work', 'url' => '/work'],
                ['label' => 'For startups', 'url' => '/startups'],
                ['label' => 'For agencies', 'url' => '/agencies'],
                ['label' => 'Technologies', 'url' => '/technologies'],
                ['label' => 'About', 'url' => '/about'],
            ],
            'footer_columns' => [[
                'title' => 'Company',
                'links' => [
                    ['label' => 'About', 'url' => '/about'], ['label' => 'Work', 'url' => '/work'],
                    ['label' => 'For startups', 'url' => '/startups'], ['label' => 'For agencies', 'url' => '/agencies'],
                    ['label' => 'Technologies', 'url' => '/technologies'], ['label' => 'Care plans', 'url' => '/care-plans'],
                    ['label' => 'Free audit', 'url' => '/free-audit'], ['label' => 'Contact', 'url' => '/contact'],
                ],
            ]],
            'budget_ranges' => ['Under $1,000', '$1,000–$5,000', '$5,000–$15,000', '$15,000+', 'Not sure yet'],
            'common_faqs' => [
                ['q' => 'How do projects start?', 'a' => 'With a free 30-minute consultation. We clarify the outcome, then send a written plan and estimate within 48 hours.'],
                ['q' => 'Can you work with an existing product?', 'a' => 'Yes. We can audit, stabilise, modernise or extend an existing platform without forcing a complete rebuild.'],
                ['q' => 'Do you stay involved after launch?', 'a' => 'Yes. Every project can move onto a care plan for monitoring, updates, fixes and new features.'],
                ['q' => 'Who owns the code?', 'a' => 'You do. Code, designs, accounts and documentation are handed over to you at the end of the project.'],
                ['q' => 'How do you keep us updated?', 'a' => 'You get a weekly progress update with what was finished, what is next and any decisions we need from you.'],
                ['q' => 'Do you work with international clients?', 'a' => 'Yes. We are based in Faisalabad, Pakistan, and keep overlap hours for UK, US and Gulf clients.'],
            ],
            'deployment_included' => $deployment,
            'cta_title' => 'Have an idea or a problem to solve?',
            'cta_text' => 'Book a free 30-minute consultation. You get a written plan and estimate within 48 hours.',
            'cta_button' => 'Book a consultation',
            'default_seo_title' => $site['title'],
            'default_seo_description' => $site['description'],
            'robots_txt' => "User-agent: *\nAllow: /\n",
        ]);

        $bySlug = collect($services)->keyBy('slug');
        foreach ($services as $i => $s) {
            Service::query()->updateOrCreate(['slug' => $s['slug']], [
                'title' => $s['title'],
                'short' => $s['short'],
                'intro' => $s['intro'],
                'icon' => ['saas-mvp-development' => 'rocket', 'web-applications-websites' => 'layout-template', 'mobile-app-development' => 'smartphone', 'booking-business-management-systems' => 'calendar-check', 'ai-integration-automation' => 'bot', 'white-label-development' => 'handshake'][$s['slug']] ?? 'rocket',
                'tags' => $s['tags'],
                'audience' => $s['audience'],
                'problems' => $s['problems'],
                'features' => $s['features'],
                'deliverables' => $s['deliverables'],
                'steps' => $s['steps'],
                'stack' => $s['stack'],
                'faqs' => $s['faqs'],
                'related_services' => $s['related'],
                'sort_order' => $i,
                'is_published' => true,
                'seo_title' => $s['title'],
                'seo_description' => $s['short'],
            ]);
        }

        foreach ($this->json('case-studies') as $i => $c) {
            CaseStudy::query()->updateOrCreate(['slug' => $c['slug']], [
                'name' => $c['name'],
                'client_type' => $c['clientType'],
                'category' => $c['category'],
                'platform' => $c['platform'],
                'summary' => $c['summary'],
                'challenge' => $c['challenge'],
                'solution' => $c['solution'],
                'features' => $c['features'],
                'scope' => $c['scope'],
                'stack' => $c['stack'],
                'services' => $c['services'],
                'sort_order' => $i,
                'is_published' => true,
                'seo_title' => $c['name'],
                'seo_description' => $c['summary'],
            ]);
        }

        foreach ($this->json('tech') as $i => $g) {
            TechGroup::query()->updateOrCreate(['title' => $g['title']], ['text' => $g['text'], 'core' => $g['core'], 'also' => $g['also'], 'sort_order' => $i]);
        }

        foreach ($this->json('care-plans')['carePlans'] as $i => $p) {
            CarePlan::query()->updateOrCreate(['name' => $p['name']], [
                'label' => $i === 1 ? 'Most flexible' : 'Plan '.($i + 1),
                'for' => $p['for'],
                'points' => $p['points'],
                'is_highlighted' => $i === 1,
                'sort_order' => $i,
            ]);
        }

        foreach ($this->json('posts') as $p) {
            $category = PostCategory::query()->firstOrCreate(['slug' => Str::slug($p['category'])], ['name' => $p['category']]);
            $body = collect($p['sections'])->map(fn ($s) => '<h2>'.e($s['heading']).'</h2><p>'.e($s['body']).'</p>')->implode('');
            Post::query()->updateOrCreate(['slug' => $p['slug']], [
                'title' => $p['title'],
                'excerpt' => $p['excerpt'],
                'body' => $body,
                'post_category_id' => $category->id,
                'author_name' => 'LogicsGrid team',
                'read_time' => $p['readTime'],
                'status' => 'published',
                'published_at' => Carbon::parse($p['date']),
            ]);
        }

        foreach ($this->json('pages') as $page) {
            $sections = array_map(fn (array $block) => $this->expand($block, $bySlug->all(), $deployment), $page['sections']);
            Page::query()->updateOrCreate(['slug' => $page['slug']], [
                'name' => $page['name'],
                'hero_eyebrow' => $page['hero_eyebrow'] ?? null,
                'hero_title' => $page['hero_title'],
                'hero_text' => $page['hero_text'] ?? null,
                'hero_labels' => $page['hero_labels'] ?? [],
                'sections' => $sections,
                'is_published' => true,
                'seo_title' => $page['seo_title'] ?? null,
                'seo_description' => $page['seo_description'] ?? null,
            ]);
        }
    }

    /**
     * Copies service and deployment content into blocks that reference it, so every block is
     * self-contained and fully editable in the page builder.
     */
    private function expand(array $block, array $services, array $deployment): array
    {
        $data = $block['data'];
        if ($slug = $data['from_service'] ?? null) {
            $s = $services[$slug];
            $data = match ($block['type']) {
                'audience_list' => $data + ['items' => $s['audience']],
                'problem_fix' => $data + ['rows' => $s['problems']],
                'steps' => $data + ['items' => $s['steps']],
                'numbered_rows' => $data + ['items' => $s['features']],
                'faq' => array_merge($data, ['items' => array_merge($s['faqs'], $data['items'] ?? [])]),
                default => $data,
            };
            unset($data['from_service']);
        }
        if ($data['use_deployment'] ?? false) {
            if ($block['type'] === 'steps') {
                $data['items'] = $deployment;
            } else {
                $data['principles'] = $deployment;
            }
            unset($data['use_deployment']);
        }

        return ['type' => $block['type'], 'data' => $data];
    }
}
