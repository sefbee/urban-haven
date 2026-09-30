# Implementation Plan: Urban Haven Platform (F00–F24)

## Overview

Implement all 25 features in strict dependency order (F00 → F24) on the existing Laravel 13 / PHP 8.3+ skeleton. Business logic lives exclusively in `app/Services` and `app/Policies`; Blade templates contain only presentation logic. No Vue/React/SPA — Blade + Alpine.js + Tailwind CSS only. MySQL-backed search only (no Elasticsearch). Lead capture is always transactional: DB commit before queue dispatch.

---

## Tasks

- [x] 1. F00 — Bootstrap and Foundation
  - Wire `AuditLogger` contract to `DatabaseAuditLogger` in `AppServiceProvider::register()`
  - Bind all service contracts (`InventoryService`, `LeadService`, `SearchService`, `MediaService`) in `AppServiceProvider`
  - Register all policy mappings in `AppServiceProvider::boot()` via `Gate::policy()` for `Property`, `Project`, `Lead`, `CmsPage`, and `User`
  - Add `before()` gate hook in `AppServiceProvider::boot()` so `owner_admin` implicitly passes all checks
  - Register `EnsureStaffIsActive` and `EnsureOwnerMfa` as named middleware aliases in `bootstrap/app.php`
  - Apply the middleware stack (`auth`, `EnsureStaffIsActive`, `EnsureOwnerMfa`) to the `/admin` route group in `routes/web.php`
  - Configure Tailwind CSS and Alpine.js in `vite.config.js` and `resources/css/app.css`; confirm `@vite` directive is in the base layout
  - Verify `composer run setup` installs cleanly: `php artisan migrate` runs all four existing migrations without error
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [x] 2. F01 — Staff Authentication and RBAC
  - [x] 2.1 Create `database/seeders/RolesPermissionsSeeder.php` that seeds the three roles (`owner_admin`, `content_editor`, `sales_user`) and all `resource.action` permission keys, then attaches permissions to roles; call it from `DatabaseSeeder`
    - _Requirements: 3.5_
  - [x] 2.2 Create `app/Http/Controllers/Admin/Auth/LoginController.php` with `showLogin` and `login` POST action; `login` delegates credential check to `Auth::attempt`, calls `StaffService::markLogin` on success and `StaffService::recordFailedLogin` on failure, then redirects to `admin.dashboard`
    - Apply `throttle:5,1` rate-limit middleware to the login POST route (5 attempts per minute per IP)
    - _Requirements: 2.1, 2.2, 2.10_
  - [x] 2.3 Create login Blade view at `resources/views/admin/auth/login.blade.php` with email/password form; display validation errors; no JavaScript framework
    - _Requirements: 2.1, 2.2_
  - [x] 2.4 Create `app/Http/Controllers/Admin/Auth/MfaController.php` with `enroll` (GET/POST), `challenge` (GET), `verify` (POST), and `recoveryCodes` (GET) actions; all MFA logic delegated to `MfaService`
    - On successful TOTP verify: `session(['mfa_passed' => true])` and record `auth.mfa_verified` audit entry
    - On recovery code use: call `MfaService::consumeRecoveryCode`; mark code used; set `mfa_passed`
    - _Requirements: 2.3, 2.4, 2.5_
  - [x] 2.5 Create MFA Blade views: `admin/auth/mfa/enroll.blade.php` (shows QR SVG and secret), `admin/auth/mfa/challenge.blade.php` (TOTP input), `admin/auth/mfa/recovery-codes.blade.php`
    - _Requirements: 2.3, 2.4, 2.5_
  - [x] 2.6 Create `app/Http/Controllers/Admin/StaffController.php` with `index`, `create`, `store`, `edit`, `update`, `deactivate` actions; delegate all mutations to `StaffService`; `deactivate` purges sessions and records audit
    - Create `app/Http/Requests/Admin/StoreStaffRequest.php` and `UpdateStaffRequest.php` with `authorize()` calling `StaffPolicy`
    - Create `app/Policies/StaffPolicy.php` with `view`, `create`, `update`, `deactivate` methods
    - _Requirements: 2.7, 2.9_
  - [x] 2.7 Create staff management Blade views: `admin/staff/index.blade.php`, `admin/staff/create.blade.php`, `admin/staff/edit.blade.php`
    - _Requirements: 2.7_
  - [x]* 2.8 Write feature tests in `tests/Feature/Auth/` covering: successful login sets session, failed login records audit and returns error, rate limit returns 429 on 6th attempt, deactivated user is redirected, owner_admin without MFA is redirected to enroll, valid TOTP grants mfa_passed, recovery code is single-use (Property 2), `sales_user` gets 403 on staff management endpoints
    - _Requirements: 2.1–2.10; Properties 1, 2, 3, 4, 29_

