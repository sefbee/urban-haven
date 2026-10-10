# Implementation Plan: Urban Haven Quality & UX Excellence Pass

## Overview

Systematic repair of the existing Laravel 13 / PHP 8.4 platform across five domains: public search & property UX, property detail & gallery, shortlist/compare/account, admin CRM & visit workflows, and cross-cutting concerns. Every task stays within the existing stack (Blade, Alpine.js, Tailwind 4, Laravel 13, PHPUnit) and preserves approved layouts and data contracts.

## Tasks

- [x] 1. Filter Bar Alpine Wiring fixes
  - [x] 1.1 Replace raw `document.querySelector` with `x-ref="status"` in `partials/filter-bar.blade.php`
    - In `uhBrowse`, replace any `document.querySelector('[name=listing_type]')` or equivalent DOM selector with `this.$refs.status` wherever the hidden listing-type input is read or written
    - _Requirements: 1.1_

  - [x] 1.2 Fix "Any status" button to dispatch null/empty `uh-listing-purpose` event
    - Change the dispatch call inside the "Any status" button handler so `detail` is `null` or `''` instead of `'sale'`, preventing the price-range from snapping to sale stops on clear
    - _Requirements: 1.2_

  - [x] 1.3 Gate range-slider events to `blur`/`change` only; debounce select/checkbox to 250 ms
    - Remove any `@input` or `@mousemove` listeners that trigger `search()` on range inputs; keep `@change.debounce.250ms` for select and checkbox inputs; add `@blur` listener for text inputs
    - _Requirements: 1.3, 1.4_

  - [x] 1.4 Write property test for filter URL round-trip (P1)
    - **Property P1: Filter URL Round-Trip**
    - Generate random valid filter bags, apply them, reload the resulting URL, assert the result set is identical
    - **Validates: Requirements 2.1, 2.2**

- [x] 2. Filter URL round-trip and XHR behaviour
  - [x] 2.1 Implement `history.pushState` on every filter change in `uhBrowse.search()`
    - After a successful fetch, call `history.pushState({}, '', url)` so the address bar reflects active filters; ensure the URL constructed from the form matches what a page reload would use
    - _Requirements: 2.2_

  - [x] 2.2 Add `X-Requested-With: XMLHttpRequest` header to all fetch calls and implement DOM fragment replacement
    - Set the header on every `fetch()` call inside `uhBrowse`; on success replace `.uh-results-bar`, `.uh-active-filters`, and `.uh-workspace` via `replaceWith()`
    - _Requirements: 2.3_

  - [x] 2.3 Add network-error fallback to `window.location.assign(url)` in fetch catch block
    - In the `catch` handler of `uhBrowse.search()`, call `window.location.assign(url)` so a failed XHR gracefully degrades to a full-page load
    - _Requirements: 2.4_

- [x] 3. Property Card rendering fixes
  - [x] 3.1 Guard `$amenityCatalog` null reference in `showcase-slide.blade.php`
    - Wrap every `$amenityCatalog->get(...)` call with a null-safe check (`$amenityCatalog?->get(...)`) or provide a default empty collection when the variable is not passed; suppress PHP notices on homepage carousel
    - _Requirements: 3.1_

  - [x] 3.2 Remove duplicate `is-on` class binding in `showcase-slide.blade.php`
    - Remove the static `@class(['is-on' => $photoIndex === 0])` directive; keep only the dynamic `:class="{ 'is-on': photoOn(…) }"` Alpine binding so the class is never applied twice
    - _Requirements: 3.2_

  - [x] 3.3 Add background scrim to price overlay in `partials/property-card.blade.php`
    - Add a gradient or semi-transparent overlay element (e.g. `bg-gradient-to-t from-black/60`) behind the `.uh-listing-image-price` text so price text remains readable over any image
    - _Requirements: 3.3_

  - [x] 3.4 Write unit tests for property card rendering edge cases
    - Test that `showcase-slide` renders without errors when `$amenityCatalog` is null
    - Test that `is-on` class appears exactly once on the active photo element
    - _Requirements: 3.1, 3.2_

