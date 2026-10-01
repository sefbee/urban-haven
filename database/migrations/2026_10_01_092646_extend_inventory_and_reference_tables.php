<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_types', function (Blueprint $table) {
            $table->string('category')->default('residential')->after('label');
            $table->string('field_profile')->default('apartment')->after('category');
        });

        DB::table('property_types')->whereIn('key', ['land', 'plot'])->update(['category' => 'land', 'field_profile' => 'plot']);
        DB::table('property_types')->whereIn('key', ['commercial', 'office', 'shop'])->update(['category' => 'commercial', 'field_profile' => 'commercial']);

        Schema::table('location_areas', function (Blueprint $table) {
            $table->text('intro')->nullable()->after('city');
            $table->string('meta_description', 160)->nullable()->after('intro');
            $table->decimal('lat', 10, 7)->nullable()->after('meta_description');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->string('address')->nullable()->after('location_area_id');
            $table->string('price_mode')->default('fixed')->after('listing_type');
            $table->string('currency', 3)->default('BDT')->after('price_basis');
            $table->unsignedTinyInteger('balconies')->nullable()->after('bathrooms');
            $table->unsignedTinyInteger('parking_spaces')->nullable()->after('balconies');
            $table->foreignId('assigned_contact_id')->nullable()->after('trust_label')->constrained('users')->nullOnDelete();
            $table->unsignedInteger('display_priority')->nullable()->after('is_featured');
            $table->unsignedInteger('version')->default(1)->after('display_priority');
            $table->timestamp('reserved_at')->nullable()->after('availability');
            $table->foreignId('reserved_by')->nullable()->after('reserved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reservation_expires_at')->nullable()->after('reserved_by');

            $table->index('area_sqft');
            $table->index('price');
            $table->index('reservation_expires_at');
        });

        DB::table('properties')->whereNull('price')->update(['price_mode' => 'on_request']);

        Schema::table('projects', function (Blueprint $table) {
            $table->string('address')->nullable()->after('location_area_id');
            $table->string('property_category')->default('residential')->after('development_stage');
            $table->timestamp('last_updated_at')->nullable()->after('is_featured');
            $table->index('development_stage');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->string('original_disk')->default('public')->after('disk');
            $table->string('original_path')->nullable()->after('original_disk');
            $table->boolean('is_public')->default(true)->after('sort_order');
            $table->string('checksum', 64)->nullable()->after('size_bytes');
            $table->index(['mediable_type', 'mediable_id', 'collection', 'sort_order'], 'media_owner_collection_order_index');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('media_owner_collection_order_index');
            $table->dropColumn(['original_disk', 'original_path', 'is_public', 'checksum']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['development_stage']);
            $table->dropColumn(['address', 'property_category', 'last_updated_at']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['area_sqft']);
            $table->dropIndex(['price']);
            $table->dropIndex(['reservation_expires_at']);
            $table->dropConstrainedForeignId('assigned_contact_id');
            $table->dropConstrainedForeignId('reserved_by');
            $table->dropColumn(['address', 'price_mode', 'currency', 'balconies', 'parking_spaces', 'display_priority', 'version', 'reserved_at', 'reservation_expires_at']);
        });

        Schema::table('location_areas', function (Blueprint $table) {
            $table->dropColumn(['intro', 'meta_description', 'lat', 'lng']);
        });

        Schema::table('property_types', function (Blueprint $table) {
            $table->dropColumn(['category', 'field_profile']);
        });
    }
};
