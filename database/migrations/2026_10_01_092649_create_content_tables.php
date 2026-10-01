<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->string('template')->default('default')->after('slug');
            $table->json('pending_changes')->nullable()->after('body');
        });

        Schema::table('cms_blocks', function (Blueprint $table) {
            $table->json('draft_content')->nullable()->after('content');
            $table->boolean('is_visible')->default(true)->after('draft_content');
            $table->unsignedInteger('sort_order')->default(0)->after('is_visible');
        });

        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('post_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('excerpt', 300)->nullable();
            $table->longText('body')->nullable();
            $table->json('pending_changes')->nullable();
            $table->string('author_label')->nullable();
            $table->json('related_post_ids')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->string('group')->default('General');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(false);
            $table->timestamps();

            $table->index(['is_visible', 'group', 'sort_order']);
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->string('label');
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['location', 'is_visible', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_categories');

        Schema::table('cms_blocks', function (Blueprint $table) {
            $table->dropColumn(['draft_content', 'is_visible', 'sort_order']);
        });

        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn(['template', 'pending_changes']);
        });
    }
};