- [ ] 4. Property detail page fixes (`public/properties/show.blade.php`)
  - [x] 4.1 Replace `data-active` tab indicator with `is-active` class or `aria-current`
    - In `.uh-pd-tabs-strip`, replace the `data-active` attribute and any `:data-active` Tailwind arbitrary variant with a standard `is-active` CSS class toggle or `aria-current="page"` binding
    - _Requirements: 4.1_

  - [x] 4.2 Add `rel="noopener"` to all floor plan `target="_blank"` anchors
    - Locate every `<a target="_blank">` link rendering floor plan downloads and add `rel="noopener"` (or `rel="noopener noreferrer"`) to prevent tab-napping
    - _Requirements: 4.2, 15.4_

  - [x] 4.3 Re-index `$thumbs` collection so `$loop->index` is contiguous
    - Change `$thumbs = $media->slice(1, 2)` to `$thumbs = $media->slice(1, 2)->values()` (or equivalent `->values()` call) so array keys are reset and `open({{ $loop->index + 1 }})` maps to the correct photo index
    - _Requirements: 4.3_

  - [x] 4.4 Fix "View all photos" button clipping by `overflow: hidden` parent
    - In `.uh-pd-gallery`, either change the parent container to `overflow: visible`, use `overflow: clip` only on the image portion, or reposition `.uh-pd-gallery-all` outside the clipping ancestor so the button is fully visible at all supported viewport widths
    - _Requirements: 4.4_

- [x] 5. Checkpoint — ensure all tests pass and property detail renders correctly
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Gallery lightbox behaviour (`uhGallery` Alpine component in `public/properties/show.blade.php`)
  - [x] 6.1 Implement bounds-checked `open(index)` with body scroll lock
    - In `uhGallery.open(index)`: guard `0 ≤ index < count`, set `active = index`, set `lightbox = true`, add `overflow-hidden` to `document.body`; log a warning and return early for out-of-bounds indices
    - _Requirements: 5.1, 5.2_

  - [ ]* 6.2 Write property test for gallery navigation wrap-around (P6)
    - **Property P6: Gallery Navigation Wraps Within Bounds**
    - For any `n ≥ 1`, assert `next()` from index `n-1` produces `active = 0`; `previous()` from index `0` produces `active = n-1`
    - **Validates: Requirements 5.3, 5.4**

  - [x] 6.3 Implement `next()` / `previous()` with modular wrap
    - `next()`: `this.active = (this.active + 1) % this.count`; `previous()`: `this.active = (this.active - 1 + this.count) % this.count`
    - _Requirements: 5.3, 5.4_

  - [x] 6.4 Implement `close()` with body scroll restore
    - `close()`: set `lightbox = false`, remove `overflow-hidden` from `document.body`
    - _Requirements: 5.5_

  - [x] 6.5 Implement focus management — move focus on open, restore on close
    - On `open()`: store `document.activeElement` as `this._opener`; use `this.$nextTick(() => this.$el.focus())` to move focus to the lightbox container (ensure the container has `tabindex="-1"`)
    - On `close()`: call `this._opener?.focus()` to return focus to the triggering element
    - _Requirements: 5.6, 14.2_

  - [ ]* 6.6 Write unit tests for gallery open/close/focus behaviour
    - Test open with valid index sets `active` and `lightbox = true`
    - Test open with out-of-bounds index does not open lightbox
    - Test close sets `lightbox = false`
    - _Requirements: 5.1, 5.2, 5.5_

