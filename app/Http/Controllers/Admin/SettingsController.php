<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\LocationArea;
use App\Models\PropertyType;
use App\Models\Setting;
use App\Support\HealthStatus;
use App\Support\PhoneNumber;
use App\Support\SettingsSchema;
use App\Support\TaggedCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function index(): View
    {
        abort_unless($this->userCanManage(), 403);

        return view('admin.settings.index', [
            'definitions' => collect(SettingsSchema::definitions())->groupBy('group', preserveKeys: true),
            'values' => Setting::allValues(),
            'areas' => LocationArea::query()->orderBy('name')->get(),
            'types' => PropertyType::query()->orderBy('label')->get(),
            'amenities' => Amenity::query()->orderBy('label')->get(),
            'mailConfigured' => HealthStatus::mailConfigured(),
            'profiles' => PropertyType::PROFILES,
            'categories' => PropertyType::CATEGORIES,
        ]);
    }

    public function update(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($this->userCanManage(), 403);
        $definitions = SettingsSchema::definitions();
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
        $rules['settings.social_links.*'] = ['url:https', 'max:255'];

        $validated = validator(['settings' => $input], $rules, [], collect($definitions)->mapWithKeys(fn (array $definition, string $key): array => ['settings.'.$key => strtolower($definition['label'])])->all())
            ->after(function ($validator) use ($input): void {
                if (! $input['enable_sale'] && ! $input['enable_rent']) {
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

    public function storeArea(Request $request): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $validated = $request->validate(['name' => ['required', 'string', 'max:120'], 'city' => ['required', 'string', 'max:120']]);
        LocationArea::query()->create($validated + ['is_active' => true]);
        TaggedCache::flush(['search', 'homepage']);

        return back()->with('status', 'Area added.');
    }

    public function updateArea(Request $request, LocationArea $area): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $bounds = config('urbanhaven.maps.bounds');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'intro' => ['nullable', 'string', 'max:2000'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'lat' => ['nullable', 'required_with:lng', 'numeric', 'between:'.$bounds['south'].','.$bounds['north']],
            'lng' => ['nullable', 'required_with:lat', 'numeric', 'between:'.$bounds['west'].','.$bounds['east']],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $area->update([...$validated, 'is_active' => $request->boolean('is_active', $area->is_active)]);
        TaggedCache::flush(['search', 'homepage']);

        return back()->with('status', $area->name.' updated.');
    }

    public function deactivateArea(LocationArea $area): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $area->update(['is_active' => false]);

        return back()->with('status', 'Area deactivated.');
    }

    public function storeType(Request $request): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/', 'unique:property_types,key'],
            'label' => ['required', 'string', 'max:80'],
            'category' => ['required', Rule::in(PropertyType::CATEGORIES)],
            'field_profile' => ['required', Rule::in(PropertyType::PROFILES)],
        ]);
        PropertyType::query()->create($validated + ['is_active' => true]);
        TaggedCache::flush(['search', 'homepage']);

        return back()->with('status', 'Type added.');
    }

    public function updateType(Request $request, PropertyType $type): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'category' => ['required', Rule::in(PropertyType::CATEGORIES)],
            'field_profile' => ['required', Rule::in(PropertyType::PROFILES)],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $type->update([...$validated, 'is_active' => $request->boolean('is_active', $type->is_active)]);
        TaggedCache::flush(['search', 'homepage']);

        return back()->with('status', $type->label.' updated.');
    }

    public function deactivateType(PropertyType $type): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $type->update(['is_active' => false]);

        return back()->with('status', 'Type deactivated.');
    }

    public function storeAmenity(Request $request): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $validated = $request->validate(['key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/', 'unique:amenities,key'], 'label' => ['required', 'string', 'max:80']]);
        Amenity::query()->create($validated + ['is_active' => true]);

        return back()->with('status', 'Amenity added.');
    }

    public function deactivateAmenity(Amenity $amenity): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $amenity->update(['is_active' => false]);

        return back()->with('status', 'Amenity deactivated.');
    }

    private function userCanManage(): bool
    {
        return (bool) request()->user()?->isOwnerAdmin();
    }

    private function userCanManageReference(): bool
    {
        $user = request()->user();

        return (bool) ($user?->isOwnerAdmin() || $user?->hasPermission('reference.manage'));
    }
}