- [x] 3. F02 — Settings and Reference Data
  - [x] 3.1 Create migration `create_settings_and_reference_tables` with `settings` (group, key UK, value text, cast string), `location_areas` (name, slug UK, city, is_active bool), `property_types` (key UK, label, is_active bool), `amenities` (key UK, label, icon_svg text, is_active bool)
    - _Requirements: 3.1, 3.3_
  - [x] 3.2 Create `app/Models/Setting.php`, `LocationArea.php`, `PropertyType.php`, `Amenity.php` with appropriate fillable/casts; add `scopeActive()` on reference models; add `Setting::get(key)` and `Setting::set(key, value)` static helpers that apply the `cast` column
    - _Requirements: 3.1, 3.2_
  - [x] 3.3 Create `app/Http/Controllers/Admin/SettingsController.php` with `index` and `update` actions; `update` calls `Setting::set` and records audit; restrict to `owner_admin` via policy
    - Create CRUD controllers for `LocationAreaController`, `PropertyTypeController`, `AmenityController` with `index`, `create`, `store`, `edit`, `update`, `destroy` (soft-deactivate, not hard delete)
    - _Requirements: 3.1, 3.2, 3.3, 3.4_
  - [x] 3.4 Create Blade views: `admin/settings/index.blade.php`, `admin/reference/location-areas/index.blade.php`, and equivalent `create`/`edit` views for each reference type
    - _Requirements: 3.1, 3.3, 3.4_
  - [x]* 3.5 Write feature tests: `Setting::set` then `::get` returns correctly cast type (Property 5), inactive area excluded from property-create option list (Property 6), only `owner_admin` can update settings (403 for others)
    - _Requirements: 3.2, 3.4; Properties 5, 6_

- [x] 4. F03 — Media Management
  - [x] 4.1 Create migration `create_media_table` with all columns from design: `mediable_type`, `mediable_id`, `collection`, `disk`, `path`, `original_filename`, `mime_type`, `size_bytes`, `width`, `height`, `sort_order`, `alt_texts` (json), timestamps; add polymorphic index on `(mediable_type, mediable_id)`
    - _Requirements: 4.4, 4.5_
  - [x] 4.2 Create `app/Models/Media.php` with `mediable` morphTo relation; `alt_texts` cast to array; add `HasMedia` trait/interface for polymorphic parent models
    - _Requirements: 4.4, 4.5_
  - [x] 4.3 Create `app/Contracts/MediaService.php` interface and `app/Services/Media/MediaService.php` implementation
    - `store()`: validate size ≤ 8192 KB and MIME in `[image/jpeg, image/png, image/webp, image/gif]` using file content inspection (`finfo`), not extension; reject executable types with 422; strip EXIF via Intervention/Image; generate derivatives at widths `[480, 768, 1280, 1920]` in WebP using `config('urbanhaven.media.derivative_widths')`; persist media record in DB transaction; no orphaned files if DB insert fails
    - `reorder()`: update `sort_order` for each ID to match submitted array position within a transaction
    - `delete()`: delete DB record, remove all derivative files from disk, record audit entry
    - _Requirements: 4.1–4.7; Req 24.4, 24.5_
  - [x] 4.4 Create `app/Http/Controllers/Admin/MediaController.php` with `store` (POST, returns JSON `{id, url, thumb_url}`), `reorder` (PUT), `destroy` (DELETE) actions; `store` calls `MediaService::store`; all actions call `$this->authorize()`
    - Create `app/Http/Requests/Admin/StoreMediaRequest.php` (file validation rules as backup to service-layer checks)
    - _Requirements: 4.1, 4.6, 4.7_
  - [x]* 4.5 Write feature tests: valid upload creates media record and derivative files exist (Property 7), oversized file returns 422 with no media record (Property 8), wrong MIME returns 422, reorder updates sort_order to match submitted order (Property 9), delete removes record and files, EXIF stripping test
    - _Requirements: 4.1–4.7; Properties 7, 8, 9_

