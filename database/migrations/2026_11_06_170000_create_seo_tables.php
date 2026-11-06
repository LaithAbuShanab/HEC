<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();
            $table->string('to_path')->nullable();
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();
        });

        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2);
            $table->string('query');
            $table->unsignedInteger('results_count');
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
        Schema::dropIfExists('redirects');
    }
};
