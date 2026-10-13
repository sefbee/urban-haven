<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
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
        $hasLegacyProjectData = DB::table('projects')->whereNotNull('trust_label')->exists();
        $hasLegacyPropertyData = DB::table('properties')
            ->where(function (Builder $query): void {
                $query->whereNotNull('virtual_tour_url')
                    ->orWhereNotNull('trust_label')
                    ->orWhereNotNull('assigned_contact_id');
            })
            ->exists();

        if ($hasLegacyProjectData || $hasLegacyPropertyData) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('trust_label');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['assigned_contact_id']);
            $table->dropColumn(['virtual_tour_url', 'trust_label', 'assigned_contact_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('trust_label')->nullable();
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->string('virtual_tour_url')->nullable();
            $table->string('trust_label')->nullable();
            $table->foreignId('assigned_contact_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