- [x] 5. F04 — Inventory Management (Projects, Properties, Units)
  - [x] 5.1 Install `spatie/laravel-sluggable` via composer; create migration `create_inventory_tables` with `projects`, `properties`, `units` tables per design ERD including all FK constraints, JSON columns (`amenity_ids`), and decimal precision; add composite indexes `(location_area_id, property_type_id, price)` and `(status, published_at)` on `properties`
    - _Requirements: 5.1, 5.3_
  - [x] 5.2 Create `app/Models/Project.php`, `Property.php`, `Unit.php` with Eloquent relations (`Project hasMany Property`, `Property hasMany Unit`, `Property belongsTo PropertyType`, `Property belongsTo LocationArea`), `HasSlug` from spatie/sluggable, `area_sqft` auto-computed on save via `saving` model event calling `AreaConverter::toSqft`, `HasMedia` morph relation
    - Add `published()` scope on `Property` and `Project` that joins `publication_states` where `status = published`
    - _Requirements: 5.2, 5.3, 5.4_
  - [x] 5.3 Create `app/Contracts/InventoryService.php` interface and `app/Services/Inventory/InventoryService.php` implementation
    - `createProperty`, `updateProperty`, `deleteProperty` must wrap in `DB::transaction`; on slug change, auto-create a `redirects` record; record audit entry for every mutation
    - `createProject`, `updateProject`, `deleteProject` with same transactional and audit requirements
    - `createUnit`, `updateUnit`, `deleteUnit` must validate `property_id` foreign key
    - _Requirements: 5.1, 5.5, 5.6, 5.8_
  - [x] 5.4 Create `app/Policies/PropertyPolicy.php` and `ProjectPolicy.php` with `view`, `create`, `update`, `delete`, `publish` methods; return `false` for users without matching permission key who are not `owner_admin`
    - _Requirements: 5.7_
  - [x] 5.5 Create admin controllers: `Admin\PropertyController` (index, create, store, edit, update, destroy), `Admin\ProjectController`, `Admin\UnitController`; all use typed `FormRequest` classes with `authorize()` delegating to the appropriate policy; all mutations go through `InventoryService`
    - Create `StorePropertyRequest`, `UpdatePropertyRequest`, `StoreProjectRequest`, `UpdateProjectRequest`, `StoreUnitRequest` in `app/Http/Requests/Admin/`
    - _Requirements: 5.1–5.8_
  - [x] 5.6 Create Blade views: `admin/properties/index.blade.php`, `admin/properties/create.blade.php`, `admin/properties/edit.blade.php`, and equivalent for projects and units; option lists use `scopeActive()` for location areas, property types, and amenities
    - _Requirements: 3.4, 5.1_
  - [x]* 5.7 Write feature tests: `content_editor` can create/update property, `sales_user` gets 403 on create/delete, slug uniqueness produces `-2` suffix (Property 11), `area_sqft` computed correctly from `area_value` + `area_unit` (Property 10), slug change creates redirect record, unit requires valid `property_id`, audit entries created on CRUD
    - _Requirements: 5.1–5.8; Properties 10, 11_

- [x] 6. F05 — Publication and Editorial Workflow
  - [x] 6.1 Create migration `create_publication_tables` with `publication_states` (`publishable_type`, `publishable_id` polymorphic, `status` enum, `published_by` FK, `published_at`, `unpublished_at`, `unpublish_reason`) and `redirects` (`from_path` UK, `to_path`, `http_code`, `is_active`) tables
    - _Requirements: 6.1, 5.5_
  - [x] 6.2 Create `app/Models/PublicationState.php` (polymorphic `MorphTo`), `Redirect.php`; add `publicationState()` `morphOne` relation to `Property` and `Project`
    - _Requirements: 6.1_
  - [x] 6.3 Implement publication workflow in `InventoryService`: `submitForReview`, `approve`, `publishProperty`/`publishProject`, `unpublishProperty`/`unpublishProject` methods
    - Enforce state machine: reject any out-of-sequence transition with a `ValidationException`; wrap state updates in `DB::transaction`; flush Redis cache tags (`properties`, `property:{id}` or `projects`, `project:{id}`) after every publish/unpublish; `unpublish` requires non-empty `unpublish_reason`
    - _Requirements: 6.1–6.8_
  - [x] 6.4 Create `app/Http/Controllers/Admin/PublicationController.php` with `submitForReview`, `approve`, `publish`, `unpublish` actions; each calls `$this->authorize('publish', $property)` or equivalent; delegates to `InventoryService`
    - Create `UnpublishRequest` with `unpublish_reason` required validation
    - _Requirements: 6.1–6.8_
  - [x]* 6.5 Write feature tests: `draft → pending_review → approved → published → unpublished` transitions all succeed, invalid transition (e.g., draft → published) returns 422 (Property 12), unpublish without reason returns validation error, publish flushes correct cache tags (Property 13), `sales_user` gets 403 on publish
    - _Requirements: 6.1–6.8; Properties 12, 13_

- [x] 7. F06 — Public Website Layout
  - [x] 7.1 Create `resources/views/layouts/public.blade.php` as the Blade master layout with `@stack('head')`, nav, `@yield('content')`, footer, `@vite(['resources/css/app.css', 'resources/js/app.js'])`; set `<html lang="{{ app()->getLocale() }}">`
    - Add responsive navigation with Tailwind CSS and Alpine.js hamburger toggle; include WhatsApp click-to-chat link from `config('urbanhaven.whatsapp.number')`
    - _Requirements: 7.1, 7.2, 7.4_
  - [x] 7.2 Add Laravel localization files for `en` and `bn` locales under `resources/lang/`; add locale-switch route and session persistence; create `app/Http/Middleware/SetLocale.php` and register it on web routes
    - _Requirements: 7.3, 7.4_
  - [x] 7.3 Create `resources/views/layouts/admin.blade.php` master layout for admin panel with sidebar navigation, notification bell slot, and user menu; extend with Alpine.js for sidebar collapse
    - _Requirements: 7.1_
  - [x]* 7.4 Write feature tests: public layout includes `@vite` assets, `lang` attribute matches active locale, locale switch persists across requests
    - _Requirements: 7.1–7.4_

