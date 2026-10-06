<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('cms_blocks')->where('key', 'intent_blocks')->delete();

        DB::table('cms_pages')
            ->where('slug', 'contact')
            ->where(fn ($query) => $query->whereNull('template')->orWhere('template', 'default'))
            ->update(['template' => 'contact']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('cms_pages')->where('slug', 'contact')->where('template', 'contact')->update(['template' => 'default']);
    }
};
