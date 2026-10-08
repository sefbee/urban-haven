<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\HealthStatus;
use App\Support\PhoneNumber;
use App\Support\SettingsSchema;
use App\Support\SocialProfiles;
use App\Support\TaggedCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    private const BRANDING_DIRECTORY = 'branding';

    public function index(): View
    {
        return $this->screen('general');
    }

    public function theme(): View
    {
        return $this->screen('theme');
    }

    public function contact(): View
    {
        return $this->screen('contact', ['socialProfiles' => SocialProfiles::all(), 'socialPlatforms' => SocialProfiles::PLATFORMS]);
    }

    public function enquiries(): View
    {
        return $this->screen('enquiries', ['mailConfigured' => HealthStatus::mailConfigured()]);
    }

    public function listings(): View
    {
        return $this->screen('listings');
    }

    public function analytics(): View
    {
        return $this->screen('analytics', ['tracking' => $this->trackingStatus()]);
    }

    public function seo(): View
    {
        return $this->screen('seo');
    }

    public function update(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($this->userCanManage(), 403);

        $input = (array) $request->input('settings', []);
        $definitions = $this->definitionsFor($request, $input);
        abort_if($definitions === [], 422, 'Nothing to save.');

        foreach ($definitions as $key => $definition) {
            if ($definition['input'] === 'checkbox') {
                $input[$key] = filter_var($input[$key] ?? false, FILTER_VALIDATE_BOOLEAN);
            }

            if ($definition['input'] === 'color' && is_string($input[$key] ?? null)) {
                $input[$key] = strtolower(trim($input[$key]));
            }

            if (in_array($definition['input'], ['text', 'email', 'url', 'tel'], true) && is_string($input[$key] ?? null)) {
                $input[$key] = trim($input[$key]);
            }
        }

        $imageKeys = array_keys(array_filter($definitions, fn (array $definition): bool => $definition['input'] === 'image'));
        $fieldDefinitions = array_diff_key($definitions, array_flip($imageKeys));

        $rules = collect($fieldDefinitions)->mapWithKeys(fn (array $definition, string $key): array => ['settings.'.$key => $definition['rules']])->all();
        foreach ($imageKeys as $key) {
            $rules['images.'.$key] = ['nullable', ...SettingsSchema::IMAGE_RULES[$key]];
            $rules['remove_images.'.$key] = ['nullable', 'boolean'];
        }

        $attributes = collect($definitions)->mapWithKeys(fn (array $definition, string $key): array => [
            'settings.'.$key => strtolower($definition['label']),
            'images.'.$key => strtolower($definition['label']),
        ])->all();

        $validated = validator([
            'settings' => $input,
            'images' => (array) $request->file('images', []),
            'remove_images' => (array) $request->input('remove_images', []),
        ], $rules, [
            'settings.*.regex' => 'The :attribute is not in the expected format.',
        ], $attributes)
            ->after(function ($validator) use ($input, $definitions): void {
                if (isset($definitions['enable_sale'], $definitions['enable_rent']) && ! ($input['enable_sale'] ?? false) && ! ($input['enable_rent'] ?? false)) {
                    $validator->errors()->add('settings.enable_sale', 'Keep at least one of sale or rent switched on.');
                }
            })
            ->validate();

        $before = Setting::allValues();
        $changed = [];

        foreach ($fieldDefinitions as $key => $definition) {
            $value = $validated['settings'][$key] ?? null;

            if ($definition['input'] === 'tel' && filled($value)) {
                $value = PhoneNumber::normalize((string) $value);
            }

            if (($before[$key] ?? null) !== $value) {
                $changed[] = $key;
            }

            Setting::set($key, $value ?? '', $definition['group'], $definition['cast']);
        }

        foreach ($imageKeys as $key) {
            $current = (string) ($before[$key] ?? '');
            $upload = $validated['images'][$key] ?? null;
            $remove = (bool) ($validated['remove_images'][$key] ?? false);

            if (! $upload instanceof UploadedFile && ! $remove) {
                continue;
            }

            $next = $upload instanceof UploadedFile ? $this->storeBrandImage($upload, $key) : '';
            $this->deleteBrandImage($current);
            Setting::set($key, $next, $definitions[$key]['group'], $definitions[$key]['cast']);
            $changed[] = $key;
        }

        if ($changed !== []) {
            $auditLogger->record($request->user()->id, 'settings.updated', Setting::class, null, null, ['keys' => $changed], $request->ip());
            TaggedCache::flush(['homepage', 'properties', 'search', 'projects', 'sitemap']);
        }

        return back()->with('status', $changed === [] ? 'No changes to save.' : 'Settings saved.');
    }

    public function updateSocial(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($this->userCanManage(), 403);

        $validated = $request->validate([
            'profiles' => ['nullable', 'array', 'max:'.SocialProfiles::MAX],
            'profiles.*.platform' => ['required', Rule::in(array_keys(SocialProfiles::PLATFORMS))],
            'profiles.*.label' => ['nullable', 'string', 'max:60'],
            'profiles.*.url' => ['required', 'url:https', 'max:255'],
            'profiles.*.active' => ['nullable', 'boolean'],
            'profiles.*.new_tab' => ['nullable', 'boolean'],
        ], [], [
            'profiles.*.platform' => 'platform',
            'profiles.*.url' => 'profile link',
            'profiles.*.label' => 'label',
        ]);

        SocialProfiles::save($validated['profiles'] ?? []);
        $auditLogger->record($request->user()->id, 'settings.updated', Setting::class, null, null, ['keys' => ['social_profiles']], $request->ip());
        TaggedCache::flush(['homepage', 'sitemap']);

        return back()->with('status', 'Social profiles saved.');
    }

    public function testEmail(Request $request): RedirectResponse
    {
        abort_unless($this->userCanManage(), 403);

        if (! HealthStatus::mailConfigured()) {
            return back()->withErrors(['mail' => 'Email is not configured on this server (driver: '.config('mail.default').'). Set MAIL_MAILER and SMTP details in the environment.']);
        }

        try {
            Mail::raw('This is a test message from '.config('app.name').' to confirm email delivery works.', function ($message) use ($request): void {
                $message->to($request->user()->email)->subject('Urban Haven test email');
            });
        } catch (Throwable $exception) {
            Log::warning('Test email failed', ['error' => $exception->getMessage()]);

            return back()->withErrors(['mail' => 'The mail server rejected the message. Check the SMTP settings and server log.']);
        }

        return back()->with('status', 'Test email sent to '.$request->user()->email.'.');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function screen(string $screen, array $extra = []): View
    {
        abort_unless($this->userCanManage(), 403);
        $config = SettingsSchema::SCREENS[$screen];

        return view('admin.settings.screen', [
            'screen' => $screen,
            'title' => $config['title'],
            'description' => $config['description'],
            'groups' => $config['groups'],
            'definitions' => collect(SettingsSchema::forGroups($config['groups']))->groupBy('group', preserveKeys: true),
            'values' => Setting::allValues(),
            ...$extra,
        ]);
    }

    /**
     * Groups named by the form, or else the groups of the keys that were sent.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, array<string, mixed>>
     */
    private function definitionsFor(Request $request, array $input): array
    {
        $editable = collect(SettingsSchema::SCREENS)->pluck('groups')->flatten()->unique()->all();
        $requested = array_values(array_intersect(array_filter((array) $request->input('groups', []), 'is_string'), $editable));

        if ($requested === []) {
            $all = SettingsSchema::definitions();
            $requested = collect(array_keys($input))
                ->filter(fn (mixed $key): bool => is_string($key) && isset($all[$key]))
                ->map(fn (string $key): string => $all[$key]['group'])
                ->intersect($editable)
                ->unique()
                ->values()
                ->all();
        }

        return SettingsSchema::forGroups($requested);
    }

    /**
     * @return list<array{label: string, state: string, tone: string, detail: string}>
     */
    private function trackingStatus(): array
    {
        $enabled = (bool) Setting::get('analytics_enabled', true);
        $consent = config('urbanhaven.analytics.consent_cookie');
        $row = function (string $label, ?string $value, string $missing) use ($enabled): array {
            return match (true) {
                ! $enabled => ['label' => $label, 'state' => 'Paused', 'tone' => 'neutral', 'detail' => 'Tracking is switched off below.'],
                filled($value) => ['label' => $label, 'state' => 'Active', 'tone' => 'success', 'detail' => $value],
                default => ['label' => $label, 'state' => 'Not set', 'tone' => 'warn', 'detail' => $missing],
            };
        };

        return [
            ['label' => 'Master switch', 'state' => $enabled ? 'On' : 'Off', 'tone' => $enabled ? 'success' : 'neutral', 'detail' => $enabled ? 'Tracking runs once visitors accept cookies.' : 'No tracking scripts load.'],
            $row('Google Tag Manager', config('urbanhaven.analytics.gtm_id'), 'Add a GTM-XXXX container ID.'),
            $row('Google Analytics 4', config('urbanhaven.analytics.ga4_id'), 'Add a G-XXXX measurement ID.'),
            $row('Meta Pixel', config('urbanhaven.analytics.meta_pixel_id'), 'Add a Pixel ID.'),
            $row('Google Ads', config('urbanhaven.analytics.google_ads_id'), 'Add an AW-XXXX conversion ID, or set it up in Tag Manager.'),
            (bool) Setting::get('consent_banner_enabled', true)
                ? ['label' => 'Consent banner', 'state' => 'On', 'tone' => 'success', 'detail' => 'Tracking waits for consent (cookie “'.$consent.'”).']
                : ['label' => 'Consent banner', 'state' => 'Off', 'tone' => 'warn', 'detail' => 'Tracking loads for every visitor without asking.'],
        ];
    }

    private function storeBrandImage(UploadedFile $file, string $key): string
    {
        $name = Str::slug(str_replace('_', '-', $key)).'-'.Str::lower(Str::random(10)).'.'.$file->extension();

        return $file->storeAs(self::BRANDING_DIRECTORY, $name, 'public');
    }

    private function deleteBrandImage(string $path): void
    {
        if ($path !== '' && str_starts_with($path, self::BRANDING_DIRECTORY.'/')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function userCanManage(): bool
    {
        return (bool) request()->user()?->isOwnerAdmin();
    }
}