- [x] 7. Shortlist & compare state management
  - [x] 7.1 Guard `uhSavedList` to skip server fetch when ID set is unchanged
    - In `uhSavedList`'s `$watch` callback, compare the new ID set against the previous one (e.g. serialise sorted IDs); only call `load()` when the set has changed
    - _Requirements: 6.1_

  - [x] 7.2 Fix compare page to hide content area immediately when count drops below 2
    - Change the `x-show` condition on the compare content div from `count >= 2 && html` to `count >= 2`; clear `html` when an item is removed so stale content does not persist
    - _Requirements: 6.2_

  - [x] 7.3 Replace bare `confirm()` string in shortlist "Clear all" with `uhCopy` i18n
    - Locate the `confirm('...')` call in the shortlist clear-all handler; replace the literal string with `this.$store.copy.get('shortlist.clearConfirm')` or the appropriate `uhCopy` accessor
    - _Requirements: 6.3_

  - [x] 7.4 Enforce `LIMITS[list]` in `SavedStore.toggle()` with limit-reached notice
    - In `registerSavedStore.toggle(list, id)`: before appending, check `this[list].length >= LIMITS[list]`; if at limit, set the notice to the limit-reached copy and return without mutating the list
    - _Requirements: 6.4_

  - [ ]* 7.5 Write property test for saved store limit enforcement (P3)
    - **Property P3: Saved Store Limit Enforcement**
    - Fuzz random add/remove sequences for shortlist and compare; assert `|list| ≤ LIMITS[list]` invariant holds after every operation
    - **Validates: Requirements 6.4**

- [x] 8. Lead enquiry form (`partials/lead-form.blade.php`)
  - [x] 8.1 Add Bangladeshi phone number pattern validation to `uhLeadForm`
    - Add a `pattern="01[0-9]{9}"` attribute (or Alpine `x-model` + computed validator) to the phone field; display a validation hint (`fieldErrors.phone`) when the format does not match the `01XXXXXXXXX` (11-digit) pattern
    - _Requirements: 7.1_

  - [x] 8.2 Eliminate duplicate consent error message on full-page POST fallback
    - Remove the `@error('consent_given')` Blade directive from the consent field and rely solely on the Alpine `x-show="fieldError('consent_given')"` binding, OR gate the Alpine binding to only render on XHR responses; ensure exactly one error message appears per submission path
    - _Requirements: 7.2, 13.1_

  - [x] 8.3 Handle HTTP 429 with rate-limit-specific error message
    - In the `uhLeadForm.submit()` catch/response handling, check `response.status === 429` and set `this.error` to the rate-limit copy string (e.g. `uhCopy.get('lead.rateLimitError')`) instead of the generic error message
    - _Requirements: 7.3_

  - [x] 8.4 Implement all remaining `submit()` postconditions (2xx, 4xx, network, `submitting = false`)
    - 2xx: set `done = true`, populate `message` from response body
    - 4xx with `errors`: populate `fieldErrors`, keep `done = false`
    - Network/offline error: set `error` to offline copy string, re-enable submit button
    - All paths: ensure `submitting = false` in `finally` block
    - _Requirements: 7.4, 7.5, 7.6, 7.7_

  - [ ]* 8.5 Write property test for LeadForm submit postconditions (P7)
    - **Property P7: LeadForm Submit Postconditions**
    - For each HTTP response class (2xx, 4xx, 429, network error), assert the correct combination of `done`, `fieldErrors`, `error`, and `submitting` state
    - **Validates: Requirements 7.3, 7.4, 7.5, 7.6, 7.7**

- [x] 9. Lead deduplication (`LeadController` / `Lead` model)
  - [x] 9.1 Implement `isRepeatSubmission()` dedup check in `LeadController::store()`
    - Query `Lead::where('phone', $phone)->where('created_at', '>=', now()->subSeconds(60))`; add `->where('property_id', $propertyId)` when property is specified; if a match is found, return a success response with `is_repeat_contact = true` without creating a new record
    - _Requirements: 8.1, 8.2_

  - [x] 9.2 Ensure boundary conditions (59 s = duplicate, 61 s = new lead) are correctly handled
    - Confirm the cutoff is `now()->subSeconds(60)` (exclusive), so a submission at exactly 59 s is within the window and at 61 s is outside it
    - _Requirements: 8.3, 8.4_

  - [ ]* 9.3 Write property test for lead deduplication idempotency (P2)
    - **Property P2: Lead Deduplication Idempotency**
    - For any valid lead submission `S`, submitting `S` twice within 60 s must create exactly one `Lead` record; the second response must have `is_repeat_contact = true`
    - **Validates: Requirements 8.1, 8.2**

  - [ ]* 9.4 Write unit tests for dedup boundary conditions
    - Test: submission at t+59 s → duplicate (no new record)
    - Test: submission at t+61 s → new record created
    - _Requirements: 8.3, 8.4_

