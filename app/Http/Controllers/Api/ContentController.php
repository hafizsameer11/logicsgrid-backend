<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarePlan;
use App\Models\CaseStudy;
use App\Models\Enquiry;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\TechGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Read-only content API consumed by the public website, plus enquiry submission. */
class ContentController extends Controller
{
    private const SEO = ['seo_title', 'seo_description', 'seo_keywords', 'og_image', 'canonical_url', 'noindex'];

    /** Turns stored upload paths into absolute URLs. */
    private function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }
        $url = Storage::disk('public')->url($path);

        // The website runs on another host, so media URLs must always be absolute.
        return str_starts_with($url, 'http') ? $url : url($url);
    }

    private function seo($model): array
    {
        $seo = $model->only(self::SEO);
        $seo['og_image'] = $this->url($seo['og_image'] ?? null);

        return $seo;
    }

    public function site(): JsonResponse
    {
        $s = SiteSetting::query()->firstOrFail();

        return response()->json([
            'name' => $s->company_name,
            'tagline' => $s->tagline,
            'logo' => $this->url($s->logo),
            'logoIcon' => $this->url($s->logo_icon),
            'favicon' => $this->url($s->favicon),
            'email' => $s->email,
            'phone' => $s->phone,
            'whatsapp' => $s->whatsapp,
            'location' => $s->location,
            'hours' => $s->hours,
            'hoursNote' => $s->hours_note,
            'stats' => $s->stats ?? [],
            'trustPoints' => $s->trust_points ?? [],
            'socialLinks' => $s->social_links ?? [],
            'headerLinks' => $s->header_links ?? [],
            'footerColumns' => $s->footer_columns ?? [],
            'budgetRanges' => $s->budget_ranges ?? [],
            'commonFaqs' => $s->common_faqs ?? [],
            'deploymentIncluded' => $s->deployment_included ?? [],
            'cta' => ['title' => $s->cta_title, 'text' => $s->cta_text, 'button' => $s->cta_button],
            'seo' => [
                'title' => $s->default_seo_title,
                'description' => $s->default_seo_description,
                'ogImage' => $this->url($s->default_og_image),
                'googleAnalyticsId' => $s->google_analytics_id,
                'googleSiteVerification' => $s->google_site_verification,
            ],
            'services' => Service::query()->where('is_published', true)->orderBy('sort_order')->get(['slug', 'title', 'short', 'icon']),
        ]);
    }

    public function page(string $slug): JsonResponse
    {
        $page = Page::query()->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return response()->json([
            'slug' => $page->slug,
            'hero' => [
                'eyebrow' => $page->hero_eyebrow,
                'title' => $page->hero_title,
                'text' => $page->hero_text,
                'labels' => $page->hero_labels ?? [],
                'image' => $this->url($page->hero_image),
            ],
            'sections' => $page->sections ?? [],
            'seo' => $this->seo($page),
            'updatedAt' => $page->updated_at,
        ]);
    }

    private function servicePayload(Service $s): array
    {
        return [
            ...$s->only(['slug', 'title', 'short', 'intro', 'icon', 'tags', 'audience', 'problems', 'features', 'deliverables', 'steps', 'stack', 'faqs']),
            'image' => $this->url($s->image),
            'related' => $s->related_services ?? [],
            'seo' => $this->seo($s),
        ];
    }

    public function services(): JsonResponse
    {
        return response()->json(Service::query()->where('is_published', true)->orderBy('sort_order')->get()->map(fn ($s) => $this->servicePayload($s)));
    }

    public function service(string $slug): JsonResponse
    {
        return response()->json($this->servicePayload(Service::query()->where('slug', $slug)->where('is_published', true)->firstOrFail()));
    }

    private function caseStudyPayload(CaseStudy $c): array
    {
        return [
            ...$c->only(['slug', 'name', 'category', 'platform', 'summary', 'challenge', 'solution', 'features', 'scope', 'stack', 'results', 'quote', 'services']),
            'clientType' => $c->client_type,
            'quoteAuthor' => $c->quote_author,
            'projectUrl' => $c->project_url,
            'coverImage' => $this->url($c->cover_image),
            'gallery' => array_map(fn ($p) => $this->url($p), $c->gallery ?? []),
            'seo' => $this->seo($c),
        ];
    }

    public function caseStudies(): JsonResponse
    {
        return response()->json(CaseStudy::query()->where('is_published', true)->orderBy('sort_order')->get()->map(fn ($c) => $this->caseStudyPayload($c)));
    }

    public function caseStudy(string $slug): JsonResponse
    {
        return response()->json($this->caseStudyPayload(CaseStudy::query()->where('slug', $slug)->where('is_published', true)->firstOrFail()));
    }

    private function postPayload(Post $p, bool $withBody): array
    {
        return array_filter([
            'slug' => $p->slug,
            'title' => $p->title,
            'excerpt' => $p->excerpt,
            'body' => $withBody ? $p->body : null,
            'coverImage' => $this->url($p->cover_image),
            'category' => $p->category?->name,
            'categorySlug' => $p->category?->slug,
            'author' => $p->author_name,
            'readTime' => $p->read_time,
            'publishedAt' => $p->published_at?->toIso8601String(),
            'seo' => $this->seo($p),
        ], fn ($v) => $v !== null);
    }

    public function posts(Request $request): JsonResponse
    {
        $posts = Post::query()->live()->with('category')
            ->when($request->query('category'), fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->orderByDesc('published_at')->get();

        return response()->json([
            'posts' => $posts->map(fn ($p) => $this->postPayload($p, false)),
            'categories' => PostCategory::query()->whereHas('posts', fn ($q) => $q->live())->orderBy('name')->get(['slug', 'name']),
        ]);
    }

    public function post(string $slug): JsonResponse
    {
        $post = Post::query()->live()->with('category')->where('slug', $slug)->firstOrFail();

        return response()->json($this->postPayload($post, true));
    }

    public function techGroups(): JsonResponse
    {
        return response()->json(TechGroup::query()->orderBy('sort_order')->get(['title', 'text', 'core', 'also']));
    }

    public function carePlans(): JsonResponse
    {
        return response()->json(CarePlan::query()->orderBy('sort_order')->get(['name', 'label', 'for', 'points', 'is_highlighted']));
    }

    public function redirects(): JsonResponse
    {
        return response()->json(Redirect::query()->where('is_active', true)->get(['from_path', 'to_path', 'status_code']));
    }

    /** Every public URL with its last update, for sitemap.xml on the website. */
    public function sitemap(): JsonResponse
    {
        $urls = collect();
        Page::query()->where('is_published', true)->where('noindex', false)->get()
            ->each(fn ($p) => $urls->push(['path' => $p->slug === 'home' ? '/' : "/{$p->slug}", 'updatedAt' => $p->updated_at]));
        Service::query()->where('is_published', true)->where('noindex', false)->get()
            ->each(fn ($s) => $urls->push(['path' => "/services/{$s->slug}", 'updatedAt' => $s->updated_at]));
        CaseStudy::query()->where('is_published', true)->where('noindex', false)->get()
            ->each(fn ($c) => $urls->push(['path' => "/work/{$c->slug}", 'updatedAt' => $c->updated_at]));
        Post::query()->live()->where('noindex', false)->get()
            ->each(fn ($p) => $urls->push(['path' => "/insights/{$p->slug}", 'updatedAt' => $p->updated_at]));

        return response()->json(['urls' => $urls->values(), 'robots' => SiteSetting::query()->value('robots_txt')]);
    }

    public function storeEnquiry(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['nullable', 'in:contact,audit'],
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'string', 'max:200'],
            'service' => ['nullable', 'string', 'max:120'],
            'budget' => ['nullable', 'string', 'max:60'],
            'details' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        Enquiry::query()->create($data + ['type' => 'contact', 'status' => 'new']);

        return response()->json(['ok' => true], 201);
    }
}
