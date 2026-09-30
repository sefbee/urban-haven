<?php

namespace Tests\Feature\Security;

use App\Models\LocationArea;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PublicationState;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_form_includes_a_csrf_token_field(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_web_middleware_includes_request_forgery_protection(): void
    {
        $web = app(Kernel::class)->getMiddlewareGroups()['web'] ?? [];

        $this->assertTrue(collect($web)->contains(
            fn (mixed $middleware): bool => is_string($middleware) && str_contains($middleware, 'PreventRequestForgery'),
        ));
    }

    public function test_property_title_is_escaped_on_the_public_page(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $area = LocationArea::query()->create(['name' => 'Uttara', 'city' => 'Dhaka', 'is_active' => true]);
        $type = PropertyType::query()->create(['key' => 'apt-x', 'label' => 'Apartment', 'is_active' => true]);
        $property = Property::query()->create([
            'title' => '<script>alert(1)</script>',
            'property_type_id' => $type->id,
            'location_area_id' => $area->id,
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_basis' => 'total_sale',
            'area_unit' => 'sqft',
        ]);
        $property->publicationState()->update(['status' => PublicationState::PUBLISHED]);

        $this->get(route('properties.show', $property->slug))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }
}
