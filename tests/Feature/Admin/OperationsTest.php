<?php

namespace Tests\Feature\Admin;

use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\OverdueFollowUpsDigest;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperationsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_editor_cannot_publish_cms_pages_or_blocks(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);
        $page = CmsPage::query()->create(['title' => 'Privacy', 'body' => '<p>Draft</p>']);
        $block = CmsBlock::query()->create(['key' => 'hero', 'label' => 'Hero', 'content' => ['title' => 'Live title'], 'is_visible' => true, 'updated_at' => now()]);

        $this->actingAs($editor)->post(route('admin.cms.publish', $page))->assertForbidden();
        $this->assertFalse($page->fresh()->isPublished());

        $this->actingAs($editor)->put(route('admin.cms.blocks.update', $block), ['content' => ['title' => 'Editor title'], 'is_visible' => '0'])->assertRedirect();
        $block->refresh();
        $this->assertSame('Live title', $block->content['title']);
        $this->assertSame('Editor title', $block->draft_content['title']);
        $this->assertTrue($block->is_visible);

        $this->actingAs($editor)->post(route('admin.cms.blocks.publish', $block))->assertForbidden();
    }

    public function test_publisher_uploads_several_homepage_hero_images_and_editor_cannot(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $owner = $this->staff(Role::OWNER_ADMIN);
        $editor = $this->staff(Role::CONTENT_EDITOR);
        $block = CmsBlock::query()->create(['key' => 'hero', 'label' => 'Hero', 'content' => ['title' => 'Live title'], 'is_visible' => true, 'updated_at' => now()]);
        $upload = fn (): array => [
            'owner_type' => 'cms_block',
            'owner_id' => $block->id,
            'collection' => 'gallery',
            'files' => [UploadedFile::fake()->image('skyline.jpg', 1600, 900), UploadedFile::fake()->image('river.jpg', 1600, 900)],
        ];

        $this->actingAs($editor)->post(route('admin.media.store'), $upload())->assertForbidden();
        $this->assertSame(0, $block->media()->count());

        $this->actingAs($owner)->post(route('admin.media.store'), $upload())->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(2, $block->media()->count());
        $this->assertTrue(CmsBlock::imagesFor('hero')->isEmpty(), 'Images stay private until they have alt text.');

        [$skyline, $river] = $block->media()->get()->all();
        $this->actingAs($owner)->patch(route('admin.media.update', $skyline), ['alt_text' => 'Dhaka skyline at dusk', 'is_public' => '1'])->assertRedirect();
        $this->actingAs($owner)->patch(route('admin.media.update', $river), ['alt_text' => 'Hatirjheel at night', 'is_public' => '1'])->assertRedirect();

        $this->assertSame([$skyline->id, $river->id], CmsBlock::imagesFor('hero')->pluck('id')->all());
        $this->get(route('home'))->assertOk()
            ->assertSee('Dhaka skyline at dusk')
            ->assertSee('uhCoverSlides(2)', false)
            ->assertSee('Show photo 2 of 2');

        $this->actingAs($editor)->delete(route('admin.media.destroy', $river))->assertForbidden();
        $this->actingAs($owner)->delete(route('admin.media.destroy', $river))->assertRedirect();
        $this->assertSame([$skyline->id], CmsBlock::imagesFor('hero')->pluck('id')->all());
        $this->get(route('home'))->assertOk()->assertDontSee('uhCoverSlides', false);
    }

    public function test_hero_block_only_accepts_photographs(): void
    {
        Storage::fake('public');
        $owner = $this->staff(Role::OWNER_ADMIN);
        $block = CmsBlock::query()->create(['key' => 'hero', 'label' => 'Hero', 'content' => ['title' => 'Live title'], 'is_visible' => true, 'updated_at' => now()]);

        $this->actingAs($owner)->post(route('admin.media.store'), [
            'owner_type' => 'cms_block',
            'owner_id' => $block->id,
            'collection' => 'brochure',
            'file' => UploadedFile::fake()->create('brochure.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('collection');
    }

    public function test_blocks_without_images_reject_uploads(): void
    {
        Storage::fake('public');
        $owner = $this->staff(Role::OWNER_ADMIN);
        $block = CmsBlock::query()->create(['key' => 'about', 'label' => 'About', 'content' => ['title' => 'About'], 'is_visible' => true, 'updated_at' => now()]);

        $this->actingAs($owner)->post(route('admin.media.store'), [
            'owner_type' => 'cms_block',
            'owner_id' => $block->id,
            'file' => UploadedFile::fake()->image('skyline.jpg', 1600, 900),
        ])->assertForbidden();
    }

    public function test_editor_changes_to_a_live_page_wait_for_a_publisher(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $editor = $this->staff(Role::CONTENT_EDITOR);
        $page = CmsPage::query()->create(['title' => 'About', 'slug' => 'about', 'body' => '<p>Live body</p>']);
        $this->actingAs($owner)->post(route('admin.cms.publish', $page))->assertRedirect();

        $this->actingAs($editor)->put(route('admin.cms.update', $page), ['title' => 'About', 'slug' => 'about', 'template' => 'default', 'body' => '<p>Edited body</p>'])->assertRedirect();

        $this->get(route('cms.show', 'about'))->assertOk()->assertSee('Live body')->assertDontSee('Edited body');

        $this->actingAs($owner)->post(route('admin.cms.publish', $page->fresh()))->assertRedirect();
        $this->get(route('cms.show', 'about'))->assertOk()->assertSee('Edited body');
    }

    public function test_cms_body_is_sanitised(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.cms.store'), [
            'title' => 'Terms',
            'slug' => 'terms',
            'template' => 'default',
            'body' => '<p>Fine</p><script>alert(1)</script><a href="javascript:alert(1)">x</a>',
        ])->assertRedirect();

        $body = CmsPage::query()->where('slug', 'terms')->value('body');
        $this->assertStringNotContainsString('<script', $body);
        $this->assertStringNotContainsString('javascript:', $body);
    }

    public function test_cms_slug_cannot_shadow_application_routes(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);

        $this->actingAs($owner)->post(route('admin.cms.store'), ['title' => 'Admin', 'slug' => 'admin', 'template' => 'default', 'body' => 'x'])
            ->assertSessionHasErrors('slug');
    }

    public function test_editor_faq_entries_stay_hidden(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);

        $this->actingAs($editor)->post(route('admin.faqs.store'), ['question' => 'Is parking included?', 'answer' => 'Yes.', 'is_visible' => '1'])->assertRedirect();

        $this->assertFalse(Faq::query()->sole()->is_visible);
        $this->get(route('faq'))->assertOk()->assertDontSee('Is parking included?');
    }

    public function test_settings_ignore_unknown_keys_and_require_a_purpose(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $payload = [
            'company_name' => 'Urban Haven',
            'phone' => '01711000000',
            'email' => 'sales@example.com',
            'consent_text' => 'You may contact me.',
            'lead_retention_days' => 730,
            'address_display_mode' => 'approximate',
            'coordinate_precision' => 2,
            'enable_sale' => '1',
            'enable_rent' => '1',
            'analytics_script' => '<script>evil()</script>',
        ];

        $this->actingAs($owner)->put(route('admin.settings.update'), ['settings' => $payload])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('settings', ['key' => 'analytics_script']);
        $this->assertSame('+8801711000000', Setting::get('phone'));

        $this->actingAs($owner)->put(route('admin.settings.update'), ['settings' => [...$payload, 'enable_sale' => '0', 'enable_rent' => '0']])
            ->assertSessionHasErrors();
        $this->assertTrue((bool) Setting::get('enable_sale'));
    }

    public function test_non_owner_cannot_change_settings(): void
    {
        $editor = $this->staff(Role::CONTENT_EDITOR);

        $this->actingAs($editor)->put(route('admin.settings.update'), ['settings' => ['company_name' => 'Hijacked']])->assertForbidden();
    }

    public function test_staff_reach_the_dashboard_without_two_factor_authentication(): void
    {
        config(['urbanhaven.mfa.enforce' => true]);
        $owner = $this->staff(Role::OWNER_ADMIN);
        $sales = $this->staff(Role::SALES_USER);

        $this->actingAs($owner)->get(route('admin.dashboard'))->assertOk()->assertSee('Dashboard');
        $this->actingAs($sales)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($owner)->get(route('admin.mfa.setup'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($owner)->get(route('admin.mfa.challenge'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_redirect_csv_import_reports_bad_rows(): void
    {
        $owner = $this->staff(Role::OWNER_ADMIN);
        $csv = UploadedFile::fake()->createWithContent('redirects.csv', "from_path,to_path,http_code\n/old-a,/properties,301\nnot-a-path,/x,301\n/old-b,https://example.com/new,302\n");

        $this->actingAs($owner)->post(route('admin.redirects.import'), ['file' => $csv])
            ->assertRedirect()
            ->assertSessionHas('importFailures');

        $this->assertDatabaseHas('redirects', ['from_path' => '/old-a', 'http_code' => 301]);
        $this->assertDatabaseHas('redirects', ['from_path' => '/old-b', 'http_code' => 302]);
        $this->assertDatabaseCount('redirects', 2);
    }

    public function test_overdue_digest_notifies_owners_of_overdue_follow_ups(): void
    {
        Notification::fake();
        $sales = $this->staff(Role::SALES_USER);
        $lead = Lead::query()->create(['type' => 'general_contact', 'name' => 'Late', 'phone' => '+8801711111111', 'phone_hash' => Lead::hashPhone('+8801711111111'), 'status' => 'contacted', 'priority' => 'high', 'assigned_to' => $sales->id]);
        LeadFollowUp::query()->create(['lead_id' => $lead->id, 'user_id' => $sales->id, 'action_type' => 'call', 'scheduled_at' => now()->subDay(), 'status' => LeadFollowUp::OPEN, 'priority' => 'high']);

        $this->artisan('uh:overdue-digest')->assertSuccessful();

        Notification::assertSentTo($sales, OverdueFollowUpsDigest::class);
    }

    public function test_retention_prunes_only_old_closed_leads(): void
    {
        Setting::set('lead_retention_days', 30, 'leads', 'int');
        $old = Lead::query()->create(['type' => 'general_contact', 'name' => 'Old lost', 'phone' => '+8801711111111', 'phone_hash' => 'a', 'status' => 'lost', 'loss_reason' => 'price', 'priority' => 'low']);
        $open = Lead::query()->create(['type' => 'general_contact', 'name' => 'Old open', 'phone' => '+8801722222222', 'phone_hash' => 'b', 'status' => 'contacted', 'priority' => 'low']);
        Lead::query()->whereKey([$old->id, $open->id])->update(['updated_at' => now()->subDays(60)]);

        $this->artisan('uh:prune-leads', ['--dry-run' => true])->assertSuccessful();
        $this->assertDatabaseHas('leads', ['id' => $old->id]);

        $this->artisan('uh:prune-leads')->assertSuccessful();
        $this->assertDatabaseMissing('leads', ['id' => $old->id]);
        $this->assertDatabaseHas('leads', ['id' => $open->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lead.retention_pruned']);
    }

    public function test_health_command_and_up_endpoint_report_ok(): void
    {
        $this->artisan('uh:health')->assertSuccessful();
        $this->get('/up')->assertOk();
        $this->assertNotNull(cache()->get('uh:health'));
    }

    private function staff(string $role): User
    {
        $user = User::factory()->create();
        $this->assignRole($user, $role);

        return $user;
    }
}
