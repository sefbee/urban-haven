<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Property types become sub-types of two main types: residential and commercial.
     * Plots and anything else outside those two move under residential.
     */
    public function up(): void
    {
        DB::table('property_types')
            ->whereNotIn('category', ['residential', 'commercial'])
            ->update(['category' => 'residential']);

        Schema::table('property_types', function (Blueprint $table) {
            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('property_types', function (Blueprint $table) {
            $table->dropIndex(['category', 'is_active']);
        });

        DB::table('property_types')
            ->where('field_profile', 'plot')
            ->where('category', 'residential')
            ->update(['category' => 'land']);
    }
};
