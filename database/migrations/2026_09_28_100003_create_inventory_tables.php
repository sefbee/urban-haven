<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('development_stage')->default('ongoing');
            $table->string('city');
            $table->foreignId('location_area_id')->nullable()->constrained('location_areas')->nullOnDelete();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('developer_name')->nullable();
            $table->date('completion_date')->nullable();
            $table->text('handover_info')->nullable();
            $table->json('amenity_ids')->nullable();
            $table->json('highlights')->nullable();
            $table->string('trust_label')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('reference')->nullable()->unique();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_type_id')->constrained('property_types');
            $table->foreignId('location_area_id')->constrained('location_areas');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('availability')->default('available');
            $table->string('listing_type')->default('sale');
            $table->decimal('price', 16, 2)->nullable();
            $table->string('price_basis')->default('total_sale');
            $table->decimal('area_value', 12, 2)->nullable();
            $table->string('area_unit')->default('sqft');
            $table->decimal('area_sqft', 12, 2)->nullable();
            $table->unsignedInteger('bedrooms')->nullable();
            $table->unsignedInteger('bathrooms')->nullable();
            $table->integer('floor_number')->nullable();
            $table->boolean('is_furnished')->default(false);
            $table->json('amenity_ids')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('map_approximation')->nullable();
            $table->string('video_url')->nullable();
            $table->string('virtual_tour_url')->nullable();
            $table->string('trust_label')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamp('last_updated_at')->nullable();
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->timestamps();

            $table->index(['location_area_id', 'property_type_id', 'price']);
            $table->index(['availability', 'listing_type']);
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number');
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 16, 2)->nullable();
            $table->string('status')->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'unit_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('projects');
    }
};
