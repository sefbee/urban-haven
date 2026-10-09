# Requirements Document

## Introduction

This document captures requirements for the Urban Haven Quality & UX Excellence Pass — a systematic repair and hardening of the existing Laravel 13 / PHP 8.4 real-estate platform for the Dhaka market. The scope covers five domains: (1) public search & property UX, (2) property detail & gallery, (3) shortlist/compare/account, (4) admin CRM & visit workflows, and (5) cross-cutting concerns including forms, accessibility, responsive behaviour, and error/loading states.

No new features are introduced; every requirement corresponds to a specific gap identified in the design audit.

---

## Glossary

- **FilterBar**: The `uhBrowse` Alpine.js component that drives property search via `partials/filter-bar.blade.php`.
- **Gallery**: The `uhGallery` Alpine.js lightbox component embedded in `public/properties/show.blade.php`.
- **Lead**: An enquiry submission captured by `LeadController`, modelled by the `Lead` Eloquent model.
- **LeadForm**: The `uhLeadForm` Alpine.js component in `partials/lead-form.blade.php`.
- **PropertyCard**: The reusable tile component in `partials/property-card.blade.php` and `showcase-slide.blade.php`.
- **SavedStore**: The `$store.saved` Alpine.js store that manages shortlist, compare, and recently-viewed lists in `localStorage`.
- **ShowcaseSlide**: The `showcase-slide.blade.php` carousel tile used on the homepage and search results.
- **AdminPropertyForm**: The multi-section compose form in `admin/properties/_form.blade.php`.
- **AdminLeads**: The CRM table (`admin/leads/index.blade.php`) and detail view (`admin/leads/show.blade.php`).
- **SiteVisit**: A viewing request managed through `admin/visits/index.blade.php`, modelled by `SiteVisitRequest`.
- **PublishChecklist**: The `canPublish(property)` algorithm that validates a property is ready for publication.
- **uhCopy**: The application's i18n copy system used for localised UI strings.
- **TRANSITIONS**: The allowed state-machine transitions defined on `Lead` and `SiteVisitRequest` models.
- **DeduplicationWindow**: The 60-second window within which identical lead submissions are treated as duplicates.

---

## Requirements

### Requirement 1: Filter Bar Alpine Wiring

**User Story:** As a site visitor, I want the property search filters to work reliably, so that I can find properties that match my criteria without unexpected behaviour.

#### Acceptance Criteria

1. THE FilterBar SHALL reference the listing-type hidden input via `x-ref="status"` rather than via a raw `document.querySelector` selector.
2. WHEN the "Any status" button inside the status popover is clicked, THE FilterBar SHALL dispatch the `uh-listing-purpose` event with an empty or null detail value rather than `'sale'`.
3. WHEN a range slider input fires a `mousemove` or `input` event, THE FilterBar SHALL NOT trigger a search fetch; THE FilterBar SHALL only trigger a search fetch on `blur` or `change` events for text and range inputs.
4. WHEN a select or checkbox filter is changed, THE FilterBar SHALL trigger a debounced search fetch after 250 ms.

---

### Requirement 2: Filter URL Round-Trip

**User Story:** As a site visitor, I want shareable search URLs to reproduce identical results, so that I can bookmark, share, and revisit filtered property lists.

#### Acceptance Criteria

1. WHEN a user applies any combination of valid filter parameters and the resulting URL is reloaded, THE FilterBar SHALL produce results identical to the original filtered query.
2. WHEN a filter change is applied, THE FilterBar SHALL update `window.location` via `history.pushState` so the URL reflects the active filters.
3. WHEN the FilterBar makes a fetch request, THE FilterBar SHALL include an `X-Requested-With: XMLHttpRequest` header and replace only the relevant DOM sections on success.
4. IF a fetch request fails due to a network error or 5xx response, THEN THE FilterBar SHALL fall back to a full-page navigation to the constructed URL via `window.location.assign`.

---

### Requirement 3: Property Card Rendering

**User Story:** As a site visitor, I want property cards to display correctly in all contexts, so that I can browse listings without layout or data errors.

#### Acceptance Criteria

1. WHEN `ShowcaseSlide` is rendered in any context (homepage carousel, search results, or shortlist), THE ShowcaseSlide SHALL guard against a null `$amenityCatalog` and render without PHP notices when the variable is not passed.
2. WHEN the active photo of a `ShowcaseSlide` is rendered, THE ShowcaseSlide SHALL apply the `is-on` class exactly once; it SHALL NOT apply the class via both a static `@class` directive and a dynamic `:class` binding simultaneously.
3. WHEN a property price overlay is displayed on a `PropertyCard`, THE PropertyCard SHALL apply a background scrim to the image so that price text remains readable regardless of image content.

