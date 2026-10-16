<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_english_remains_active_even_with_a_bengali_session(): void
    {
        $this->withSession(['locale' => 'bn'])
            ->get(route('properties.index', ['q' => 'gulshan']))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Search');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Explore properties')
            ->assertSee('fa-light fa-magnifying-glass', false);
    }

    public function test_locale_endpoint_rejects_non_english_locales(): void
    {
        $this->from(route('home'))
            ->post(route('locale.switch'), ['locale' => 'bn'])
            ->assertSessionHasErrors('locale');

        $this->from(route('home'))
            ->post(route('locale.switch'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');
    }

    public function test_bengali_catalog_covers_the_english_interface_catalog(): void
    {
        $english = json_decode((string) file_get_contents(resource_path('lang/en.json')), true, 512, JSON_THROW_ON_ERROR);
        $bengali = json_decode((string) file_get_contents(resource_path('lang/bn.json')), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([], array_diff(array_keys($english), array_keys($bengali)));
    }
}
