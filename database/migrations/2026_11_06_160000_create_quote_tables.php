<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_carts', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('locale', 2);
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('quote_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('free_text')->nullable();
            $table->string('thickness')->nullable();
            $table->string('dimensions')->nullable();
            $table->decimal('quantity', 12, 3)->nullable();
            $table->string('unit')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->nullable()->unique();
            $table->uuid('idempotency_key')->unique();
            $table->string('name');
            $table->string('phone', 32);
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('city');
            $table->string('fulfillment');
            $table->date('needed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('privacy_accepted_at');
            $table->string('locale', 2);
            $table->string('status')->default('new')->index();
            $table->string('notification_status')->default('pending')->index();
            $table->unsignedTinyInteger('notification_attempts')->default(0);
            $table->timestamp('notified_at')->nullable();
            $table->text('last_notification_error')->nullable();
            $table->json('source')->nullable();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('quote_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_label')->nullable();
            $table->string('product_url')->nullable();
            $table->boolean('is_custom')->default(false);
            $table->string('thickness')->nullable();
            $table->string('dimensions')->nullable();
            $table->decimal('quantity', 12, 3)->nullable();
            $table->string('unit')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('quote_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedInteger('size_bytes');
            $table->string('sha256', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['quote_attachments', 'quote_request_items', 'quote_requests', 'quote_cart_items', 'quote_carts'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
