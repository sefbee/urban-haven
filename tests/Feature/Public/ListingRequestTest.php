<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ListingRequestTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_list_property_page_returns_not_found(): void
    {
        $this->get('/list-property')->assertNotFound();
    }

    public function test_list_property_submission_is_not_allowed(): void
    {
        $this->post('/list-property', [
            'name' => 'Rafi',
            'phone' => '01711111111',
            'listing_type' => 'sale',
        ])->assertMethodNotAllowed();
    }
}
