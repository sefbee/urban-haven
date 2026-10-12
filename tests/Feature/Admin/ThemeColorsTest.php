<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\ThemeColors;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ThemeColorsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_theme_colors_default_structure_contains_all_six_roles(): void
    {
        $current = ThemeColors::current();

        $this->assertArrayHasKey('brand', $current);
        $this->assertArrayHasKey('primary_1', $current['brand']);
        $this->assertArrayHasKey('primary_2', $current['brand']);
        $this->assertArrayHasKey('secondary_1', $current['brand']);
        $this->assertArrayHasKey('secondary_2', $current['brand']);
        $this->assertArrayHasKey('accent_1', $current['brand']);
        $this->assertArrayHasKey('accent_2', $current['brand']);

        // Backward-compatible aliases
        $this->assertSame($current['brand']['primary_1'], $current['brand']['dominant']);
        $this->assertSame($current['brand']['secondary_1'], $current['brand']['secondary']);
        $this->assertSame($current['brand']['accent_1'], $current['brand']['accent']);
    }

    public function test_css_variables_outputs_six_color_tokens_and_mappings(): void
    {
        $vars = ThemeColors::cssVariables();

        $this->assertArrayHasKey('--color-primary-1', $vars);
        $this->assertArrayHasKey('--color-primary-2', $vars);
        $this->assertArrayHasKey('--color-secondary-1', $vars);
        $this->assertArrayHasKey('--color-secondary-2', $vars);
        $this->assertArrayHasKey('--color-accent-1', $vars);
        $this->assertArrayHasKey('--color-accent-2', $vars);
        $this->assertArrayHasKey('--color-accent-2-hover', $vars);

        // Verify mappings
        $this->assertSame($vars['--color-primary-1'], $vars['--color-dominant']);
        $this->assertSame($vars['--color-primary-1'], $vars['--color-page-background']);
        $this->assertSame($vars['--color-primary-2'], $vars['--color-card-surface']);
        $this->assertSame($vars['--color-secondary-1'], $vars['--color-secondary']);
        $this->assertSame($vars['--color-secondary-1'], $vars['--color-body-text']);
        $this->assertSame($vars['--color-secondary-2'], $vars['--color-muted-text']);
        $this->assertSame($vars['--color-accent-1'], $vars['--color-accent']);
        $this->assertSame($vars['--color-accent-2'], $vars['--color-gold']);
        $this->assertSame($vars['--color-accent-2'], $vars['--color-brass']);
    }

    public function test_owner_can_save_six_color_palette_via_api(): void
    {
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);

        $payload = [
            'theme' => [
                'brand' => [
                    'primary_1' => '#F4EFE6',
                    'primary_2' => '#FFFFFF',
                    'secondary_1' => '#18201E',
                    'secondary_2' => '#7A756D',
                    'accent_1' => '#214E34',
                    'accent_2' => '#D4AF37',
                ],
                'advanced' => [
                    'surfaces_text' => [],
                    'hero_slider' => [],
                    'mobile_nav' => [],
                ],
            ],
        ];

        $response = $this->actingAs($owner)
            ->postJson(route('api.admin.settings.theme'), $payload);

        $response->assertOk()
            ->assertJsonPath('message', 'Colors saved.');

        $stored = ThemeColors::current();
        $this->assertSame('#f4efe6', $stored['brand']['primary_1']);
        $this->assertSame('#ffffff', $stored['brand']['primary_2']);
        $this->assertSame('#18201e', $stored['brand']['secondary_1']);
        $this->assertSame('#7a756d', $stored['brand']['secondary_2']);
        $this->assertSame('#214e34', $stored['brand']['accent_1']);
        $this->assertSame('#d4af37', $stored['brand']['accent_2']);

        // Check CSS variables generated from stored values
        $vars = ThemeColors::cssVariables();
        $this->assertSame('#f4efe6', $vars['--color-primary-1']);
        $this->assertSame('#ffffff', $vars['--color-primary-2']);
        $this->assertSame('#18201e', $vars['--color-secondary-1']);
        $this->assertSame('#7a756d', $vars['--color-secondary-2']);
        $this->assertSame('#214e34', $vars['--color-accent-1']);
        $this->assertSame('#d4af37', $vars['--color-accent-2']);
        $this->assertSame('#d4af37', $vars['--color-gold']);
    }

    public function test_api_rejects_invalid_hex_colors(): void
    {
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);

        $response = $this->actingAs($owner)
            ->postJson(route('api.admin.settings.theme'), [
                'theme' => [
                    'brand' => [
                        'primary_1' => 'invalid-color',
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('theme.brand.primary_1');
    }

    public function test_admin_theme_settings_page_renders_successfully(): void
    {
        $owner = User::factory()->create();
        $this->assignRole($owner, Role::OWNER_ADMIN);

        $this->actingAs($owner)
            ->get(route('admin.settings.theme'))
            ->assertOk()
            ->assertSee('6-Colour Palette Rule (60-30-10)')
            ->assertSee('60% Primary')
            ->assertSee('30% Secondary')
            ->assertSee('10% Accent')
            ->assertSee('Curated Designer Palettes')
            ->assertSee('Primary Colours')
            ->assertSee('Secondary Colours')
            ->assertSee('Accent Colours')
            ->assertSee('Interactive Live Preview');
    }

    public function test_public_pages_reflect_theme_css_variables(): void
    {
        Setting::set('theme_colors', [
            'brand' => [
                'primary_1' => '#faf8f5',
                'primary_2' => '#ffffff',
                'secondary_1' => '#121212',
                'secondary_2' => '#666666',
                'accent_1' => '#0a5c36',
                'accent_2' => '#e5a93b',
            ],
        ], 'theme', 'json');

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('--color-primary-1:#faf8f5', false)
            ->assertSee('--color-primary-2:#ffffff', false)
            ->assertSee('--color-secondary-1:#121212', false)
            ->assertSee('--color-secondary-2:#666666', false)
            ->assertSee('--color-accent-1:#0a5c36', false)
            ->assertSee('--color-accent-2:#e5a93b', false)
            ->assertSee('--color-card-surface:#ffffff', false)
            ->assertSee('--color-page-background:#faf8f5', false);
    }
}
