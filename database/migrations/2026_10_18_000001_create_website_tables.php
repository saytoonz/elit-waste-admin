<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('website_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('status', ['Draft', 'Published'])->default('Draft');
            $table->boolean('is_home')->default(false);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('og_image')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('website_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_page_id')->constrained('website_pages')->cascadeOnDelete();
            $table->string('type', 50);
            $table->json('data')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['website_page_id', 'sort_order']);
        });

        Schema::create('website_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('location', 30)->default('header'); // header | footer_1 | footer_2 | footer_3
            $table->string('label');
            $table->foreignId('website_page_id')->nullable()->constrained('website_pages')->cascadeOnDelete();
            $table->string('url')->nullable();
            $table->boolean('new_tab')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['location', 'sort_order']);
        });

        Schema::create('website_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body')->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('status', ['Draft', 'Published'])->default('Draft');
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        Schema::create('website_media', function (Blueprint $table) {
            $table->id();
            $table->string('path'); // relative to public/, e.g. storage/website/2026/10/x.jpg
            $table->string('original_name')->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('website_leads', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->default('contact'); // contact | quote | bin_request | pickup
            $table->string('status', 20)->default('New');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('location')->nullable();
            $table->string('service_interest')->nullable();
            $table->string('frequency')->nullable();
            $table->text('message')->nullable();
            $table->string('source_url')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->date('follow_up_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('website_lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_lead_id')->constrained('website_leads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 20)->default('note'); // note | call | email | sms | visit | status
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('website_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_subscribers');
        Schema::dropIfExists('website_lead_activities');
        Schema::dropIfExists('website_leads');
        Schema::dropIfExists('website_media');
        Schema::dropIfExists('website_posts');
        Schema::dropIfExists('website_menu_items');
        Schema::dropIfExists('website_sections');
        Schema::dropIfExists('website_pages');
        Schema::dropIfExists('website_settings');
    }
};
