<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsScreensTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_saves_theme_colours_and_rejects_invalid_ones(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->put(route('admin.settings.update'), [
            'groups' => ['theme'],
            'settings' => ['theme_primary' => '#3B5BDB', 'theme_dark' => '#111111', 'theme_accent' => '#a68456', 'theme_surface' => '#ffffff'],
        ])->assertSessionHasNoErrors();

        $this->assertSame('#3b5bdb', Setting::get('theme_primary'));

        $this->actingAs($owner)->put(route('admin.settings.update'), [
            'groups' => ['theme'],
            'settings' => ['theme_primary' => 'blue', 'theme_dark' => '#111111', 'theme_accent' => '#a68456', 'theme_surface' => '#ffffff'],
        ])->assertSessionHasErrors('settings.theme_primary');
    }

    public function test_owner_uploads_and_removes_the_logo(): void
    {
        Storage::fake('public');
        $owner = $this->owner();
        $general = ['company_name' => 'Urban Haven', 'company_tagline' => ''];

        $this->actingAs($owner)->put(route('admin.settings.update'), [
            'groups' => ['branding', 'brand_assets'],
            'settings' => $general,
            'images' => ['brand_logo' => UploadedFile::fake()->image('logo.png', 600, 200)],
        ])->assertSessionHasNoErrors();

        $path = Setting::get('brand_logo');
        $this->assertStringStartsWith('branding/', $path);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($owner)->get(route('admin.dashboard'))->assertOk()->assertSee($path);

        $this->actingAs($owner)->put(route('admin.settings.update'), [
            'groups' => ['branding', 'brand_assets'],
            'settings' => $general,
            'remove_images' => ['brand_logo' => '1'],
        ])->assertSessionHasNoErrors();

        $this->assertSame('', (string) Setting::get('brand_logo'));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_analytics_settings_override_config_and_can_be_paused(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->put(route('admin.settings.update'), [
            'groups' => ['analytics'],
            'settings' => ['analytics_enabled' => '1', 'analytics_gtm_id' => 'GTM-ABC123', 'analytics_ga4_id' => '', 'analytics_meta_pixel_id' => ''],
        ])->assertSessionHasNoErrors();

        $this->actingAs($owner)->get(route('admin.settings.analytics'))->assertOk()->assertSee('GTM-ABC123');
        $this->assertSame('GTM-ABC123', config('urbanhaven.analytics.gtm_id'));

        $this->actingAs($owner)->put(route('admin.settings.update'), [
            'groups' => ['analytics'],
            'settings' => ['analytics_enabled' => '0', 'analytics_gtm_id' => 'GTM-ABC123', 'analytics_ga4_id' => '', 'analytics_meta_pixel_id' => ''],
        ]);

        $this->actingAs($owner)->get(route('admin.settings.analytics'))->assertOk()->assertSee('Paused');
        $this->assertNull(config('urbanhaven.analytics.gtm_id'));

        $this->actingAs($owner)->put(route('admin.settings.update'), [
            'groups' => ['analytics'],
            'settings' => ['analytics_enabled' => '1', 'analytics_gtm_id' => 'nope', 'analytics_ga4_id' => '', 'analytics_meta_pixel_id' => ''],
        ])->assertSessionHasErrors('settings.analytics_gtm_id');
    }

    public function test_social_profiles_are_saved_and_mirrored_for_the_public_footer(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->put(route('admin.settings.social'), ['profiles' => [
            ['platform' => 'facebook', 'label' => '', 'url' => 'https://facebook.com/urbanhaven', 'active' => '1'],
            ['platform' => 'youtube', 'label' => 'Tours', 'url' => 'https://youtube.com/@urbanhaven', 'active' => '0'],
        ]])->assertSessionHasNoErrors();

        $this->assertSame(['https://facebook.com/urbanhaven'], array_values((array) Setting::get('social_links')));

        $this->actingAs($owner)->get(route('admin.settings.contact'))->assertOk()->assertSee('Tours');

        $this->actingAs($owner)->put(route('admin.settings.social'), ['profiles' => [
            ['platform' => 'facebook', 'url' => 'http://insecure.example', 'active' => '1'],
        ]])->assertSessionHasErrors('profiles.0.url');
    }

    public function test_seo_indexing_switch_controls_robots(): void
    {
        $owner = $this->owner();
        $this->app['env'] = 'production';

        $this->actingAs($owner)->put(route('admin.settings.update'), [
            'groups' => ['seo'],
            'settings' => ['seo_default_title' => '', 'seo_default_description' => '', 'seo_google_verification' => '', 'seo_allow_indexing' => '0'],
        ])->assertSessionHasNoErrors();

        $this->get(route('robots'))->assertOk()->assertSee('Disallow: /');
    }

    public function test_non_owners_cannot_open_or_save_settings(): void
    {
        $editor = User::factory()->create();
        $this->assignRole($editor, Role::CONTENT_EDITOR);

        $this->actingAs($editor)->get(route('admin.settings.contact'))->assertForbidden();
        $this->actingAs($editor)->put(route('admin.settings.update'), ['groups' => ['theme'], 'settings' => ['theme_primary' => '#000000']])->assertForbidden();
        $this->actingAs($editor)->put(route('admin.settings.social'), ['profiles' => []])->assertForbidden();
    }

    private function owner(): User
    {
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);

        return $owner;
    }
}