---

### Requirement 4: Property Detail Page

**User Story:** As a site visitor, I want the property detail page to work correctly and accessibly, so that I can view full listing information without broken interactions or security issues.

#### Acceptance Criteria

1. THE property detail tabs strip SHALL use an `is-active` CSS class or `aria-current` attribute to indicate the active tab, rather than a Tailwind arbitrary `data-active` variant.
2. WHEN floor plan download links are rendered with `target="_blank"`, THE property detail page SHALL include `rel="noopener"` on each such anchor element.
3. WHEN `$media->slice(1, 2)` is used to produce the gallery thumbnail collection, THE property detail page SHALL re-index the resulting collection so that `$loop->index` is contiguous and `open($index + 1)` opens the correct photo.
4. WHEN the "View all photos" gallery button is rendered, THE property detail page SHALL ensure the button is not visually clipped by an `overflow: hidden` parent at any supported viewport width.

---

### Requirement 5: Gallery Lightbox Behaviour

**User Story:** As a site visitor, I want the photo gallery to open, navigate, and close correctly, so that I can view all property photos in sequence.

#### Acceptance Criteria

1. WHEN `Gallery.open(index)` is called with a valid index `i` where `0 ≤ i < count`, THE Gallery SHALL set the active photo to index `i` and display the lightbox with body scroll locked.
2. WHEN `Gallery.open(index)` is called with an index outside `[0, count)`, THE Gallery SHALL NOT open the lightbox and SHALL log a warning.
3. WHEN `Gallery.next()` is called while the active photo is the last in the collection, THE Gallery SHALL wrap to photo index 0.
4. WHEN `Gallery.previous()` is called while the active photo is the first in the collection, THE Gallery SHALL wrap to the last photo index `count - 1`.
5. WHEN `Gallery.close()` is called, THE Gallery SHALL hide the lightbox and restore body scroll.
6. WHEN the gallery lightbox is opened, THE Gallery SHALL move keyboard focus to the lightbox container; WHEN the gallery lightbox is closed, THE Gallery SHALL return keyboard focus to the element that triggered the open action.

---

### Requirement 6: Shortlist and Compare State Management

**User Story:** As a site visitor, I want my shortlist and compare list to update accurately without unnecessary flicker or stale UI state, so that I have a reliable saved-properties experience.

#### Acceptance Criteria

1. WHEN the `SavedStore` fires a store mutation that does not change the set of IDs in a list (e.g. a `prune()` call that removes nothing), THE SavedStore's `uhSavedList` component SHALL NOT trigger a server fetch for that list.
2. WHEN the compare page is rendered and the number of compared items drops to fewer than 2, THE compare page SHALL hide the comparison content area immediately, regardless of whether the `html` variable was previously populated from a prior load.
3. WHEN the shortlist "Clear all" action is triggered, THE shortlist page SHALL display a confirmation dialog whose text is sourced from the `uhCopy` i18n system rather than a bare JavaScript string literal.
4. THE SavedStore SHALL enforce a maximum list length of `LIMITS[list]` for each list in `{shortlist, compare}`; after the limit is reached, any further add operation SHALL be rejected and a limit-reached notice SHALL be shown.

---

### Requirement 7: Lead Enquiry Form

**User Story:** As a site visitor, I want the enquiry form to validate my input and handle server responses clearly, so that I can submit property enquiries with confidence.

#### Acceptance Criteria

1. WHEN a user enters a phone number, THE LeadForm SHALL validate that the value matches the Bangladeshi mobile format: starts with `01` and contains exactly 11 digits; THE LeadForm SHALL display a validation hint if the format does not match.
2. WHEN the form is submitted via full-page POST fallback and a `consent_given` validation error is present, THE LeadForm SHALL render the consent error message exactly once; it SHALL NOT render duplicate messages from both the server `@error` directive and the Alpine `x-show="fieldError"` binding simultaneously.
3. WHEN the server returns HTTP 429, THE LeadForm SHALL display a rate-limit-specific message (e.g. "Too many requests, please try again shortly") rather than a generic error message.
4. WHEN the form submission completes with HTTP 2xx, THE LeadForm SHALL set `done = true` and display a success message.
5. WHEN the server returns HTTP 4xx with an `errors` key, THE LeadForm SHALL populate `fieldErrors` and highlight the relevant fields without setting `done = true`.
6. WHEN a network or offline error occurs during submission, THE LeadForm SHALL display an offline/error message and re-enable the submit button.
7. THE LeadForm SHALL set `submitting = false` in all exit paths, including success, validation error, rate-limit error, and network error.

