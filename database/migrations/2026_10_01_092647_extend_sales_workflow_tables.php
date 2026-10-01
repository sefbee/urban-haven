<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('type')->default('property_inquiry')->after('id');
            $table->boolean('consent_given')->default(false)->after('message');
            $table->string('submission_token', 64)->nullable()->unique()->after('consent_given');
            $table->boolean('is_repeat_contact')->default(false)->after('submission_token');
            $table->foreignId('repeat_of_lead_id')->nullable()->after('is_repeat_contact')->constrained('leads')->nullOnDelete();
            $table->boolean('is_unread')->default(true)->after('repeat_of_lead_id');
            $table->string('loss_reason')->nullable()->after('status');

            $table->index(['assigned_to', 'status']);
            $table->index('next_action_at');
            $table->index('created_at');
        });

        DB::table('leads')->where('source', 'visit_request')->update(['type' => 'visit_request']);
        DB::table('leads')->whereNull('property_id')->whereNull('project_id')->where('source', '!=', 'visit_request')->update(['type' => 'general_contact']);
        DB::table('leads')->where('priority', 'normal')->update(['priority' => 'medium']);
        DB::table('leads')->where('priority', 'urgent')->update(['priority' => 'high']);

        Schema::table('leads', function (Blueprint $table) {
            $table->string('priority')->default('medium')->change();
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['lead_id', 'created_at']);
        });

        Schema::table('lead_follow_ups', function (Blueprint $table) {
            $table->string('priority')->default('medium')->after('action_type');
            $table->string('status')->default('open')->after('priority');
            $table->index(['user_id', 'status', 'scheduled_at']);
        });

        DB::table('lead_follow_ups')->whereNotNull('completed_at')->update(['status' => 'done']);

        Schema::table('site_visit_requests', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('preferred_at');
            $table->foreignId('confirmed_by')->nullable()->after('confirmed_at')->constrained('users')->nullOnDelete();
            $table->text('outcome_note')->nullable()->after('notes');
            $table->index(['status', 'preferred_at']);
        });

        DB::table('site_visit_requests')->where('status', 'pending')->update(['status' => 'requested']);

        Schema::table('site_visit_requests', function (Blueprint $table) {
            $table->string('status')->default('requested')->change();
        });
    }

    public function down(): void
    {
        Schema::table('site_visit_requests', function (Blueprint $table) {
            $table->dropIndex(['status', 'preferred_at']);
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropColumn(['confirmed_at', 'outcome_note']);
            $table->string('status')->default('pending')->change();
        });

        DB::table('site_visit_requests')->where('status', 'requested')->update(['status' => 'pending']);

        Schema::table('lead_follow_ups', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'status', 'scheduled_at']);
            $table->dropColumn(['priority', 'status']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::dropIfExists('lead_activities');

        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['next_action_at']);
            $table->dropIndex(['created_at']);
            $table->dropConstrainedForeignId('repeat_of_lead_id');
            $table->dropUnique(['submission_token']);
            $table->dropColumn(['type', 'consent_given', 'submission_token', 'is_repeat_contact', 'is_unread', 'loss_reason']);
            $table->string('priority')->default('normal')->change();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }
};
