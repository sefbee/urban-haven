<?php

namespace Tests\Feature\Admin;

use App\Models\Faq;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminSearchTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_finds_properties_leads_and_faqs(): void
    {
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);
        $property = $this->makePublishableProperty(['title' => 'Gulshan Lakeview Residence']);
        $this->lead(['name' => 'Gulshan Buyer']);
        Faq::query()->create(['question' => 'Is Gulshan safe?', 'answer' => 'Yes.', 'group' => 'Areas', 'is_visible' => true]);

        $this->actingAs($owner)
            ->get(route('admin.search', ['q' => 'Gulshan']))
            ->assertOk()
            ->assertSee('Gulshan Lakeview Residence')
            ->assertSee(route('admin.properties.edit', $property), false)
            ->assertSee('Gulshan Buyer')
            ->assertSee('Is Gulshan safe?');
    }

    public function test_sales_user_only_finds_leads_they_may_see(): void
    {
        $sales = User::factory()->create();
        $this->assignRole($sales, Role::SALES_USER);
        $other = User::factory()->create();
        $this->assignRole($other, Role::SALES_USER);
        $this->lead(['name' => 'Rahim Mine', 'assigned_to' => $sales->id]);
        $this->lead(['name' => 'Rahim Theirs', 'assigned_to' => $other->id]);

        $this->actingAs($sales)
            ->get(route('admin.search', ['q' => 'Rahim']))
            ->assertOk()
            ->assertSee('Rahim Mine')
            ->assertDontSee('Rahim Theirs');
    }

    public function test_editor_does_not_see_leads_or_staff(): void
    {
        $editor = User::factory()->create(['name' => 'Karim Editor']);
        $this->assignRole($editor, Role::CONTENT_EDITOR);
        $this->lead(['name' => 'Karim Lead']);

        $this->actingAs($editor)
            ->get(route('admin.search', ['q' => 'Karim']))
            ->assertOk()
            ->assertDontSee('Karim Lead')
            ->assertDontSee(route('admin.staff.edit', $editor), false);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function lead(array $attributes): Lead
    {
        return Lead::query()->create(array_merge([
            'type' => 'general_contact',
            'phone' => '+8801711111111',
            'status' => 'new',
            'priority' => 'medium',
        ], $attributes));
    }

    public function test_short_terms_do_not_search(): void
    {
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);

        $this->actingAs($owner)->get(route('admin.search', ['q' => 'a']))->assertOk()->assertSee('Type at least two characters.');
    }
}