- [x] 10. Checkpoint — run full test suite; ensure dedup, form, and gallery tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 11. Admin property form (`admin/properties/_form.blade.php`)
  - [x] 11.1 Exclude `reservation_expires_at` from payload when status is not `'reserved'`
    - Add `x-ref` or a conditional name attribute so the `datetime-local` input is only included in the submitted form data when `availability === 'reserved'`; alternatively, strip the field server-side in the `AdminPropertyRequest` when the status is not `'reserved'`
    - _Requirements: 9.1_

  - [ ]* 11.2 Write property test for reservation expiry submission guard (P8)
    - **Property P8: Reservation Expiry Submission Guard**
    - For any form submission where `availability ≠ 'reserved'`, assert `reservation_expires_at` is absent from the persisted model
    - **Validates: Requirements 9.1**

  - [x] 11.3 Disable submit button in `.uh-admin-dock` when `$canEdit = false`
    - The submit button in the dock is outside the disabled fieldset; add `@disabled(! $canEdit)` (or `:disabled="!canEdit"` via Alpine) directly to that button element
    - _Requirements: 9.2, 13.3_

  - [x] 11.4 Set correct save-button label for editor vs owner roles
    - Pass a `$saveLabel` variable from the controller: `'Save draft'` when the authenticated user is an editor, `'Save changes'` when they are an owner; render `{{ $saveLabel }}` on the submit button
    - _Requirements: 9.3, 9.4, 16.2, 16.3_

  - [ ]* 11.5 Write feature tests for admin property form role behaviour
    - Test: editor submits form → record saved as draft, not published
    - Test: owner submits form with complete checklist → publication proceeds
    - Test: submit button is disabled when `canEdit = false`
    - _Requirements: 9.2, 9.3, 9.4, 16.2, 16.3_

- [x] 12. Property publish checklist (`canPublish` algorithm)
  - [x] 12.1 Implement `canPublish(Property $property)` as a dedicated service or action class
    - Create `app/Actions/CanPublishProperty.php` (or equivalent); implement the single-pass algorithm: collect all failures for `reference`, `price`/`price_mode`, `area_value`, gallery images, and per-image alt text; return `['canPublish' => bool, 'failures' => array]`
    - _Requirements: 12.1–12.7_

  - [x] 12.2 Wire `canPublish` check into the publish route/controller with HTTP 422 response
    - In the property publish action (controller or policy), call the action; if `canPublish` is false, return HTTP 422 with the `failures` array; allow the request to proceed only when all checks pass
    - _Requirements: 12.1–12.6_

  - [ ]* 12.3 Write property test for publish checklist completeness (P5)
    - **Property P5: Publish Checklist Completeness**
    - For any property missing one or more required fields, assert `POST /admin/publications/properties/{id}/publish` returns 422; for a fully complete property, assert 2xx
    - **Validates: Requirements 12.1, 12.2, 12.3, 12.4, 12.5**

  - [ ]* 12.4 Write unit tests for `canPublish` edge cases
    - Test each individual failure condition in isolation
    - Test that all failures are returned in a single pass (not short-circuited)
    - _Requirements: 12.7_

