<?php

namespace Tests\Feature\Leads;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Role;
use App\Models\SiteVisitRequest;
use App\Models\User;
use App\Services\Lead\LeadExporter;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_lead_stages_are_exactly_the_seven_srs_stages(): void
    {
        $this->assertSame(['new', 'contacted', 'qualified', 'visit_scheduled', 'negotiation', 'won', 'lost'], Lead::STATUSES);
        $this->assertSame(['New', 'Contacted', 'Qualified', 'Visit Scheduled', 'Negotiation', 'Won', 'Lost'], array_values(Lead::STATUS_LABELS));

        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead();

        $this->actingAs($owner)->post(route('admin.leads.status', $lead), ['status' => 'negotiating'])->assertSessionHasErrors('status');
        $this->assertSame('new', $lead->fresh()->status);
    }

    public function test_sales_user_only_sees_their_own_leads(): void
    {
        $sales = $this->staff(Role::SALES_USER);
        $other = $this->staff(Role::SALES_USER);
        $mine = $this->lead(['name' => 'Mine Rahman', 'assigned_to' => $sales->id]);
        $theirs = $this->lead(['name' => 'Theirs Karim', 'assigned_to' => $other->id, 'phone' => '01822222222']);
        $unassigned = $this->lead(['name' => 'Nobody Hasan', 'phone' => '01933333333']);

        $this->actingAs($sales)->get(route('admin.leads.index'))
            ->assertOk()
            ->assertSee('Mine Rahman')
            ->assertDontSee('Theirs Karim')
            ->assertDontSee('Nobody Hasan');

        $this->actingAs($sales)->get(route('admin.leads.show', $mine))->assertOk();
        $this->actingAs($sales)->get(route('admin.leads.show', $theirs))->assertForbidden();
        $this->actingAs($sales)->get(route('admin.leads.show', $unassigned))->assertForbidden();
        $this->actingAs($sales)->getJson(route('admin.leads.show', $theirs))->assertForbidden();
    }

    public function test_sales_user_cannot_change_someone_elses_lead(): void
    {
        $sales = $this->staff(Role::SALES_USER);
        $theirs = $this->lead(['assigned_to' => $this->staff(Role::SALES_USER)->id]);

        $this->actingAs($sales)->post(route('admin.leads.status', $theirs), ['status' => 'contacted'])->assertForbidden();
        $this->actingAs($sales)->post(route('admin.leads.notes', $theirs), ['body' => 'Peeking'])->assertForbidden();
        $this->actingAs($sales)->post(route('admin.leads.priority', $theirs), ['priority' => 'high'])->assertForbidden();
        $this->actingAs($sales)->post(route('admin.leads.follow-ups', $theirs), ['quick' => 'call_tomorrow'])->assertForbidden();
        $this->actingAs($sales)->post(route('admin.leads.assign', $theirs), ['assigned_to' => $sales->id])->assertForbidden();

        $this->assertSame('new', $theirs->fresh()->status);
        $this->assertDatabaseCount('lead_notes', 0);
    }

    public function test_sales_user_cannot_reach_other_follow_ups_or_visits(): void
    {
        $sales = $this->staff(Role::SALES_USER);
        $other = $this->staff(Role::SALES_USER);
        $theirs = $this->lead(['assigned_to' => $other->id, 'name' => 'Other Person']);
        $followUp = LeadFollowUp::query()->create([
            'lead_id' => $theirs->id,
            'user_id' => $other->id,
            'action_type' => 'call',
            'scheduled_at' => now()->subHour(),
            'status' => LeadFollowUp::OPEN,
            'priority' => 'medium',
        ]);
        $visit = SiteVisitRequest::query()->create([
            'lead_id' => $theirs->id,
            'preferred_at' => now()->addDays(2),
            'status' => SiteVisitRequest::REQUESTED,
        ]);

        $this->actingAs($sales)->post(route('admin.follow-ups.complete', $followUp))->assertForbidden();
        $this->actingAs($sales)->post(route('admin.visits.status', $visit), ['status' => 'cancelled'])->assertForbidden();
        $this->actingAs($sales)->get(route('admin.follow-ups.index', ['view' => 'all']))->assertOk()->assertDontSee('Other Person');
        $this->actingAs($sales)->get(route('admin.visits.index'))->assertOk()->assertDontSee('Other Person');
    }

    public function test_sales_user_cannot_export_leads(): void
    {
        $sales = $this->staff(Role::SALES_USER);

        $this->actingAs($sales)->get(route('admin.leads.export'))->assertForbidden();
        $this->actingAs($sales)->get(route('admin.leads.export.download', '00000000-0000-0000-0000-000000000000.csv'))->assertForbidden();
    }

    public function test_lost_requires_a_loss_reason(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead();

        $this->actingAs($owner)->post(route('admin.leads.status', $lead), ['status' => 'lost'])->assertSessionHasErrors('loss_reason');
        $this->assertSame('new', $lead->fresh()->status);

        $reason = array_key_first(Lead::LOSS_REASONS);
        $this->actingAs($owner)->post(route('admin.leads.status', $lead), ['status' => 'lost', 'loss_reason' => $reason])->assertSessionHasNoErrors();

        $this->assertSame('lost', $lead->fresh()->status);
        $this->assertSame($reason, $lead->fresh()->loss_reason);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lead.status_updated', 'subject_id' => $lead->id]);
    }

    public function test_reopening_a_lost_lead_clears_the_loss_reason(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead(['status' => 'lost', 'loss_reason' => array_key_first(Lead::LOSS_REASONS)]);

        $this->actingAs($owner)->post(route('admin.leads.status', $lead), ['status' => 'contacted'])->assertSessionHasNoErrors();

        $this->assertNull($lead->fresh()->loss_reason);
    }

    public function test_lead_detail_shows_stage_form_with_loss_reasons_and_contact_links(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead(['name' => 'Detail Person']);

        $response = $this->actingAs($owner)->get(route('admin.leads.show', $lead))->assertOk()->assertSee('Detail Person');

        foreach (Lead::STATUS_LABELS as $label) {
            $response->assertSee($label);
        }
        foreach (Lead::LOSS_REASONS as $label) {
            $response->assertSee($label);
        }
        $response->assertSee('tel:+8801711111111', false)->assertSee('wa.me/8801711111111', false);
    }

    public function test_owner_export_neutralises_formulas_and_is_audited(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $this->lead(['name' => '=HYPERLINK("http://evil")']);

        $response = $this->actingAs($owner)->get(route('admin.leads.export'));
        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lead.exported']);
        $this->assertSame("'+880", LeadExporter::neutralise('+880'));
    }

    public function test_follow_up_queue_and_visits_pages_render_for_owner(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $lead = $this->lead(['assigned_to' => $owner->id]);

        $this->actingAs($owner)->post(route('admin.leads.follow-ups', $lead), ['quick' => 'call_tomorrow'])->assertSessionHasNoErrors();

        $this->actingAs($owner)->get(route('admin.follow-ups.index'))->assertOk();
        $this->actingAs($owner)->get(route('admin.follow-ups.index', ['view' => 'all']))->assertOk()->assertSee($lead->name);
        $this->actingAs($owner)->get(route('admin.visits.index'))->assertOk();
    }

    public function test_dashboard_only_counts_visible_leads_and_hides_them_from_editors(): void
    {
        $sales = $this->staff(Role::SALES_USER);
        $this->lead(['name' => 'Mine Dash', 'assigned_to' => $sales->id]);
        $this->lead(['name' => 'Other Dash', 'phone' => '01822222222', 'assigned_to' => $this->staff(Role::SALES_USER)->id]);

        $this->actingAs($sales)->get(route('admin.dashboard'))->assertOk()->assertSee('Mine Dash')->assertDontSee('Other Dash');

        $editor = $this->staff(Role::CONTENT_EDITOR);
        $this->actingAs($editor)->get(route('admin.dashboard'))->assertOk()->assertDontSee('Mine Dash')->assertDontSee('Recent leads');
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
        $phone = $attributes['phone'] ?? '01711111111';
        $normalized = PhoneNumber::normalize($phone);

        return Lead::query()->create(array_merge([
            'type' => 'general_contact',
            'name' => 'Rafi Ahmed',
            'phone_hash' => Lead::hashPhone($normalized),
            'status' => 'new',
            'priority' => 'medium',
            'consent_given' => true,
        ], $attributes, ['phone' => $normalized]));
    }
}
