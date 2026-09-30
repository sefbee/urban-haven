<?php

namespace Tests\Feature\Leads;

use App\Jobs\NotifyNewLeadJob;
use App\Models\LocationArea;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PublicationState;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LeadCaptureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_inquiry_creates_a_lead_and_dispatches_notification_job(): void
    {
        Queue::fake();
        $area = LocationArea::query()->create(['name' => 'Banani', 'city' => 'Dhaka', 'is_active' => true]);
        $type = PropertyType::query()->create(['key' => 'apt2', 'label' => 'Apartment', 'is_active' => true]);
        $property = Property::query()->create([
            'title' => 'Enquiry home',
            'property_type_id' => $type->id,
            'location_area_id' => $area->id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_basis' => 'total_sale',
            'area_unit' => 'sqft',
        ]);
        $property->publicationState()->update(['status' => PublicationState::PUBLISHED]);

        $this->post(route('inquiries.store'), [
            'name' => 'Rafi',
            'phone' => '01711111111',
            'property_id' => $property->id,
            'utm_source' => 'google',
        ], ['Accept' => 'application/json'])->assertCreated();

        $this->assertDatabaseHas('leads', ['name' => 'Rafi', 'utm_source' => 'google']);
        Queue::assertPushed(NotifyNewLeadJob::class);
    }

    public function test_eleventh_inquiry_returns_429(): void
    {
        $area = LocationArea::query()->create(['name' => 'Mirpur', 'city' => 'Dhaka', 'is_active' => true]);
        $type = PropertyType::query()->create(['key' => 'apt3', 'label' => 'Apartment', 'is_active' => true]);
        $property = Property::query()->create([
            'title' => 'Rate limit home',
            'property_type_id' => $type->id,
            'location_area_id' => $area->id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_basis' => 'total_sale',
            'area_unit' => 'sqft',
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('inquiries.store'), [
                'name' => 'Rafi',
                'phone' => '01712222222',
                'property_id' => $property->id,
            ]);
        }

        $this->post(route('inquiries.store'), [
            'name' => 'Rafi',
            'phone' => '01712222222',
            'property_id' => $property->id,
        ])->assertStatus(429);
    }
}
