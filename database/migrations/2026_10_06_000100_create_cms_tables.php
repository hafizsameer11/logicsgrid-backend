<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** SEO columns shared by every public content type. */
    private function seo(Blueprint $table): void
    {
        $table->string('seo_title')->nullable();
        $table->text('seo_description')->nullable();
        $table->string('seo_keywords')->nullable();
        $table->string('og_image')->nullable();
        $table->string('canonical_url')->nullable();
        $table->boolean('noindex')->default(false);
    }

    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('tagline')->nullable();
            $table->string('logo')->nullable();
            $table->string('logo_icon')->nullable();
            $table->string('favicon')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('location')->nullable();
            $table->string('hours')->nullable();
            $table->string('hours_note')->nullable();
            $table->json('stats')->nullable();
            $table->json('trust_points')->nullable();
            $table->json('social_links')->nullable();
            $table->json('header_links')->nullable();
            $table->json('footer_columns')->nullable();
            $table->json('budget_ranges')->nullable();
            $table->json('common_faqs')->nullable();
            $table->json('deployment_included')->nullable();
            $table->string('cta_title')->nullable();
            $table->text('cta_text')->nullable();
            $table->string('cta_button')->nullable();
            $table->string('default_seo_title')->nullable();
            $table->text('default_seo_description')->nullable();
            $table->string('default_og_image')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('google_site_verification')->nullable();
            $table->text('robots_txt')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('hero_eyebrow')->nullable();
            $table->string('hero_title');
            $table->text('hero_text')->nullable();
            $table->json('hero_labels')->nullable();
            $table->string('hero_image')->nullable();
            $table->json('sections')->nullable();
            $table->boolean('is_published')->default(true);
            $this->seo($table);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('short');
            $table->text('intro');
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->json('tags')->nullable();
            $table->json('audience')->nullable();
            $table->json('problems')->nullable();
            $table->json('features')->nullable();
            $table->json('deliverables')->nullable();
            $table->json('steps')->nullable();
            $table->json('stack')->nullable();
            $table->json('faqs')->nullable();
            $table->json('related_services')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $this->seo($table);
            $table->timestamps();
        });

        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('client_type')->nullable();
            $table->string('category');
            $table->string('platform')->nullable();
            $table->text('summary');
            $table->text('challenge')->nullable();
            $table->text('solution')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('features')->nullable();
            $table->json('scope')->nullable();
            $table->json('stack')->nullable();
            $table->json('results')->nullable();
            $table->text('quote')->nullable();
            $table->string('quote_author')->nullable();
            $table->string('project_url')->nullable();
            $table->json('services')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $this->seo($table);
            $table->timestamps();
        });

        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('cover_image')->nullable();
            $table->foreignId('post_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name')->nullable();
            $table->string('read_time')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $this->seo($table);
            $table->timestamps();
        });

        Schema::create('tech_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('text')->nullable();
            $table->json('core')->nullable();
            $table->json('also')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('care_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('label')->nullable();
            $table->text('for')->nullable();
            $table->json('points')->nullable();
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('contact');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('website')->nullable();
            $table->string('service')->nullable();
            $table->string('budget')->nullable();
            $table->text('details');
            $table->string('status')->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();
            $table->string('to_path');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['redirects', 'enquiries', 'care_plans', 'tech_groups', 'posts', 'post_categories', 'case_studies', 'services', 'pages', 'site_settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