- [x] 8. F07 — Property Search
  - [x] 8.1 Create `app/Contracts/SearchService.php` interface and `app/Services/Search/SearchService.php` implementation
    - `search(array $filters, int $page, int $perPage): LengthAwarePaginator`: build query on `Property::query()->published()->with([...])`, apply each filter with null guards, cache result for 300 seconds under cache tag `properties` using key `search:` + `md5(serialize(filters+page+perPage))`; enforce `perPage` ∈ [1, 24]
    - _Requirements: 8.1–8.7_
  - [x] 8.2 Create `app/Http/Controllers/Public/PropertySearchController.php` with `index` (GET `/search` or `/properties`) action; use `SearchRequest` FormRequest for filter validation; pass paginated results to Blade
    - Create `app/Http/Requests/Public/SearchRequest.php` validating all filter params
    - _Requirements: 8.1–8.7_
  - [x] 8.3 Create `resources/views/public/properties/index.blade.php` with filter sidebar (Alpine.js for mobile collapse), property card grid, and pagination links; price via `MoneyFormatter::formatBdt`, area via `AreaConverter::format`
    - _Requirements: 8.1–8.7_
  - [x]* 8.4 Write feature tests: search returns only published records regardless of filter (Property 14), adding a filter never increases result count (Property 15), all 8 filter types reduce results correctly, empty filter set returns all published, cache returns same result on second call, `perPage > 24` is rejected with 422, pagination metadata always present
    - _Requirements: 8.1–8.7; Properties 14, 15_

- [x] 9. F08 — Property Detail Page
  - [x] 9.1 Create `app/Http/Controllers/Public/PropertyController.php` with `show(string $slug)` action: resolve property via `Property::where('slug', $slug)->published()->firstOr(...)`, handle redirects table lookup, return 404 for unpublished/missing; cache response for 600 s under tag `property:{id}`
    - _Requirements: 9.1, 9.2, 9.3, 9.6_
  - [x] 9.2 Create `resources/views/public/properties/show.blade.php` extending `layouts.public`; display all property fields; price via `MoneyFormatter::formatBdt` (Requirements 9.4), area via `AreaConverter::format` (Requirements 9.5); image gallery with srcset at derivative widths; bilingual alt text via `alt_texts.en`/`alt_texts.bn`; inline lead enquiry form
    - _Requirements: 9.1–9.6_
  - [x]* 9.3 Write feature tests: published property returns 200 with all fields, unpublished slug returns 404 (Property 16), redirected slug returns 301/302, price null shows 'Price on request' (Property 17), detail page cached under correct tag
    - _Requirements: 9.1–9.6; Properties 16, 17_

- [x] 10. F09 — Projects Public Listing and Detail
  - [x] 10.1 Create `app/Http/Controllers/Public/ProjectController.php` with `index` and `show(string $slug)` actions; `index` returns published projects cached under `projects` tag (300 s); `show` returns published project with its published properties; both return 404 for unpublished/missing
    - _Requirements: 10.1–10.5_
  - [x] 10.2 Create `resources/views/public/projects/index.blade.php` and `show.blade.php`; listing shows featured media, name, city, area, developer, completion date; detail page shows linked published properties as cards
    - _Requirements: 10.1–10.5_
  - [x]* 10.3 Write feature tests: listing excludes unpublished projects, detail returns 404 for unpublished, only published properties shown on project detail, responses cached under `projects` tag
    - _Requirements: 10.1–10.5_

- [x] 11. F10 — Map View
  - [x] 11.1 Add Leaflet.js to `package.json`; import and initialise in `resources/js/app.js`; add map tile URL and attribution from `config('urbanhaven.maps')`
    - _Requirements: 11.2_
  - [x] 11.2 Create a `resources/views/public/properties/map.blade.php` partial (or embed in search page) that passes published property coordinates to Leaflet via a `data-properties` JSON attribute; round lat/lng to `config('urbanhaven.maps.approximate_decimals')` (2 decimal places) before embedding; popup shows name, type, price, and detail link
    - _Requirements: 11.1, 11.3, 11.4_
  - [x]* 11.3 Write feature tests: map data endpoint returns only published properties, coordinates rounded to max 2 decimal places (Property 18)
    - _Requirements: 11.1, 11.4; Property 18_

- [x] 12. F11 — Compare and Shortlist
  - [x] 12.1 Create `app/Http/Controllers/Public/ShortlistController.php` with `add` (POST), `remove` (DELETE), `index` (GET `/compare`) actions; shortlist stored in `session('shortlist', [])`; max 4 items enforced in controller — return JSON error when limit reached; `index` loads property models and renders comparison table
    - _Requirements: 12.1–12.4_
  - [x] 12.2 Create `resources/views/public/compare.blade.php` with side-by-side attribute rows (price, area, bedrooms, bathrooms, floor, furnished, amenities); use Alpine.js for "Add to Shortlist" toggle on property cards across listing and detail pages
    - _Requirements: 12.4_
  - [x]* 12.3 Write feature tests: shortlist persists in session, max 4 enforced with informative message, remove updates session, compare view shows aligned attribute rows for 2–4 properties
    - _Requirements: 12.1–12.4_

