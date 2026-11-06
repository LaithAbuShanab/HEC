<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('summary')->nullable();
            $table->json('body')->nullable();
            $table->json('materials')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('pricing_requirements')->nullable();
            $table->string('review_status')->default('draft')->index();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->json('seo')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('excerpt')->nullable();
            $table->json('body')->nullable();
            $table->string('technical_reviewer')->nullable();
            $table->date('technically_reviewed_at')->nullable();
            $table->string('review_status')->default('draft')->index();
            $table->json('seo')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('summary')->nullable();
            $table->json('body')->nullable();
            $table->string('review_status')->default('draft')->index();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->json('seo')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('work_items', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('summary')->nullable();
            $table->json('body')->nullable();
            $table->date('performed_on')->nullable();
            $table->string('review_status')->default('draft')->index();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->json('seo')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->json('question');
            $table->json('answer');
            $table->string('group')->nullable()->index();
            $table->boolean('show_on_home')->default(false);
            $table->string('review_status')->default('draft')->index();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('title');
            $table->json('body')->nullable();
            $table->string('review_status')->default('draft')->index();
            $table->json('seo')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        foreach (['guide_product' => 'guide', 'industry_product' => 'industry', 'product_service' => 'service', 'product_work_item' => 'work_item'] as $pivot => $owner) {
            Schema::create($pivot, function (Blueprint $table) use ($owner) {
                $table->foreignId("{$owner}_id")->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->unsignedSmallInteger('sort')->default(0);
                $table->primary(["{$owner}_id", 'product_id']);
            });
        }

        Schema::create('category_guide', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guide_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'guide_id']);
        });
    }

    public function down(): void
    {
        foreach (['category_guide', 'product_work_item', 'product_service', 'industry_product', 'guide_product', 'pages', 'faqs', 'work_items', 'industries', 'guides', 'services'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
