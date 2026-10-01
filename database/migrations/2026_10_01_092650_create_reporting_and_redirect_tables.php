<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('field');
            $table->string('from_value')->nullable();
            $table->string('to_value')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['property_id', 'created_at']);
            $table->index('created_at');
        });

        Schema::create('property_metrics_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('cta_clicks')->default(0);

            $table->unique(['property_id', 'date']);
            $table->index('date');
        });

        Schema::table('redirects', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('http_code');
            $table->foreignId('created_by')->nullable()->after('reason')->constrained('users')->nullOnDelete();
            $table->unsignedInteger('hits')->default(0)->after('is_active');
            $table->timestamp('last_hit_at')->nullable()->after('hits');
        });
    }

    public function down(): void
    {
        Schema::table('redirects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['reason', 'hits', 'last_hit_at']);
        });

        Schema::dropIfExists('property_metrics_daily');
        Schema::dropIfExists('property_status_history');
    }
};