- [x] 13. F12 — Similar Properties
  - [x] 13.1 Create `app/Contracts/SimilarPropertiesService.php` interface and `app/Services/Search/SimilarPropertiesService.php` implementation
    - `similar(Property $property, int $limit = 6): Collection`: tier 1 = same `location_area_id`, `property_type_id`, price within `±PRICE_BAND_PERCENT%`; tier 2 = same city + type, fills remaining slots; never include source property; only published; `PRICE_BAND_PERCENT` from `config('urbanhaven.search.price_band_percent')`
    - _Requirements: 13.1–13.5_
  - [x] 13.2 Inject `SimilarPropertiesService` into `PropertyController::show`; pass result to `show.blade.php` and render as a horizontal card strip
    - _Requirements: 13.1–13.5_
  - [x]* 13.3 Write feature tests: returns ≤ 6 results (Property 19), never includes source property, all results published, tier 2 fills when tier 1 insufficient, returns only published properties
    - _Requirements: 13.1–13.5; Property 19_

- [x] 14. F13 — Lead Capture
  - [x] 14.1 Create migration `create_leads_tables` with `leads` (all columns from design ERD including `phone_hash` computed column or application-level hash, `utm_*` fields, `ip_address`, `status` enum) and `lead_notes` tables; add composite index `(phone_hash, property_id, created_at)` on `leads`
    - _Requirements: 14.1, 14.3, 14.4_
  - [x] 14.2 Create `app/Models/Lead.php` with `belongsTo` relations to `Property`, `Project`, `User` (assigned_to); `HasMany` `LeadNote`; add `phone_hash` attribute that stores `hash('sha256', $phone)` on set; create `app/Models/LeadNote.php`
    - _Requirements: 14.1–14.4_
  - [x] 14.3 Create `app/Contracts/LeadService.php` interface and `app/Services/Lead/LeadService.php` implementation
    - `capture(array $validated, Request $request): Lead`: check duplicate by `phone_hash + property_id + created_at >= now()-repeat_window`; if found return existing lead (no notification); else `DB::transaction(fn => Lead::create([...]))` then `dispatch(NotifyNewLeadJob($lead->id))` and `dispatch(AttributeLeadSourceJob($lead->id))` **outside** the transaction; store `utm_source/medium/campaign` and `ip_address`
    - `assign`, `addNote`, `scheduleFollowUp` methods
    - _Requirements: 14.1–14.6_
  - [x] 14.4 Create `app/Http/Controllers/Public/LeadController.php` with `store` action; apply `throttle:10,1` rate-limit on the route; delegate to `LeadService::capture`; return JSON 201 on success, 500 on DB failure with user-friendly message
    - Create `app/Http/Requests/Public/LeadCaptureRequest.php` validating BD phone format (regex), required name, optional email/message, at least one of property_id/project_id
    - _Requirements: 14.1, 14.5, 14.6_
  - [x] 14.5 Create `app/Jobs/NotifyNewLeadJob.php` (queued) that loads the lead, determines recipients (assigned user or all `sales_user`s), and calls `recipient->notify(new NewLeadNotification($lead))`; create `app/Jobs/AttributeLeadSourceJob.php` stub
    - Set `tries = 3`, exponential backoff on both jobs
    - _Requirements: 17.1, 17.5_
  - [x]* 14.6 Write feature tests: lead persisted before job dispatch, duplicate within window returns existing lead with no new record (Property 20), UTM params stored correctly (Property 21), rate limit returns 429 on 11th request (Property 30), DB failure returns 500 with friendly message, lead note creation
    - _Requirements: 14.1–14.6; Properties 20, 21, 30_

- [x] 15. F14 — Site Visit Requests
  - [x] 15.1 Create migration `create_site_visit_requests_table` with all columns from design ERD (`lead_id` FK, `property_id`, `project_id`, `preferred_at`, `status` default `pending`, `assigned_to` FK, `notes`)
    - _Requirements: 15.1, 15.2_
  - [x] 15.2 Create `app/Models/SiteVisitRequest.php` with `belongsTo` relations; add status constants (`pending`, `confirmed`, `completed`, `cancelled`)
    - _Requirements: 15.2, 15.3_
  - [x] 15.3 Create `app/Services/Lead/SiteVisitService.php` with `create(array $validated, Request $request): SiteVisitRequest` (creates or reuses lead, creates visit record, dispatches notification) and `updateStatus(SiteVisitRequest $visit, string $status, User $actor): void` (enforces allowed transitions, throws `ValidationException` on invalid)
    - Allowed transitions: `pending→confirmed`, `pending→cancelled`, `confirmed→completed`, `confirmed→cancelled`
    - _Requirements: 15.1–15.4_
  - [x] 15.4 Create `app/Http/Controllers/Public/SiteVisitController.php` (`store`) and `app/Http/Controllers/Admin/SiteVisitController.php` (`index`, `updateStatus`); FormRequests for both
    - _Requirements: 15.1–15.4_
  - [x]* 15.5 Write feature tests: site visit created with `pending` status, valid transitions succeed, invalid transition returns validation error (e.g., `pending→completed`), notification dispatched on create
    - _Requirements: 15.1–15.4_

