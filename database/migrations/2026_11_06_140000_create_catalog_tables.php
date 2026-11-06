<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_units', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('name');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('review_status')->default('draft')->index();
            $table->json('seo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('appendix_no', 4)->nullable()->unique();
            $table->string('sku')->nullable()->unique();
            $table->string('slug')->unique();
            $table->foreignId('primary_category_id')->constrained('categories')->restrictOnDelete();

            $table->json('name');
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            $table->json('uses')->nullable();

            $table->json('material')->nullable();
            $table->string('grade')->nullable();
            $table->string('standard')->nullable();
            $table->json('origin')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();

            $table->foreignId('sale_unit_id')->nullable()->constrained('sale_units')->nullOnDelete();
            $table->string('condition')->default('unconfirmed');
            $table->string('availability')->default('needs_confirmation')->index();
            $table->timestamp('availability_updated_at')->nullable();
            $table->decimal('price', 12, 3)->nullable();
            $table->string('price_currency', 3)->default('JOD');

            $table->string('review_status')->default('draft')->index();
            $table->json('missing_fields')->nullable();
            $table->text('source_reference')->nullable();
            $table->date('source_reviewed_at')->nullable();

            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->json('seo')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->text('search_text_ar')->nullable();
            $table->text('search_text_en')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('products', function (Blueprint $table) {
                $table->fullText(['search_text_ar', 'search_text_en'], 'products_search_fulltext');
            });
        }

        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->primary(['category_id', 'product_id']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable()->unique();
            $table->json('label');
            $table->string('standard')->nullable();

            $table->decimal('thickness_mm', 8, 3)->nullable();
            $table->decimal('width_mm', 10, 2)->nullable();
            $table->decimal('length_mm', 10, 2)->nullable();
            $table->decimal('diameter_mm', 10, 2)->nullable();
            $table->string('nominal_size')->nullable();
            $table->foreignId('sale_unit_id')->nullable()->constrained('sale_units')->nullOnDelete();
            $table->string('availability')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('spec_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('name');
            $table->string('unit')->nullable();
            $table->string('type')->default('text');
            $table->boolean('is_filterable')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('product_spec_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spec_attribute_id')->constrained()->cascadeOnDelete();
            $table->json('value')->nullable();
            $table->decimal('value_numeric', 14, 4)->nullable()->index();
            $table->unique(['product_id', 'spec_attribute_id']);
            $table->timestamps();
        });

        Schema::create('product_related', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->primary(['product_id', 'related_product_id']);
        });

        Schema::create('historical_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('note');
            $table->string('source')->nullable();
            $table->timestamps();
        });

        Schema::create('search_synonyms', function (Blueprint $table) {
            $table->id();
            $table->json('terms');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'search_synonyms', 'historical_specs', 'product_related', 'product_spec_values',
            'spec_attributes', 'product_variants', 'category_product', 'products', 'categories', 'sale_units',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
