<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publication_states', function (Blueprint $table) {
            $table->text('review_note')->nullable()->after('unpublish_reason');
            $table->foreignId('submitted_by')->nullable()->after('review_note')->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            $table->index(['publishable_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('publication_states', function (Blueprint $table) {
            $table->dropIndex(['publishable_type', 'status']);
            $table->dropConstrainedForeignId('submitted_by');
            $table->dropColumn(['review_note', 'submitted_at']);
        });
    }
};