- [x] 16. F15 — Sales Workflow
  - [x] 16.1 Create migration `create_lead_follow_ups_table` with all columns from design ERD (`lead_id`, `user_id`, `action_type` enum, `notes`, `scheduled_at`, `completed_at`)
    - _Requirements: 16.1, 16.2_
  - [x] 16.2 Create `app/Models/LeadFollowUp.php` with `belongsTo` `Lead` and `User`; add `action_type` enum validation at model level or via FormRequest
    - _Requirements: 16.2_
  - [x] 16.3 Add `scheduleFollowUp(Lead, array, User): LeadFollowUp` and `completeFollowUp(LeadFollowUp, User): void` methods to `LeadService`; `completeFollowUp` sets `completed_at = now()`; `assign(Lead, User $assignee, User $actor)` updates `leads.assigned_to` and records audit with old/new assignee
    - _Requirements: 16.1–16.5_
  - [x] 16.4 Create `app/Http/Controllers/Admin/LeadController.php` with `index`, `show`, `assign`, `updateStatus`, `addFollowUp`, `completeFollowUp` actions; `LeadPolicy` for authorization
    - Create `app/Policies/LeadPolicy.php` with `view`, `assign`, `export` methods
    - Create `StoreFollowUpRequest`, `AssignLeadRequest`, `UpdateLeadStatusRequest` FormRequests
    - _Requirements: 16.1–16.5_
  - [x] 16.5 Create Blade views: `admin/leads/index.blade.php` (filterable by status, UTM source, UTM campaign — Requirements 23.4), `admin/leads/show.blade.php` (timeline of notes and follow-ups, assign form, status update form)
    - _Requirements: 16.1–16.5, 23.4_
  - [x]* 16.6 Write feature tests: follow-up created with valid action_type (Property 22), invalid action_type returns 422, `completeFollowUp` sets `completed_at`, lead assignment records audit with old/new assignee, lead status progression, `sales_user` cannot assign leads they don't own (policy test)
    - _Requirements: 16.1–16.5; Property 22_

- [x] 17. Checkpoint — Ensure all tests pass
  - Run `php artisan test` and confirm all Feature and Unit tests pass. Resolve any failures before proceeding to F16.

- [x] 18. F16 — Notifications
  - [x] 18.1 Create Laravel notifications table migration (`php artisan notifications:table` equivalent — add to a named migration); create `app/Notifications/NewLeadNotification.php` and `SiteVisitNotification.php` implementing `toMail()` and `toDatabase()` channels
    - _Requirements: 17.3, 17.4_
  - [x] 18.2 Complete `NotifyNewLeadJob::handle()`: load lead with relations, select recipients per design pseudocode, call `$recipient->notify(new NewLeadNotification($lead))`; retry 3 times with exponential backoff; failure must not affect lead record
    - _Requirements: 17.1, 17.5_
  - [x] 18.3 Create `app/Http/Controllers/Admin/NotificationController.php` with `index` (list unread) and `markRead` (PATCH) actions; `markRead` sets `notifications.read_at = now()`
    - _Requirements: 17.3, 18.3_
  - [x] 18.4 Add notification bell component to `layouts/admin.blade.php` using Alpine.js showing unread count; poll or fetch via AJAX for updated count; list renders via partial `admin/notifications/_bell.blade.php`
    - _Requirements: 18.2, 18.3_
  - [x] 18.5 Create `resources/views/emails/new-lead.blade.php` and `site-visit-confirmation.blade.php` Mailable templates
    - _Requirements: 17.4_
  - [x]* 18.6 Write feature tests: new lead dispatches `NotifyNewLeadJob`, notification stored in `notifications` table for correct recipients, `markRead` sets `read_at`, job retries on failure without affecting lead
    - _Requirements: 17.1–17.5_

- [x] 19. F17 — Admin Dashboard
  - [x] 19.1 Create `app/Http/Controllers/Admin/DashboardController.php` with `index` action; query summary counts based on authenticated user's role: `new` leads count and scheduled site visits for `sales_user`; pending review count for `content_editor`/`owner_admin`; recent leads list (all for `owner_admin`, assigned-only for `sales_user`)
    - _Requirements: 18.1, 18.4_
  - [x] 19.2 Create `resources/views/admin/dashboard.blade.php` with role-appropriate metric cards, recent leads table, and unread notification bell count; use `DisplayTimezone::format` for all timestamps
    - _Requirements: 18.1–18.4_
  - [x]* 19.3 Write feature tests: `sales_user` sees own lead counts, `owner_admin` sees all leads, `markRead` decrements bell count (by re-fetching), dashboard 200 for all three roles
    - _Requirements: 18.1–18.4_

