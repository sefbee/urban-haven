<?php

namespace Tests\Feature\Leads;

use App\Jobs\NotifyNewLeadJob;
use App\Models\Lead;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeadCaptureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_inquiry_creates_a_lead_and_dispatches_notification_job(): void
    {
        Queue::fake();
        $property = $this->makeProperty(['title' => 'Enquiry home'], published: true);

        $this->postJson(route('inquiries.store'), $this->payload([
            'name' => 'Rafi',
            'property_id' => $property->id,
            'utm_source' => 'google',
        ]))->assertCreated()->assertJsonPath('event.event', 'inquiry_success');

        $this->assertDatabaseHas('leads', [
            'name' => 'Rafi',
            'utm_source' => 'google',
            'property_id' => $property->id,
            'type' => 'property_inquiry',
            'status' => 'new',
            'consent_given' => true,
        ]);
        Queue::assertPushed(NotifyNewLeadJob::class);
    }

    public function test_non_json_submission_redirects_to_thank_you_page_once(): void
    {
        Queue::fake();

        $this->post(route('inquiries.store'), $this->payload([
            'name' => 'Nabila',
            'source' => 'valuation',
            'message' => 'Gulshan 1800 sqft apartment',
        ]))->assertRedirect(route('thank-you'));

        $this->assertDatabaseHas('leads', ['name' => 'Nabila', 'source' => 'valuation', 'type' => 'general_contact']);

        $this->get(route('thank-you'))->assertOk()->assertSee('inquiry_success');
        $this->get(route('thank-you'))->assertOk()->assertDontSee('inquiry_success');
    }

    public function test_consent_and_submission_token_are_required(): void
    {
        $this->postJson(route('inquiries.store'), [
            'name' => 'Rafi',
            'phone' => '01711111111',
        ])->assertUnprocessable()->assertJsonValidationErrors(['consent_given', 'submission_token']);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_honeypot_submission_is_rejected(): void
    {
        $this->postJson(route('inquiries.store'), $this->payload(['website' => 'http://spam.example']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('website');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_invalid_bangladesh_phone_is_rejected(): void
    {
        $this->postJson(route('inquiries.store'), $this->payload(['phone' => '12345']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('phone');
    }

    public function test_unpublished_property_cannot_receive_inquiries(): void
    {
        $draft = $this->makeProperty(['title' => 'Draft home']);

        $this->postJson(route('inquiries.store'), $this->payload(['property_id' => $draft->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('property_id');
    }

    public function test_resubmitting_the_same_token_does_not_create_a_second_lead(): void
    {
        Queue::fake();
        $payload = $this->payload();

        $this->postJson(route('inquiries.store'), $payload)->assertCreated();
        $this->postJson(route('inquiries.store'), $payload)->assertOk();

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_rapid_identical_submission_with_a_new_token_is_treated_as_a_duplicate(): void
    {
        Queue::fake();
        $property = $this->makeProperty([], published: true);

        $this->postJson(route('inquiries.store'), $this->payload(['property_id' => $property->id]))->assertCreated();
        $this->postJson(route('inquiries.store'), $this->payload(['property_id' => $property->id]))->assertOk();

        $this->assertDatabaseCount('leads', 1);
    }

    public function test_repeat_inquiry_creates_a_new_flagged_lead_linked_to_the_earlier_one(): void
    {
        Queue::fake();
        $first = $this->makeProperty(['title' => 'First home'], published: true);
        $second = $this->makeProperty(['title' => 'Second home'], published: true);

        $this->postJson(route('inquiries.store'), $this->payload(['property_id' => $first->id]))->assertCreated();
        $this->travel(2)->minutes();
        $this->postJson(route('inquiries.store'), $this->payload([
            'phone' => '+8801711111111',
            'property_id' => $second->id,
        ]))->assertCreated();

        $leads = Lead::query()->orderBy('id')->get();
        $this->assertCount(2, $leads);
        $this->assertFalse($leads[0]->is_repeat_contact);
        $this->assertTrue($leads[1]->is_repeat_contact);
        $this->assertSame($leads[0]->id, $leads[1]->repeat_of_lead_id);
        $this->assertSame($leads[0]->phone_hash, $leads[1]->phone_hash);
    }

    public function test_one_phone_number_is_limited_per_hour(): void
    {
        Queue::fake();
        config(['urbanhaven.lead.per_phone_per_hour' => 2]);

        foreach (['A', 'B'] as $message) {
            $this->postJson(route('inquiries.store'), $this->payload(['message' => $message]))->assertCreated();
        }

        $this->postJson(route('inquiries.store'), $this->payload(['message' => 'C']))->assertStatus(429);
        $this->assertDatabaseCount('leads', 2);
    }

    public function test_seventh_submission_from_one_address_in_a_minute_returns_429(): void
    {
        Queue::fake();

        for ($i = 0; $i < 6; $i++) {
            $this->postJson(route('inquiries.store'), $this->payload(['phone' => '0171'.str_pad((string) $i, 7, '0', STR_PAD_LEFT)]));
        }

        $this->postJson(route('inquiries.store'), $this->payload(['phone' => '01819999999']))
            ->assertStatus(429)
            ->assertJsonStructure(['message']);
    }

    public function test_json_errors_keep_their_http_status(): void
    {
        $this->postJson(route('inquiries.store'), [])->assertUnprocessable()->assertJsonStructure(['message', 'errors']);
        $this->getJson('/properties/does-not-exist')->assertNotFound()->assertJsonMissingPath('exception');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Rafi Ahmed',
            'phone' => '01711111111',
            'consent_given' => '1',
            'submission_token' => (string) Str::uuid(),
        ], $overrides);
    }
}
