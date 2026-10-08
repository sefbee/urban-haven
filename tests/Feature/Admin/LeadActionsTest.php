<?php

namespace Tests\Feature\Admin;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Role;
use App\Models\SiteVisitRequest;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LeadActionsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_assign_lead_to_active_sales_staff(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $sales = $this->staff(Role::SALES_USER);
        $lead = $this->lead();

        $this->actingAs($owner)
            ->post(route('admin.leads.assign', $lead), ['assigned_to' => $sales->id])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame($sales->id, $lead->fresh()->assigned_to);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lead.assigned', 'subject_id' => $lead->id]);
    }

    public function test_lead_cannot_be_assigned_to_an_inactive_user(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $inactive = User::factory()->inactive()->create();
        $this->assignRole($inactive, Role::SALES_USER);
        $lead = $this->lead();

        $this->actingAs($owner)
            ->post(route('admin.leads.assign', $lead), ['assigned_to' => $inactive->id])
            ->assertSessionHasErrors('assigned_to');

        $this->assertNull($lead->fresh()->assigned_to);
    }

    public function test_owner_adds_a_note_to_a_lead(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead();

        $this->actingAs($owner)
            ->post(route('admin.leads.notes', $lead), ['body' => 'Called and left a message.'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lead_notes', ['lead_id' => $lead->id, 'body' => 'Called and left a message.']);
    }

    public function test_note_body_is_required_and_at_least_two_characters(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead();

        $this->actingAs($owner)->post(route('admin.leads.notes', $lead), ['body' => ''])->assertSessionHasErrors('body');
        $this->actingAs($owner)->post(route('admin.leads.notes', $lead), ['body' => 'x'])->assertSessionHasErrors('body');
    }

    public function test_owner_schedules_a_follow_up_explicitly(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead();

        $this->actingAs($owner)->post(route('admin.leads.follow-ups', $lead), [
            'action_type' => 'call',
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'priority' => 'high',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lead_follow_ups', ['lead_id' => $lead->id, 'action_type' => 'call', 'status' => LeadFollowUp::OPEN]);
    }

    public function test_quick_call_tomorrow_shortcut_schedules_a_call_for_next_morning(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead(['assigned_to' => $owner->id]);

        $this->actingAs($owner)->post(route('admin.leads.follow-ups', $lead), ['quick' => 'call_tomorrow'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $followUp = LeadFollowUp::query()->where('lead_id', $lead->id)->firstOrFail();
        $this->assertSame('call', $followUp->action_type);
        $this->assertSame(LeadFollowUp::OPEN, $followUp->status);
    }

    public function test_owner_can_complete_and_cancel_follow_ups(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead(['assigned_to' => $owner->id]);

        $followUp = LeadFollowUp::query()->create([
            'lead_id' => $lead->id,
            'user_id' => $owner->id,
            'action_type' => 'meeting',
            'scheduled_at' => now()->addDay(),
            'status' => LeadFollowUp::OPEN,
            'priority' => 'medium',
        ]);

        $cancel = LeadFollowUp::query()->create([
            'lead_id' => $lead->id,
            'user_id' => $owner->id,
            'action_type' => 'email',
            'scheduled_at' => now()->addDays(2),
            'status' => LeadFollowUp::OPEN,
            'priority' => 'low',
        ]);

        $this->actingAs($owner)->post(route('admin.follow-ups.complete', $followUp))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(LeadFollowUp::DONE, $followUp->fresh()->status);

        $this->actingAs($owner)->post(route('admin.follow-ups.cancel', $cancel))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $cancel->fresh()->status);
    }

    public function test_owner_updates_lead_priority_with_next_action(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead();

        $this->actingAs($owner)->post(route('admin.leads.priority', $lead), [
            'priority' => 'high',
            'next_action' => 'Send brochure',
            'next_action_at' => now()->addDays(3)->toDateString(),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('high', $lead->fresh()->priority);
        $this->assertSame('Send brochure', $lead->fresh()->next_action);
    }

    public function test_owner_confirms_a_site_visit_with_a_time(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead(['assigned_to' => $owner->id]);

        $visit = SiteVisitRequest::query()->create([
            'lead_id' => $lead->id,
            'preferred_at' => now()->addDays(3),
            'status' => SiteVisitRequest::REQUESTED,
        ]);

        $confirmedAt = now()->addDays(2)->toDateTimeString();

        $this->actingAs($owner)->post(route('admin.visits.status', $visit), [
            'status' => SiteVisitRequest::CONFIRMED,
            'confirmed_at' => $confirmedAt,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(SiteVisitRequest::CONFIRMED, $visit->fresh()->status);
    }

    public function test_confirming_a_visit_without_a_time_is_rejected(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead(['assigned_to' => $owner->id]);

        $visit = SiteVisitRequest::query()->create([
            'lead_id' => $lead->id,
            'preferred_at' => now()->addDays(3),
            'status' => SiteVisitRequest::REQUESTED,
        ]);

        $this->actingAs($owner)->post(route('admin.visits.status', $visit), [
            'status' => SiteVisitRequest::CONFIRMED,
        ])->assertSessionHasErrors('confirmed_at');
    }

    public function test_completing_a_visit_requires_an_outcome_note(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead(['assigned_to' => $owner->id]);

        $visit = SiteVisitRequest::query()->create([
            'lead_id' => $lead->id,
            'preferred_at' => now()->subDay(),
            'confirmed_at' => now()->subDay(),
            'status' => SiteVisitRequest::CONFIRMED,
        ]);

        $this->actingAs($owner)->post(route('admin.visits.status', $visit), [
            'status' => SiteVisitRequest::COMPLETED,
        ])->assertSessionHasErrors('outcome_note');

        $this->actingAs($owner)->post(route('admin.visits.status', $visit), [
            'status' => SiteVisitRequest::COMPLETED,
            'outcome_note' => 'Client loved the property.',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(SiteVisitRequest::COMPLETED, $visit->fresh()->status);
    }

    public function test_lead_index_filters_by_status_and_priority(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $highNew = $this->lead(['name' => 'High New', 'status' => 'new', 'priority' => 'high']);
        $lowContacted = $this->lead(['name' => 'Low Contacted', 'status' => 'contacted', 'priority' => 'low', 'phone' => '01822222222']);

        $this->actingAs($owner)
            ->get(route('admin.leads.index', ['status' => 'new', 'priority' => 'high']))
            ->assertOk()
            ->assertSee('High New')
            ->assertDontSee('Low Contacted');
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function lead(array $attributes = []): Lead
    {
        $phone = PhoneNumber::normalize($attributes['phone'] ?? '01711111111');

        return Lead::query()->create(array_merge([
            'type' => 'general_contact',
            'name' => 'Test Lead',
            'phone_hash' => Lead::hashPhone($phone),
            'status' => 'new',
            'priority' => 'medium',
        ], $attributes, ['phone' => $phone]));
    }
}