- [x] 20. F18 — Lead Export
  - [x] 20.1 Install `league/csv` via composer; create `app/Http/Controllers/Admin/LeadExportController.php` with `export` action that streams a CSV response via `League\Csv\Writer`; filter by `created_at` date range (inclusive); columns in exact design order: `name, phone, email, property_title, project_name, source, utm_source, utm_medium, utm_campaign, status, assigned_to, created_at`; restrict to users with `lead.export` permission via `LeadPolicy::export()`
    - _Requirements: 19.1–19.4_
  - [x] 20.2 Add export button and date-range form to `admin/leads/index.blade.php`
    - _Requirements: 19.1_
  - [x]* 20.3 Write feature tests: export with date range contains only leads within range (Property 23), export without filter contains all leads, columns in correct order, user without `lead.export` permission gets 403
    - _Requirements: 19.1–19.4; Property 23_

- [x] 21. F19 — CMS Pages and Content Blocks
  - [x] 21.1 Install `stevebauman/purify` (HTMLPurifier wrapper) via composer; create migration `create_cms_tables` with `cms_pages` (slug UK, title, body text, meta_title, meta_description, status, created_by FK, updated_by FK) and `cms_blocks` (key UK, label, content json) tables
    - _Requirements: 20.1, 20.3_
  - [x] 21.2 Create `app/Models/CmsPage.php` and `CmsBlock.php`; add `scopePublished()` on `CmsPage`; add `PublicationState` morphOne to CmsPage
    - _Requirements: 20.1, 20.4_
  - [x] 21.3 Create `app/Services/Cms/CmsService.php` with `createPage`, `updatePage`, `deletePage` (validates HTML through HTMLPurifier before save — Requirement 20.2), `updateBlock` (records audit entry)
    - _Requirements: 20.1–20.5_
  - [x] 21.4 Create `app/Policies/CmsPagePolicy.php` with `view`, `create`, `update`, `delete` methods; create `Admin\CmsPageController` and `Admin\CmsBlockController` with CRUD actions and `CmsPagePolicy` authorization
    - Create `StoreCmsPageRequest`, `UpdateCmsPageRequest` FormRequests with `authorize()`
    - _Requirements: 20.1–20.5_
  - [x] 21.5 Create public route `/{slug}` that resolves `CmsPage::where('slug', $slug)->published()->firstOrFail()` and renders `resources/views/public/cms/show.blade.php`; render `body` via `{!! $page->body !!}` (purified on save)
    - _Requirements: 20.4, 20.5_
  - [x] 21.6 Create Blade views: `admin/cms/pages/index.blade.php`, `create.blade.php`, `edit.blade.php`; `admin/cms/blocks/index.blade.php`, `edit.blade.php`; rich-text textarea for `body` field
    - _Requirements: 20.1–20.3_
  - [x]* 21.7 Write feature tests: disallowed HTML tags stripped from body on save (Property 24), CMS page published at `/{slug}` returns 200, deleted page returns 404, block update records audit, `sales_user` gets 403 on CMS CRUD
    - _Requirements: 20.1–20.5; Property 24_

- [x] 22. F20 — Homepage
  - [x] 22.1 Create `app/Http/Controllers/Public/HomeController.php` with `index` action; load featured projects and properties via settings/CMS block flags; load `hero`, `about`, `contact_details` CMS blocks by key; cache full response in Redis (tag `homepage`, TTL 600 s)
    - _Requirements: 21.1–21.4_
  - [x] 22.2 Create `resources/views/public/home.blade.php` extending `layouts.public`; render featured project cards (name, image, city, link), featured property cards (title, image, price via `MoneyFormatter`, area via `AreaConverter`, link), and CMS blocks
    - _Requirements: 21.1–21.3_
  - [x]* 22.3 Write feature tests: homepage returns 200, only published featured items shown, CMS block content rendered, response served from cache on second request
    - _Requirements: 21.1–21.4_

- [x] 23. F21 — SEO and Sitemap
  - [x] 23.1 Create migration `create_seo_overrides_table` with `seo_overrides` (`seoable_type`, `seoable_id` polymorphic, `meta_title`, `meta_description`, `og_image_path`, `noindex` bool); add `seoOverride` morphOne to `Property`, `Project`, `CmsPage`
    - _Requirements: 22.1, 22.2_
  - [x] 23.2 Create `app/Support/SeoMeta.php` value object and a `@include('partials.seo-meta', ['model' => $model])` Blade partial that renders `<meta name="title">`, `<meta name="description">`, `<meta property="og:image">`, and conditionally `<meta name="robots" content="noindex">`; override values take priority over model defaults (Property 26)
    - _Requirements: 22.1, 22.2_
  - [x] 23.3 Create `app/Services/Seo/SitemapGenerator.php` implementing the pseudocode from design: static routes + published properties + published projects + published CMS pages; cache under tag `sitemap` for 3600 s; flush `sitemap` tag on any publish/unpublish event
    - _Requirements: 22.3–22.6_
  - [x] 23.4 Create route `GET /sitemap.xml` handled by `app/Http/Controllers/Public/SitemapController.php`; respond with XML content-type; include `<loc>`, `<lastmod>`, `<changefreq>`, `<priority>` per design
    - _Requirements: 22.3, 22.4, 22.5_
  - [x] 23.5 Add admin UI for editing `seo_overrides` as a tab on the property/project edit pages (inline sub-form)
    - _Requirements: 22.1, 22.6_
  - [x]* 23.6 Write feature tests: sitemap XML contains only published slugs (Property 25), sitemap cached and re-served from cache, unpublish flushes sitemap cache, SEO override values used over model defaults (Property 26), `noindex` meta rendered when flag is true
    - _Requirements: 22.1–22.6; Properties 25, 26_

