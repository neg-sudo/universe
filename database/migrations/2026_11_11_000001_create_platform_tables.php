<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 80)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_category_id')->constrained('tool_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description');
            $table->string('icon', 80)->nullable();
            $table->json('keywords')->nullable();
            $table->json('inputs');
            $table->string('version', 30)->default('1.0.0');
            $table->string('status', 20)->default('published');
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->index(['tool_category_id', 'status']);
            $table->index(['featured', 'status']);
            $table->index('usage_count');
        });

        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('article_categories')->nullOnDelete();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 80)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_category_id')->constrained('article_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('subcategory', 120)->nullable();
            $table->string('subcategory_label', 150)->nullable();
            $table->string('title', 250);
            $table->string('slug', 280)->unique();
            $table->text('summary');
            $table->longText('content');
            $table->string('difficulty', 30)->default('beginner');
            $table->string('status', 20)->default('published');
            $table->string('language', 10)->default('en');
            $table->json('tags')->nullable();
            $table->unsignedSmallInteger('reading_time')->default(1);
            $table->unsignedInteger('word_count')->default(0);
            $table->string('source_name', 120)->nullable();
            $table->text('source_url')->nullable();
            $table->string('license', 80)->nullable();
            $table->date('retrieved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['article_category_id', 'status']);
            $table->index(['status', 'published_at']);
            $table->index('difficulty');
            $table->index('subcategory');
        });

        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('tool_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'article_id']);
            $table->unique(['user_id', 'tool_id']);
        });

        // SQLite cannot add CHECK constraints after the table exists; PostgreSQL can.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE bookmarks ADD CONSTRAINT bookmarks_single_target CHECK (
                (article_id IS NOT NULL AND tool_id IS NULL) OR (article_id IS NULL AND tool_id IS NOT NULL)
            )');
        }

        Schema::create('tool_usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->string('session_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tool_id', 'created_at']);
        });

        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('term', 200);
            $table->unsignedInteger('result_count')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
        Schema::dropIfExists('tool_usage_events');
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('article_categories');
        Schema::dropIfExists('tools');
        Schema::dropIfExists('tool_categories');
    }
};
