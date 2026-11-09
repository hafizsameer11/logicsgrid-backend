<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\Enquiry;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteContentSeeder::class);
    }

    public function test_seeder_imports_all_site_content(): void
    {
        $this->assertSame(13, Page::count());
        $this->assertSame(6, Service::count());
        $this->assertSame(3, CaseStudy::count());
        $this->assertSame(3, Post::count());
    }

    public function test_content_api_endpoints_respond(): void
    {
        foreach (['site', 'services', 'case-studies', 'posts', 'tech-groups', 'care-plans', 'redirects', 'sitemap'] as $path) {
            $this->getJson("/api/{$path}")->assertOk();
        }
        foreach (Page::pluck('slug') as $slug) {
            $this->getJson("/api/pages/{$slug}")->assertOk()->assertJsonStructure(['slug', 'hero' => ['title'], 'sections', 'seo']);
        }
        foreach (Service::pluck('slug') as $slug) {
            $this->getJson("/api/services/{$slug}")->assertOk()->assertJsonPath('slug', $slug);
        }
        foreach (CaseStudy::pluck('slug') as $slug) {
            $this->getJson("/api/case-studies/{$slug}")->assertOk();
        }
        foreach (Post::pluck('slug') as $slug) {
            $this->getJson("/api/posts/{$slug}")->assertOk()->assertJsonStructure(['body']);
        }
    }

    public function test_unknown_and_unpublished_content_returns_404(): void
    {
        $this->getJson('/api/pages/does-not-exist')->assertNotFound();
        $this->getJson('/api/services/does-not-exist')->assertNotFound();

        Service::where('slug', 'mobile-app-development')->update(['is_published' => false]);
        $this->getJson('/api/services/mobile-app-development')->assertNotFound();
        $this->assertCount(5, $this->getJson('/api/services')->json());

        Post::query()->first()->update(['status' => 'draft']);
        $this->assertCount(2, $this->getJson('/api/posts')->json('posts'));

        Post::query()->where('status', 'published')->first()->update(['published_at' => now()->addWeek()]);
        $this->assertCount(1, $this->getJson('/api/posts')->json('posts'), 'Scheduled posts stay hidden until their date');
    }

    public function test_sitemap_excludes_noindex_pages(): void
    {
        Page::where('slug', 'terms')->update(['noindex' => true]);
        $paths = collect($this->getJson('/api/sitemap')->json('urls'))->pluck('path');
        $this->assertContains('/about', $paths);
        $this->assertNotContains('/terms', $paths);
        $this->assertContains('/services/saas-mvp-development', $paths);
    }

    public function test_enquiry_is_validated_and_saved(): void
    {
        $this->postJson('/api/enquiries', ['name' => 'A'])->assertUnprocessable();
        $this->postJson('/api/enquiries', [
            'type' => 'audit', 'name' => 'Test Person', 'email' => 'test@example.com',
            'budget' => 'Not sure yet', 'details' => 'Please review our booking process and website.',
        ])->assertCreated();
        $this->assertSame('new', Enquiry::first()->status);
        $this->assertSame('audit', Enquiry::first()->type);
    }

    public function test_no_retired_brand_or_prices_in_content(): void
    {
        $dump = json_encode([Page::all(), Service::all(), CaseStudy::all(), Post::all(), $this->getJson('/api/site')->json()]);
        $this->assertStringNotContainsStringIgnoringCase('hmstech', $dump);
        $this->assertDoesNotMatchRegularExpression('/\$\s?\d+\s*(\/|per)\s*(month|mo|hour)/i', $dump);
    }

    public function test_admin_requires_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->getJson('/admin/pages')->assertUnauthorized();
    }

    public function test_admin_can_create_a_blog_post_with_cover_image(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->actingAs(User::factory()->create());

        \Livewire\Livewire::test(\App\Filament\Resources\Posts\Pages\CreatePost::class)
            ->fillForm([
                'title' => 'Testing the CMS blog',
                'slug' => 'testing-the-cms-blog',
                'excerpt' => 'A short excerpt.',
                'body' => '<p>Hello from the editor.</p>',
                'status' => 'published',
                'published_at' => now()->subMinute(),
                'cover_image' => [\Illuminate\Http\UploadedFile::fake()->image('cover.jpg', 1200, 630)],
                'seo_title' => 'Custom SEO title',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::where('slug', 'testing-the-cms-blog')->firstOrFail();
        $this->assertNotNull($post->cover_image);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($post->cover_image);

        $json = $this->getJson('/api/posts/testing-the-cms-blog')->assertOk()->json();
        $this->assertSame('Custom SEO title', $json['seo']['seo_title']);
        $this->assertStringContainsString('Hello from the editor', $json['body']);
        $this->assertStringStartsWith('http', $json['coverImage']);
    }

    public function test_admin_edits_to_settings_service_and_page_reach_the_api(): void
    {
        $this->actingAs(User::factory()->create());

        \Livewire\Livewire::test(\App\Filament\Pages\ManageSiteSettings::class)
            ->fillForm(['phone' => '+92 300 0000000', 'whatsapp' => '923000000000'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('923000000000', $this->getJson('/api/site')->json('whatsapp'));
        $this->assertNotEmpty($this->getJson('/api/site')->json('stats'), 'Saving settings keeps the other fields');

        $service = Service::where('slug', 'ai-integration-automation')->firstOrFail();
        \Livewire\Livewire::test(\App\Filament\Resources\Services\Pages\EditService::class, ['record' => $service->getRouteKey()])
            ->fillForm(['short' => 'Edited summary'])
            ->call('save')
            ->assertHasNoFormErrors();
        $api = $this->getJson('/api/services/ai-integration-automation')->json();
        $this->assertSame('Edited summary', $api['short']);
        $this->assertCount(9, $api['features'], 'Saving a service keeps its repeaters');

        $page = Page::where('slug', 'services')->firstOrFail();
        $before = $this->getJson('/api/pages/services')->json('sections');
        \Livewire\Livewire::test(\App\Filament\Resources\Pages\Pages\EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['hero_title' => 'Edited hero'])
            ->call('save')
            ->assertHasNoFormErrors();
        $after = $this->getJson('/api/pages/services')->json();
        $this->assertSame('Edited hero', $after['hero']['title']);
        $this->assertSame(array_column($before, 'type'), array_column($after['sections'], 'type'), 'Saving a page keeps every section');
        $this->assertSame($before[2]['data']['items'], array_values($after['sections'][2]['data']['items']));
    }

    public function test_every_admin_screen_loads(): void
    {
        $this->actingAs(User::factory()->create());

        $urls = ['/admin', '/admin/manage-site-settings'];
        foreach (['pages', 'services', 'case-studies', 'care-plans', 'tech-groups', 'posts', 'post-categories', 'enquiries', 'redirects'] as $resource) {
            $urls[] = "/admin/{$resource}";
            if ($resource !== 'enquiries') {
                $urls[] = "/admin/{$resource}/create";
            }
        }
        foreach (Page::pluck('id') as $id) {
            $urls[] = "/admin/pages/{$id}/edit";
        }
        foreach (Service::pluck('id') as $id) {
            $urls[] = "/admin/services/{$id}/edit";
        }
        $urls[] = '/admin/case-studies/'.CaseStudy::first()->id.'/edit';
        $urls[] = '/admin/posts/'.Post::first()->id.'/edit';

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }
}
