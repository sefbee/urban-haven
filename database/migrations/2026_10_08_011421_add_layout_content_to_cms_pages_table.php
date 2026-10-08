<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table): void {
            $table->json('layout_content')->nullable()->after('body');
        });

        DB::table('cms_pages')->where('slug', 'about')
            ->where(fn ($query) => $query->whereNull('template')->orWhere('template', 'default'))
            ->update(['template' => 'about']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('cms_pages')->where('slug', 'about')->where('template', 'about')->update(['template' => 'default']);

        Schema::table('cms_pages', function (Blueprint $table): void {
            $table->dropColumn('layout_content');
        });
    }
};