---

### Requirement 8: Lead Deduplication

**User Story:** As the platform, I want duplicate enquiries from the same contact within a short window to be detected automatically, so that the sales team is not overwhelmed with repeated entries for the same lead.

#### Acceptance Criteria

1. WHEN a lead submission `S` is received and an existing lead with the same phone number (and same property, if specified) was created within the last 60 seconds, THE Lead system SHALL NOT create a new `Lead` record; THE Lead system SHALL return a success response with `is_repeat_contact = true`.
2. WHEN a lead submission `S` is received and no matching lead exists within the 60-second window, THE Lead system SHALL create exactly one new `Lead` record.
3. WHEN a lead submission occurs at exactly 59 seconds after a prior identical submission, THE Lead system SHALL treat it as a duplicate.
4. WHEN a lead submission occurs at 61 seconds or more after a prior identical submission, THE Lead system SHALL create a new `Lead` record.

---

### Requirement 9: Admin Property Form

**User Story:** As an admin staff member, I want the property editing form to submit only relevant data and accurately reflect my permissions, so that I do not inadvertently corrupt property records.

#### Acceptance Criteria

1. WHEN the property form is submitted with a status value other than `'reserved'`, THE AdminPropertyForm SHALL NOT include `reservation_expires_at` in the submitted payload; the model SHALL NOT receive or persist that value.
2. WHEN `$canEdit` is `false`, THE AdminPropertyForm SHALL disable the submit button so it cannot be clicked; the submit button SHALL remain disabled even if it is rendered outside the disabled fieldset.
3. WHEN an editor (non-owner) views the save action, THE AdminPropertyForm SHALL display "Save draft" or equivalent draft-submit label rather than "Save changes", to accurately reflect that the action does not publish the record.
4. WHEN an owner views the save action, THE AdminPropertyForm SHALL display "Save changes" or equivalent publish label.

---

### Requirement 10: Admin Lead CRM

**User Story:** As a sales staff member, I want the lead management table and detail views to reflect real-time data and be fully interactive, so that I can manage my pipeline efficiently.

#### Acceptance Criteria

1. WHEN a staff member applies a filter on the admin leads index page, THE AdminLeads page SHALL refresh the overdue badge count to reflect the current filtered result set.
2. WHEN a staff member clicks a lead table row (the `uh-admin-clickrow` element), THE AdminLeads page SHALL navigate to that lead's detail page.
3. WHEN a staff member selects a new stage from the lead status widget, THE AdminLeads system SHALL only accept transitions that are present in `Lead::TRANSITIONS[currentStatus]`; all other transitions SHALL return HTTP 422.
4. WHEN a staff member attempts to transition a lead to `'lost'` status without providing a `loss_reason`, THE AdminLeads system SHALL return HTTP 422 and display a validation error for the `loss_reason` field.
5. WHEN a staff member submits a valid `'lost'` transition with a `loss_reason`, THE AdminLeads system SHALL persist the status change and create an audit log entry.

---

### Requirement 11: Admin Site Visit Workflows

**User Story:** As a sales staff member, I want site visit status updates to be validated and confirmed before submission, so that I do not accidentally finalise visits or submit invalid state transitions.

#### Acceptance Criteria

1. WHEN a site visit status update to `'completed'` is submitted with an empty `outcome_note`, THE SiteVisit system SHALL reject the submission with HTTP 422 and display a validation error.
2. WHEN a site visit status update to `'completed'` is submitted with a non-empty `outcome_note`, THE SiteVisit system SHALL persist the transition and mark the visit as completed.
3. WHEN a staff member attempts to submit a terminal or irreversible status transition (e.g. `'completed'`), THE SiteVisit UI SHALL display an inline confirmation prompt before submitting the form.
4. WHEN the `<template x-if>` block for the outcome note textarea is inserted by Alpine, THE SiteVisit UI SHALL ensure the `required` attribute is active before the form can be submitted, preventing submission of an empty note on a fast click.
5. THE SiteVisit system SHALL only allow transitions that conform to the defined `TRANSITIONS` map:
   - `'pending'` → `['confirmed', 'cancelled']`
   - `'confirmed'` → `['completed', 'no_show', 'cancelled']`
   - `'completed'`, `'no_show'`, and `'cancelled'` are terminal states and SHALL NOT accept further transitions.

---

### Requirement 12: Property Publish Checklist