- [x] 13. Admin Lead CRM (`admin/leads/index.blade.php`, `admin/leads/show.blade.php`)
  - [x] 13.1 Refresh overdue badge count after filter changes on the leads index
    - Include the `$overdueCount` in the XHR fragment response from `LeadController::index()` so the badge re-renders each time filters are applied; update the badge element in the DOM fragment
    - _Requirements: 10.1_

  - [x] 13.2 Wire `uh-admin-clickrow` table rows to navigate to the lead detail page
    - Add an Alpine or vanilla JS click handler to elements with class `uh-admin-clickrow` that reads a `data-href` attribute and performs `window.location.href = el.dataset.href`; ensure each `<tr>` has `data-href="{{ route('admin.leads.show', $lead) }}"`
    - _Requirements: 10.2_

  - [x] 13.3 Enforce allowed lead stage transitions in `LeadController::updateStatus()`
    - Validate that the submitted `status` value is present in `Lead::TRANSITIONS[$lead->status]`; return HTTP 422 if not; use the `TRANSITIONS` map already defined on the model
    - _Requirements: 10.3_

  - [x] 13.4 Require `loss_reason` when transitioning to `'lost'` status
    - Add a conditional validation rule: `'loss_reason' => ['required_if:status,lost', 'string']`; return HTTP 422 with a `loss_reason` field error when the rule fails
    - _Requirements: 10.4_

  - [x] 13.5 Persist `'lost'` transition and create audit log entry
    - On a valid lost transition, update `lead->status = 'lost'` and `lead->loss_reason`; call `AuditLogger::log(...)` (or equivalent) to record the transition; redirect with flash
    - _Requirements: 10.5_

  - [ ]* 13.6 Write property test for lead stage transition FSM (P4)
    - **Property P4: Lead Stage Transition Validity**
    - For each status `s` and each target status `t NOT IN TRANSITIONS[s]`, assert `PUT /admin/leads/{id}/status` returns 422; for `t IN TRANSITIONS[s]`, assert 2xx (or 302)
    - **Validates: Requirements 10.3**

  - [ ]* 13.7 Write feature tests for lead CRM workflows
    - Test: filter application refreshes overdue badge
    - Test: clicking a lead row navigates to detail page
    - Test: closing lead as lost without `loss_reason` returns 422
    - Test: valid lost transition persists status and creates audit log
    - _Requirements: 10.1–10.5_

- [x] 14. Admin site visit workflows (`admin/visits/index.blade.php`)
  - [x] 14.1 Implement `SiteVisitRequest` transition validation in `SiteVisitController`
    - Enforce the `TRANSITIONS` map: `pending → [confirmed, cancelled]`; `confirmed → [completed, no_show, cancelled]`; terminal states reject all transitions with HTTP 422
    - _Requirements: 11.5_

  - [x] 14.2 Require `outcome_note` when transitioning to `'completed'`
    - Add validation rule `'outcome_note' => ['required_if:status,completed', 'string', 'min:1']`; return HTTP 422 with field error when the note is empty on a completed transition
    - _Requirements: 11.1, 11.2_

  - [x] 14.3 Add inline confirmation prompt before submitting irreversible transitions
    - In the visit status form, add an Alpine `x-confirm` or inline `@submit.prevent="confirmIfTerminal($event)"` handler that shows a confirmation message before allowing the form to submit when the target status is `'completed'`, `'no_show'`, or `'cancelled'`
    - _Requirements: 11.3_

  - [x] 14.4 Fix `<template x-if>` / `required` race condition on outcome note textarea
    - Replace `<template x-if="status === 'completed'">` with an always-rendered textarea controlled by `x-show` and `:disabled="status !== 'completed'"`, so the `required` attribute is always present in the DOM and active before Alpine processes the form submission
    - _Requirements: 11.4_

  - [ ]* 14.5 Write feature tests for site visit transition workflows
    - Test: complete transition with empty `outcome_note` returns 422
    - Test: complete transition with valid `outcome_note` persists correctly
    - Test: terminal states reject further transitions with 422
    - _Requirements: 11.1, 11.2, 11.5_

