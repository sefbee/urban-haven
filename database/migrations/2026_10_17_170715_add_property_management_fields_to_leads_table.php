<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->string('property_type')->nullable()->after('project_id');
            $table->string('property_location')->nullable()->after('property_type');
            $table->unsignedInteger('number_of_units')->nullable()->after('property_location');
            $table->string('current_status')->nullable()->after('number_of_units');
            $table->string('service_type')->nullable()->after('current_status');
            $table->string('preferred_contact_time')->nullable()->after('preferred_contact');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn([
                'property_type',
                'property_location',
                'number_of_units',
                'current_status',
                'service_type',
                'preferred_contact_time',
            ]);
        });
    }
};