**User Story:** As an admin, I want incomplete property listings to be blocked from publication automatically, so that the public site only shows complete, high-quality listings.

#### Acceptance Criteria

1. WHEN a publish request is submitted for a property that is missing a `reference`, THE PublishChecklist system SHALL return HTTP 422 and report `'Missing reference'` in the failure list.
2. WHEN a publish request is submitted for a property where `price_mode = 'fixed'` and `price` is null, THE PublishChecklist system SHALL return HTTP 422 and report `'Missing price'` in the failure list.
3. WHEN a publish request is submitted for a property with a null `area_value`, THE PublishChecklist system SHALL return HTTP 422 and report `'Missing size'` in the failure list.
4. WHEN a publish request is submitted for a property with no gallery images, THE PublishChecklist system SHALL return HTTP 422 and report `'No photos'` in the failure list.
5. WHEN a publish request is submitted for a property where any gallery image is missing its `alt` text in the active locale, THE PublishChecklist system SHALL return HTTP 422 and report `'Photo missing alt text'` in the failure list.
6. WHEN a publish request is submitted for a property that satisfies all checklist requirements, THE PublishChecklist system SHALL allow the publication to proceed.
7. THE PublishChecklist system SHALL check all required fields in a single pass and return all failures together rather than stopping at the first failure.

---

### Requirement 13: Cross-Cutting Form Behaviour

**User Story:** As any user submitting a form, I want forms to behave consistently and safely across the application, so that I experience predictable validation and feedback.

#### Acceptance Criteria

1. WHEN a form field has both a server-side `@error` directive and an Alpine `x-show="fieldError"` binding for the same field, THE form SHALL render the error message exactly once per submission path (either server-rendered on full-page POST, or Alpine-rendered on XHR).
2. WHEN a `StaleRecordException` is thrown during a concurrent edit, THE form system SHALL redirect with a flash message: "This record was updated by someone else. Reload and re-apply your changes."
3. WHEN any admin form submit button is rendered, THE form system SHALL ensure the button is disabled or inaccessible when the user lacks edit permission, regardless of whether the button is inside or outside the disabled fieldset.

---

### Requirement 14: Accessibility

**User Story:** As any user, I want the application to meet basic accessibility standards, so that I can use it with assistive technologies and keyboard navigation.

#### Acceptance Criteria

1. WHEN a dynamic alert or notification is rendered (e.g. form errors, flash messages, live notices), THE application SHALL include `role="alert"` or `aria-live="polite"` on the alert container.
2. WHEN the gallery lightbox is opened, THE Gallery SHALL move keyboard focus to the lightbox; WHEN the lightbox is closed, THE Gallery SHALL return focus to the element that triggered the open action.
3. WHEN a modal or drawer is open, THE application SHALL trap keyboard focus within that modal or drawer so that Tab does not reach elements outside it.
4. THE public layout SHALL include a visible skip link that targets the `#main` content landmark.
5. WHEN any interactive element (button, link, input) is rendered, THE application SHALL provide a visible label or an `aria-label` attribute.

---

### Requirement 15: Security Hardening

**User Story:** As the platform operator, I want security constraints to remain intact and be applied consistently to any new code paths introduced by this pass, so that the application is not weakened.

#### Acceptance Criteria

1. WHEN a new admin form action is introduced or modified, THE application SHALL route it through an existing policy gate (`$user->can()`) and assignment-scoped query.
2. WHEN a file download link is rendered for a brochure or floor plan, THE application SHALL use the `MediaDownloadController` rather than a raw `Storage::url()` call, so that publish-state visibility is enforced.
3. WHEN rich-text property description content is rendered, THE application SHALL pass it through `Purify::clean()` at every render point.
4. WHEN floor plan links are rendered with `target="_blank"`, THE application SHALL include `rel="noopener"` to prevent tab-napping.
5. THE enquiry form `submission_token` SHALL be generated server-side and embedded as a hidden input; it SHALL NOT be generated on the client side.

---

### Requirement 16: Admin Role Boundaries

**User Story:** As the platform operator, I want role-based access to be consistently enforced, so that sales staff can only access leads assigned to them and editors cannot publish records.

#### Acceptance Criteria

1. WHEN a sales user attempts to view or modify a lead not assigned to them, THE AdminLeads system SHALL return HTTP 403 or redirect with an authorisation error.
2. WHEN an editor user submits the property form, THE AdminPropertyForm SHALL save the record as a draft rather than publishing it, regardless of the submit button label.
3. WHEN an owner user submits the property form with all publish checklist requirements met, THE AdminPropertyForm SHALL allow the publication to proceed.