- [x] 15. Checkpoint — run full test suite; ensure admin CRM and visit tests all pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 16. Cross-cutting form behaviour
  - [x] 16.1 Implement `StaleRecordException` redirect with flash message
    - Add a `Handler` (or existing exception handler) catch clause for `StaleRecordException`; redirect back with flash `'This record was updated by someone else. Reload and re-apply your changes.'`
    - _Requirements: 13.2_

  - [ ]* 16.2 Write feature tests for concurrent edit conflict handling
    - Test: submitting a form with a stale `version` triggers `StaleRecordException` and returns a redirect with the correct flash message
    - _Requirements: 13.2_

- [x] 17. Accessibility hardening
  - [x] 17.1 Add `role="alert"` or `aria-live="polite"` to all dynamic notification containers
    - Audit `layouts/public.blade.php`, `layouts/admin.blade.php`, and all flash/notification partials; add `role="alert"` to error alert containers and `aria-live="polite"` to informational live regions
    - _Requirements: 14.1_

  - [x] 17.2 Implement focus trap for open modals and drawers
    - In every modal/drawer Alpine component, add a focus-trap on open: listen for `keydown.tab` and cycle focus only among focusable children; release the trap on close
    - _Requirements: 14.3_

  - [x] 17.3 Add visible skip link targeting `#main` to `layouts/public.blade.php`
    - Add `<a href="#main" class="sr-only focus:not-sr-only ...">Skip to content</a>` as the first child of `<body>` in the public layout; ensure the `#main` landmark exists on the main content element
    - _Requirements: 14.4_

  - [x] 17.4 Audit all interactive elements for visible labels or `aria-label`
    - Scan icon-only buttons, image links, and inputs across public and admin views; add `aria-label` attributes where visible text is absent; prioritise gallery nav buttons, shortlist/compare toggle buttons, and admin action icons
    - _Requirements: 14.5_

- [ ] 18. Security hardening
  - [x] 18.1 Enforce `MediaDownloadController` for all brochure and floor plan download links
    - Search for any `Storage::url()` or `asset()` calls that serve floor plans or brochures; replace them with routes pointing to `MediaDownloadController` so publish-state visibility is enforced
    - _Requirements: 15.2_

  - [x] 18.2 Ensure `Purify::clean()` is called at every rich-text description render point
    - Grep for `$property->description` in all Blade views; confirm each render site passes the value through `Purify::clean()`; add the call where missing
    - _Requirements: 15.3_

  - [x] 18.3 Verify `submission_token` is generated server-side and embedded as a hidden input
    - Confirm `submission_token` is generated in the controller (not in JavaScript or Blade inline JS); ensure the hidden input is populated from the server-provided value; remove any client-side UUID generation
    - _Requirements: 15.5_

  - [ ]* 18.4 Write feature tests for security enforcement
    - Test: sales user cannot view or modify a lead not assigned to them (HTTP 403)
    - Test: floor plan link resolves through `MediaDownloadController`, not raw storage URL
    - _Requirements: 15.1, 15.2, 16.1_

- [x] 19. Admin role boundary enforcement
  - [x] 19.1 Scope lead queries to assigned leads for sales users in `LeadController`
    - In `LeadController::show()` and `LeadController::update()`, apply `->where('assigned_to', auth()->id())` when the authenticated user has the sales role (or use the existing policy gate); return HTTP 403 for unauthorised access
    - _Requirements: 16.1_

  - [ ]* 19.2 Write feature tests for admin role boundaries
    - Test: sales user cannot access another user's lead detail page (HTTP 403)
    - Test: editor form submission saves as draft, not published
    - Test: owner submission with all checklist items passes and publishes
    - _Requirements: 16.1, 16.2, 16.3_

- [x] 20. Final checkpoint — full test suite green; all five domains complete
  - Run the complete test suite with `php artisan test --compact`
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Property-based tests (P1–P8) validate universal correctness properties defined in the design
- Unit tests validate specific examples, boundary conditions, and edge cases
- Integration/feature tests validate end-to-end HTTP flows and role boundaries
- Checkpoints at tasks 5, 10, 15, and 20 provide natural validation breakpoints
