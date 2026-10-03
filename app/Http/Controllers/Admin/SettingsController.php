<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\HealthStatus;
use App\Support\PhoneNumber;
use App\Support\SettingsSchema;
use App\Support\TaggedCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function index(): View
    {
        abort_unless($this->userCanManage(), 403);

        return view('admin.settings.index', [
            'definitions' => $this->definitionsForGroups(['branding', 'contact', 'leads']),
            'values' => Setting::allValues(),
            'mailConfigured' => HealthStatus::mailConfigured(),
        ]);
    }

    public function listings(): View
    {
        abort_unless($this->userCanManage(), 403);

        return view('admin.settings.listings', [
            'definitions' => $this->definitionsForGroups(['listings']),
            'values' => Setting::allValues(),
        ]);
    }

    public function update(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($this->userCanManage(), 403);
        $requestedGroups = array_values(array_filter((array) $request->input('groups', [])));
        $definitions = $requestedGroups === []
            ? SettingsSchema::definitions()
            : array_filter(
                SettingsSchema::definitions(),
                fn (array $definition): bool => in_array($definition['group'], $requestedGroups, true)
            );
        $input = (array) $request->input('settings', []);

        foreach ($definitions as $key => $definition) {
            if ($definition['input'] === 'checkbox') {
                $input[$key] = filter_var($input[$key] ?? false, FILTER_VALIDATE_BOOLEAN);
            }

            if ($definition['input'] === 'lines') {
                $input[$key] = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) ($input[$key] ?? '')))));
            }
        }

        $rules = collect($definitions)->mapWithKeys(fn (array $definition, string $key): array => ['settings.'.$key => $definition['rules']])->all();
        if (isset($definitions['social_links'])) {
            $rules['settings.social_links.*'] = ['url:https', 'max:255'];
        }

        $validated = validator(['settings' => $input], $rules, [], collect($definitions)->mapWithKeys(fn (array $definition, string $key): array => ['settings.'.$key => strtolower($definition['label'])])->all())
            ->after(function ($validator) use ($input, $definitions): void {
                if (isset($definitions['enable_sale'], $definitions['enable_rent']) && ! ($input['enable_sale'] ?? false) && ! ($input['enable_rent'] ?? false)) {
                    $validator->errors()->add('settings.enable_sale', 'Keep at least one of sale or rent switched on.');
                }
            })
            ->validate()['settings'];

        $before = Setting::allValues();
        $changed = [];

        foreach ($definitions as $key => $definition) {
            $value = $validated[$key] ?? null;

            if (in_array($definition['input'], ['tel'], true) && filled($value)) {
                $value = PhoneNumber::normalize((string) $value);
            }

            if (($before[$key] ?? null) !== $value) {
                $changed[] = $key;
            }

            Setting::set($key, $value ?? '', $definition['group'], $definition['cast']);
        }

        if ($changed !== []) {
            $auditLogger->record($request->user()->id, 'settings.updated', Setting::class, null, null, ['keys' => $changed], $request->ip());
            TaggedCache::flush(['homepage', 'properties', 'search', 'projects', 'sitemap']);
        }

        return back()->with('status', $changed === [] ? 'No changes to save.' : 'Settings saved.');
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
     * @param  list<string>  $groups
     * @return Collection<string, Collection<string, array<string, mixed>>>
     */
    private function definitionsForGroups(array $groups): Collection
    {
        return collect(SettingsSchema::definitions())
            ->filter(fn (array $definition): bool => in_array($definition['group'], $groups, true))
            ->groupBy('group', preserveKeys: true);
    }

    private function userCanManage(): bool
    {
        return (bool) request()->user()?->isOwnerAdmin();
    }
}