- [x] 24. F22 — Analytics Integration
  - [x] 24.1 Add `analytics_script` key to `settings` seeder with empty default value; in `layouts/public.blade.php` `<head>`, render `{!! Setting::get('analytics_script') !!}` inside a `@if(filled(Setting::get('analytics_script')))` guard — only when non-empty (Property 27)
    - _Requirements: 23.1, 23.2_
  - [x] 24.2 Ensure the admin leads `index` view filter form includes `utm_source` and `utm_campaign` text inputs wired to `LeadController::index` query scope
    - _Requirements: 23.3, 23.4_
  - [x]* 24.3 Write feature tests: analytics script rendered in `<head>` when setting non-empty, absent when setting empty (Property 27), UTM filter returns only matching leads
    - _Requirements: 23.1–23.4; Property 27_

- [x] 25. F23 — Security QA
  - [x] 25.1 Verify all `/admin/*` routes are covered by the `auth + EnsureStaffIsActive + EnsureOwnerMfa` middleware stack; add any missing middleware to route groups in `routes/web.php`
    - _Requirements: 24.1, 24.8_
  - [x] 25.2 Audit all Blade templates: confirm all user-supplied output uses `{{ }}` and only pre-purified CMS body uses `{!! !!}`; fix any violations
    - _Requirements: 24.2_
  - [x] 25.3 Confirm `MediaService::store` uses `finfo_file` or equivalent for MIME detection (not extension); verify EXIF stripping is applied; verify executable MIME types are rejected
    - _Requirements: 24.4, 24.5_
  - [x] 25.4 Add `session.secure`, `session.http_only`, `session.same_site = lax` to `config/session.php`; confirm these are set in production via `.env`
    - _Requirements: 24.7_
  - [x] 25.5 Install `laravel/telescope` as a dev-only dependency; configure it to be disabled in production via `APP_ENV` guard in `TelescopeServiceProvider`
    - _Requirements: 25.3_
  - [x]* 25.6 Write security-focused feature tests: POST without CSRF token returns 419 (Property 28), `sales_user` cannot access owner-only routes (403), rate limit on login returns 429 (Property 29), rate limit on lead capture returns 429 (Property 30), XSS payload in property title is escaped in output, media upload with PHP MIME type is rejected with 422
    - _Requirements: 24.1–24.8; Properties 28, 29, 30_

- [x] 26. F24 — Deployment and Launch Readiness
  - [x] 26.1 Create `deploy/nginx.conf` (server block with PHP-FPM upstream, `try_files` for Laravel, gzip, and cache-control headers) and `deploy/php-fpm.conf` (pool config with appropriate `pm` settings)
    - _Requirements: 25.1_
  - [x] 26.2 Audit `.env.example` to ensure all required variables are documented: `DB_*`, `REDIS_*`, `MAIL_*`, `AWS_*`, `APP_KEY`, `QUEUE_FALLBACK_CONNECTION`, `WHATSAPP_NUMBER`, `LEAD_REPEAT_WINDOW_DAYS`
    - _Requirements: 25.2_
  - [x] 26.3 Verify `php artisan config:cache`, `route:cache`, and `view:cache` all complete without errors; fix any closures in route files that prevent route caching
    - _Requirements: 25.4_
  - [x] 26.4 Configure the `database` queue connection in `config/queue.php` as the fallback; document the `QUEUE_FALLBACK_CONNECTION=database` env var in `.env.example`
    - _Requirements: 25.5, 25.6_
  - [x] 26.5 Final checkpoint: run `php artisan test` and confirm zero failures across all Feature and Unit test suites; commit with all tests green
    - _Requirements: 25.3_

---

## Notes

- Tasks marked with `*` are optional and can be skipped for a faster MVP
- The build order follows the F00–F24 dependency graph strictly; do not implement a feature until its prerequisites are complete
- All business logic belongs in `app/Services` and `app/Policies` — never in Blade templates or controllers
- Every new data path requires: migration, Model, FormRequest with `authorize()`, Policy, Service method, and tests
- Lead capture is always transactional: `DB::transaction` commits before `dispatch()` is called
- No Vue/React — Blade + Alpine.js + Tailwind CSS only; no Elasticsearch — MySQL + Redis only
- Property tests validate universal correctness properties from the design; unit tests cover examples and edge cases
