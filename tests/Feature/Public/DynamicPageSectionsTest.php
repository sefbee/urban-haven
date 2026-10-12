<?php

namespace Tests\Feature\Public;

use App\Models\CmsPage;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PublicationState;
use App\Models\Role;
use App\Models\User;
use App\Support\AboutPageSections;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DynamicPageSectionsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_about_page_renders_dynamic_content_and_updates_instantly(): void
    {
        $admin = User::factory()->create();
        $this->assignRole($admin, Role::OWNER_ADMIN);

        $page = CmsPage::query()->create([
            'slug' => 'about',
            'title' => 'About Us',
            'template' => 'about',
            'body' => '<p>Original about body story</p>',
            'status' => 'published',
        ]);
        $page->publicationState()->updateOrCreate([], ['status' => PublicationState::PUBLISHED]);

        // 1. Initial public visit with defaults
        $response = $this->get('/about');
        $response->assertOk();

        // 2. Admin updates the Hero section via PageSectionController
        $this->actingAs($admin)
            ->put(route('admin.page-sections.update', ['page' => 'about', 'section' => 'hero']), [
                'content' => [
                    'eyebrow' => 'DYNAMIC PREVIEW EYEBROW',
                    'title' => 'CUSTOM HEADLINE FOR ABOUT',
                    'intro' => 'A unique dynamic intro for the about page',
                    'primary_cta' => 'Dynamic Action Button',
                    'primary_url' => '/custom-dynamic-action',
                    'badge' => 'SPECIAL BADGE',
                ],
            ])
            ->assertRedirect();

        // 3. Frontend visit immediately reflects the new content without stale cache
        $this->get('/about')
            ->assertOk()
            ->assertSee('DYNAMIC PREVIEW EYEBROW')
            ->assertSee('CUSTOM HEADLINE FOR ABOUT')
            ->assertSee('A unique dynamic intro for the about page')
            ->assertSee('Dynamic Action Button')
            ->assertSee('/custom-dynamic-action')
            ->assertSee('SPECIAL BADGE');

        // 4. Admin hides a section (e.g. Purpose)
        $this->actingAs($admin)
            ->post(route('admin.page-sections.visibility', ['page' => 'about', 'section' => 'purpose']))
            ->assertRedirect();

        // Purpose section should not be visible anymore
        $sections = AboutPageSections::visible();
        $this->assertArrayNotHasKey('purpose', $sections);

        // 5. Admin updates the Story section with custom quote and CTA
        $this->actingAs($admin)
            ->put(route('admin.page-sections.update', ['page' => 'about', 'section' => 'story']), [
                'content' => [
                    'eyebrow' => 'OUR HERITAGE',
                    'title' => 'CRAFTED WITH PRECISION',
                    'quote' => 'Every residence is a testament to timeless architecture.',
                    'quote_kicker' => 'OUR MOTTO',
                    'quote_label' => 'Founding Architect',
                    'cta_label' => 'Explore Signature Units',
                    'cta_url' => '/signature-units',
                    'stat_one_value' => '99%',
                    'stat_one_label' => 'Client Satisfaction',
                ],
            ])
            ->assertRedirect();

        $this->get('/about')
            ->assertOk()
            ->assertSee('OUR HERITAGE')
            ->assertSee('CRAFTED WITH PRECISION')
            ->assertSee('Every residence is a testament to timeless architecture.')
            ->assertSee('Founding Architect')
            ->assertSee('Explore Signature Units')
            ->assertSee('/signature-units')
            ->assertSee('99%')
            ->assertSee('Client Satisfaction');
    }

    public function test_contact_page_renders_dynamic_sections_and_cta(): void
    {
        $admin = User::factory()->create();
        $this->assignRole($admin, Role::OWNER_ADMIN);

        $page = CmsPage::query()->create([
            'slug' => 'contact',
            'title' => 'Contact',
            'template' => 'contact',
            'body' => '<p>Contact Us</p>',
            'status' => 'published',
        ]);
        $page->publicationState()->updateOrCreate([], ['status' => PublicationState::PUBLISHED]);

        // Admin updates contact hero & cta
        $this->actingAs($admin)
            ->put(route('admin.page-sections.update', ['page' => 'contact', 'section' => 'hero']), [
                'content' => [
                    'eyebrow' => 'GET IN TOUCH TODAY',
                    'title' => 'CUSTOM CONTACT HEADLINE',
                    'intro' => 'Speak directly with our Dhaka real estate team.',
                ],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->put(route('admin.page-sections.update', ['page' => 'contact', 'section' => 'cta']), [
                'content' => [
                    'title' => 'DYNAMIC BOTTOM CTA TITLE',
                    'intro' => 'Get in touch for bespoke property advisory.',
                    'primary_cta' => 'Call Private Banker',
                    'primary_url' => '/call-private',
                    'secondary_cta' => 'Browse Portfolio',
                    'secondary_url' => '/portfolio',
                ],
            ])
            ->assertRedirect();

        $this->get('/contact')
            ->assertOk()
            ->assertSee('GET IN TOUCH TODAY')
            ->assertSee('CUSTOM CONTACT HEADLINE')
            ->assertSee('Speak directly with our Dhaka real estate team.')
            ->assertSee('DYNAMIC BOTTOM CTA TITLE')
            ->assertSee('Get in touch for bespoke property advisory.')
            ->assertSee('Call Private Banker')
            ->assertSee('Browse Portfolio');
    }

    public function test_articles_page_renders_dynamic_sections_and_promo(): void
    {
        $admin = User::factory()->create();
        $this->assignRole($admin, Role::OWNER_ADMIN);

        $category = PostCategory::query()->create(['name' => 'Architecture', 'slug' => 'architecture']);
        $post = Post::factory()->create([
            'title' => 'Designing Homes in Gulshan',
            'post_category_id' => $category->id,
        ]);
        $post->publicationState()->updateOrCreate([], ['status' => PublicationState::PUBLISHED, 'published_at' => now()]);

        // Admin updates articles masthead & promo
        $this->actingAs($admin)
            ->put(route('admin.page-sections.update', ['page' => 'articles', 'section' => 'masthead']), [
                'content' => [
                    'eyebrow' => 'INSIGHTS & MARKET INTELLIGENCE',
                    'title' => 'DHAKA LUXURY PROPERTY DIGEST',
                    'intro' => 'Market analyses, architectural deep-dives, and buyer guides.',
                ],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->put(route('admin.page-sections.update', ['page' => 'articles', 'section' => 'promo']), [
                'content' => [
                    'eyebrow' => 'EXCLUSIVE REPORT',
                    'title' => 'Download 2026 Dhaka Real Estate Outlook',
                    'cta_label' => 'Download Free PDF',
                    'cta_url' => '/download/report-2026',
                ],
            ])
            ->assertRedirect();

        $this->get('/articles')
            ->assertOk()
            ->assertSee('INSIGHTS & MARKET INTELLIGENCE')
            ->assertSee('DHAKA LUXURY PROPERTY DIGEST')
            ->assertSee('Market analyses, architectural deep-dives, and buyer guides.')
            ->assertSee('EXCLUSIVE REPORT')
            ->assertSee('Download 2026 Dhaka Real Estate Outlook')
            ->assertSee('Download Free PDF')
            ->assertSee('/download/report-2026');
    }
}
