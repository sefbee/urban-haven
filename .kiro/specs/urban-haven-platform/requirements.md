# Requirements Document

## Introduction

Urban Haven Properties Ltd. is a single-vendor real estate platform for a Bangladeshi property company. Company staff publish and manage inventory (Projects, Properties, Units); there is no public seller registration. The platform is built on Laravel 13 / PHP 8.3+, Blade + Tailwind CSS, MySQL 8.4 LTS, Redis, Nginx/PHP-FPM, and Vite.

This document derives requirements from the approved design document covering all 25 features (F00–F24). Requirements follow EARS patterns and INCOSE quality rules.

---

## Glossary

- **System**: The Urban Haven Platform application as a whole
- **Auth_System**: The authentication and session management subsystem
- **EnsureStaffIsActive_Middleware**: Laravel middleware that terminates sessions of deactivated staff on each request
- **EnsureOwnerMfa_Middleware**: Laravel middleware that enforces TOTP verification for owner_admin users
- **StaffService**: The service class responsible for staff account lifecycle (create, update, deactivate)
- **AuditLogger**: The contract and implementation that records staff write actions to the audit_logs table
- **Settings_System**: The subsystem managing typed key-value application settings and reference data
- **MediaService**: The service responsible for file upload validation, image processing, and storage
- **InventoryService**: The service responsible for managing Projects, Properties, and Units
- **Publication_System**: The subsystem managing the editorial workflow state machine for publishable content
- **SearchService**: The service implementing property search with filtering, caching, and pagination
- **SimilarPropertiesService**: The service implementing the tiered similar-property matching algorithm
- **LeadService**: The service responsible for lead capture, deduplication, and follow-up management
- **Export_System**: The subsystem that generates CSV exports of lead data
- **CMS_System**: The subsystem managing CMS pages and structured content blocks
- **SEO_System**: The subsystem rendering meta tags and generating the XML sitemap
- **Sitemap_Generator**: The component that builds the XML sitemap from published content
- **Notification_System**: Laravel's notification subsystem used for in-app and email alerts
- **NotifyNewLeadJob**: The queued job that dispatches notifications when a new lead is captured
- **Map_Component**: The front-end component rendering Leaflet.js property map markers
- **Shortlist_System**: The session-based component for saving and comparing shortlisted properties
- **Admin_Dashboard**: The authenticated admin home page showing summary metrics and notifications
- **Public_Layout**: The Blade layout template used for all public-facing pages
- **Homepage**: The public root page (`/`) featuring projects, properties, and CMS content blocks
- **Property_Detail_Page**: The public page at `/properties/{slug}` showing full property information
- **Projects_Listing_Page**: The public page at `/projects` listing published projects
- **Project_Detail_Page**: The public page at `/projects/{slug}` showing project details and linked properties
- **Compare_View**: The public page allowing side-by-side comparison of 2–4 shortlisted properties
- **owner_admin**: The role with full platform access; MFA is mandatory
- **content_editor**: The role with inventory CRUD and media management access
- **sales_user**: The role with lead, site visit, and sales workflow access
- **TOTP**: Time-based One-Time Password used for MFA (Google Authenticator compatible)
- **BDT**: Bangladeshi Taka, the currency used throughout the platform
- **WebP**: The image format used for all media derivatives
- **UTM**: Urchin Tracking Module parameters (`utm_source`, `utm_medium`, `utm_campaign`) captured on lead forms
- **repeat_window**: The configurable period during which a duplicate lead from the same phone+property is suppressed
- **EARS**: Easy Approach to Requirements Syntax — the pattern language used for all acceptance criteria

---

## Requirements

### Requirement 1: Project Bootstrap and Foundation

**User Story:** As a developer, I want a fully configured Laravel 13 application skeleton, so that all subsequent features are built on a consistent, documented foundation.

#### Acceptance Criteria

1. THE System SHALL run on PHP 8.3+, Laravel 13, MySQL 8.4 LTS, Redis 7+, and Nginx/PHP-FPM as described in the design
2. THE System SHALL include the baseline models (`User`, `Role`, `Permission`, `AuditLog`, `MfaRecoveryCode`), services (`MfaService`, `StaffService`, `DatabaseAuditLogger`), middleware (`EnsureStaffIsActive`, `EnsureOwnerMfa`), and support helpers (`AreaConverter`, `MoneyFormatter`, `DisplayTimezone`) from the repository baseline
3. THE System SHALL compile front-end assets using Vite with Tailwind CSS and Alpine.js available in all Blade templates
4. WHEN `composer run setup` is executed on a fresh checkout, THE System SHALL install all dependencies, generate the application key, and run all migrations without errors

---

### Requirement 2: Staff Authentication and Role-Based Access Control

**User Story:** As an owner administrator, I want secure role-based authentication with MFA enforcement, so that only authorized staff can access the admin panel and each role is limited to its permitted actions.

#### Acceptance Criteria

1. WHEN a staff member submits valid email and password credentials, THE Auth_System SHALL authenticate the user and start an authenticated session
2. WHEN a staff member submits invalid credentials, THE Auth_System SHALL reject the login, display an error, and record a failed login audit entry via the AuditLogger
3. WHILE an owner_admin user has not yet completed TOTP verification in the current session, THE EnsureOwnerMfa_Middleware SHALL redirect all `/admin/*` requests to the MFA challenge page
4. WHEN an owner_admin user submits a valid TOTP code on the MFA challenge page, THE Auth_System SHALL mark the session as MFA-verified and record a successful login audit entry
5. WHEN an owner_admin user submits a valid unused recovery code, THE Auth_System SHALL mark that recovery code as used, set its `used_at` timestamp, and grant MFA-verified access
6. WHEN a deactivated staff member attempts to access any `/admin/*` route, THE EnsureStaffIsActive_Middleware SHALL invalidate the session and redirect the user to the login page
7. WHEN the owner_admin user deactivates another staff account, THE StaffService SHALL set `is_active` to false, purge all active sessions for that user, and record a `staff.deactivated` audit entry
8. THE owner_admin role SHALL implicitly pass all authorization checks via the `before()` gate hook, granting access to every permission without explicit assignment
9. WHEN a staff member without the required permission attempts a protected action, THE System SHALL return a 403 Forbidden response
10. THE System SHALL enforce a rate limit of 5 login attempts per minute per IP address on the admin login endpoint; WHEN the limit is exceeded, THE Auth_System SHALL return a 429 response

---

### Requirement 3: Settings and Reference Data Management

**User Story:** As an owner administrator, I want to manage application settings and reference data, so that the platform can be configured without code changes and inventory options stay current.

#### Acceptance Criteria

1. THE Settings_System SHALL provide an admin interface for reading and writing typed key-value settings, where each setting has a `group`, `key`, `value`, and `cast` (string, integer, boolean, json)
2. WHEN an owner_admin writes a setting value, THE Settings_System SHALL persist the value and return the correctly cast type when the setting is subsequently read
3. THE System SHALL provide CRUD admin interfaces for `location_areas`, `property_types`, and `amenities` reference tables
4. WHEN a location_area, property_type, or amenity is marked inactive (`is_active = false`), THE System SHALL exclude it from all new inventory creation and editing option lists
5. THE System SHALL seed the database with the three default roles (`owner_admin`, `content_editor`, `sales_user`) and their associated permission keys on initial migration

---

### Requirement 4: Media Management

**User Story:** As a content editor, I want to upload, organize, and manage property images, so that listings display high-quality visuals across all device sizes.

#### Acceptance Criteria

1. WHEN a staff member uploads an image file, THE MediaService SHALL validate that the file size does not exceed 8192 KB and that the MIME type is in the allowed list (JPEG, PNG, WebP, GIF)
2. WHEN a valid image is uploaded, THE MediaService SHALL store the original file and generate derivative images at widths 480, 768, 1280, and 1920 pixels in WebP format
3. IF an uploaded file fails size or MIME validation, THEN THE MediaService SHALL return a 422 validation error and create no media record or stored file
4. THE MediaService SHALL store each media record with `mediable_type`, `mediable_id`, `collection`, `disk`, `path`, `original_filename`, `mime_type`, `size_bytes`, `width`, `height`, and `sort_order` fields
5. THE Media model SHALL store bilingual alt text as a JSON object with `en` and `bn` string fields in the `alt_texts` column
6. WHEN an authorized staff member submits a reorder request for a media collection, THE MediaService SHALL update the `sort_order` of each media item to match the submitted ordered ID array
7. WHEN an authorized staff member deletes a media item, THE MediaService SHALL remove the database record, delete all derivative files from storage, and record an audit entry

---

### Requirement 5: Inventory Management (Projects, Properties, Units)

**User Story:** As a content editor, I want to create and manage property listings, projects, and units, so that the company's full real estate inventory is accurately represented on the platform.

#### Acceptance Criteria

1. THE InventoryService SHALL create, update, and delete `projects`, `properties`, and `units` records within database transactions, rolling back all changes if any step fails
2. WHEN a Property is created or updated with an `area_value` and `area_unit`, THE System SHALL calculate and store the `area_sqft` value using `AreaConverter::toSqft(area_value, area_unit)`
3. THE System SHALL auto-generate a unique URL-safe slug for each Property and Project from its name using sluggable logic
4. WHEN two Properties or Projects would produce an identical slug, THE System SHALL append a numeric suffix to ensure uniqueness (e.g. `apartment-gulshan`, `apartment-gulshan-2`)
5. WHEN an authorized staff member changes a Property's or Project's slug, THE System SHALL automatically create a `redirects` record from the old slug path to the new slug path
6. THE InventoryService SHALL record an audit entry for every create, update, and delete action on Projects, Properties, and Units
7. THE PropertyPolicy SHALL return `false` for create, update, delete, and publish actions when the requesting user lacks the corresponding permission key and is not an owner_admin
8. WHEN a Unit is created, THE System SHALL require a valid `property_id` foreign key referencing an existing Property

---

### Requirement 6: Publication and Editorial Workflow

**User Story:** As an owner administrator, I want an editorial approval workflow for all inventory items, so that only reviewed and approved content is visible to the public.

#### Acceptance Criteria

1. THE Publication_System SHALL manage publication state through the following ordered transitions only: `draft` → `pending_review` → `approved` → `published` → `unpublished`
2. WHEN a content editor submits a draft Property or Project for review, THE Publication_System SHALL transition its `publication_states.status` to `pending_review`
3. WHEN an owner_admin approves a `pending_review` item, THE Publication_System SHALL transition the status to `approved`
4. WHEN an authorized user publishes an `approved` Property or Project, THE Publication_System SHALL transition the status to `published`, record `published_by` (actor ID) and `published_at` (current timestamp), and flush the relevant Redis cache tags
5. WHEN an authorized user unpublishes a `published` item with a non-empty `unpublish_reason`, THE Publication_System SHALL transition the status to `unpublished`, record `unpublished_at`, and store the reason
6. IF an unpublish request is submitted with an empty `unpublish_reason`, THEN THE Publication_System SHALL return a validation error and not change the publication state
7. IF a state transition is requested from a state that does not satisfy the required precondition (e.g., publishing a draft), THEN THE Publication_System SHALL reject the transition with a 422 error
8. WHEN a Property or Project is published or unpublished, THE System SHALL flush all Redis cache tags associated with that item (`properties`, `property:{id}`, or `projects`, `project:{id}`)

---

### Requirement 7: Public Website Layout

**User Story:** As a visitor, I want a responsive, bilingual public website, so that I can browse property listings comfortably on any device in English or Bengali.

#### Acceptance Criteria

1. THE Public_Layout SHALL render a responsive Blade master layout including a navigation bar, page header area, main content slot, and footer using Tailwind CSS utility classes
2. THE Public_Layout SHALL include all compiled Vite assets (CSS and JS bundles) via `@vite` directive
3. THE System SHALL support content display in both English (`en`) and Bengali (`bn`) locales for all user-facing labels, navigation items, and property attribute names
4. WHEN a visitor navigates to any public route, THE Public_Layout SHALL set the HTML `lang` attribute to the active locale

---

### Requirement 8: Property Search

**User Story:** As a visitor, I want to search and filter properties by type, location, price, and features, so that I can quickly find listings that match my needs.

#### Acceptance Criteria

1. WHEN a visitor submits a search request, THE SearchService SHALL return only Properties whose `publication_states.status` is `published`, regardless of filter combination
2. THE SearchService SHALL support filtering by: `property_type` (key), `city`, `location_area_id`, `min_price`, `max_price`, `min_bedrooms`, `listing_type` (`sale` or `rent`), and one or more `amenity_ids`
3. WHEN multiple filters are applied simultaneously, THE SearchService SHALL apply all filters conjunctively (AND logic), returning only properties matching every specified criterion
4. THE SearchService SHALL cache each unique filter+page combination for 300 seconds using Redis tag `properties`; WHEN the same query is submitted within the cache TTL, THE SearchService SHALL return the cached result
5. THE SearchService SHALL paginate results with a default of 12 properties per page and a maximum of 24 per page
6. THE SearchService SHALL return pagination metadata (`total`, `per_page`, `current_page`, `last_page`) in every response
7. WHEN a filter combination produces no matching published properties, THE SearchService SHALL return an empty paginated result with `total = 0`

---

### Requirement 9: Property Detail Page

**User Story:** As a visitor, I want to view full details of a property listing, so that I can assess suitability before making an enquiry.

#### Acceptance Criteria

1. WHEN a visitor requests `/properties/{slug}` for a published property, THE System SHALL return an HTTP 200 response with the full property record including media, location, type, amenities, price, and area
2. WHEN a visitor requests `/properties/{slug}` for a slug that has been redirected, THE System SHALL return the redirect HTTP status code (301 or 302) configured in the `redirects` table
3. WHEN a visitor requests `/properties/{slug}` for an unpublished or non-existent slug (with no matching redirect), THE System SHALL return an HTTP 404 response
4. THE Property_Detail_Page SHALL display the price formatted via `MoneyFormatter::formatBdt`, always prefixed with `BDT ` or displaying `Price on request` when price is null
5. THE Property_Detail_Page SHALL display the area formatted via `AreaConverter::format`, showing the original value with the unit label
6. THE Property_Detail_Page SHALL cache the response in Redis under tag `property:{id}` with a TTL of 600 seconds

---

### Requirement 10: Projects Public Listing and Detail

**User Story:** As a visitor, I want to browse real estate projects and see their associated properties, so that I can understand developments available in different locations.

#### Acceptance Criteria

1. THE Projects_Listing_Page SHALL display only Projects whose `publication_states.status` is `published`, showing featured media, name, city, location area, developer name, and completion date
2. WHEN a visitor requests `/projects/{slug}` for a published project, THE System SHALL return an HTTP 200 response including the project's details and its associated published Properties
3. THE Project_Detail_Page SHALL display only Properties belonging to the project that have `publication_states.status = published`
4. WHEN a visitor requests `/projects/{slug}` for an unpublished or non-existent slug, THE System SHALL return an HTTP 404 response
5. THE Projects_Listing_Page and Project_Detail_Page SHALL cache responses in Redis under the `projects` tag with a TTL of 300 seconds

---

### Requirement 11: Map View

**User Story:** As a visitor, I want to view property locations on an interactive map, so that I can understand the geographic distribution of available listings.

#### Acceptance Criteria

1. WHEN the map view is displayed, THE Map_Component SHALL render a marker for each published property using coordinates rounded to a maximum of 2 decimal places (approximately 1 km precision)
2. THE Map_Component SHALL use Leaflet.js with OpenStreetMap tiles for rendering
3. WHEN a visitor clicks a property marker, THE Map_Component SHALL display the property name, type, price, and a link to its detail page
4. THE Map_Component SHALL only include markers for properties with `publication_states.status = published`

---

### Requirement 12: Compare and Shortlist

**User Story:** As a visitor, I want to shortlist properties and compare them side by side, so that I can make an informed purchasing decision.

#### Acceptance Criteria

1. WHEN a visitor clicks "Add to Shortlist" on a property, THE Shortlist_System SHALL persist the property ID in the browser session
2. WHEN a visitor removes a property from the shortlist, THE Shortlist_System SHALL remove the property ID from the session and update the displayed shortlist
3. THE Shortlist_System SHALL allow a visitor to shortlist a maximum of 4 properties at one time; WHEN the maximum is reached, adding a fifth property SHALL be rejected with an informative message
4. WHEN a visitor accesses the compare page with 2–4 shortlisted properties, THE Compare_View SHALL display the properties side by side with attribute rows aligned for: price, area, bedrooms, bathrooms, floor, furnished status, and amenities

---

### Requirement 13: Similar Properties

**User Story:** As a visitor, I want to see similar properties on a property detail page, so that I can discover alternative listings that match my preferences.

#### Acceptance Criteria

1. WHEN a property detail page is loaded, THE SimilarPropertiesService SHALL return at most 6 similar published properties, all distinct from the source property
2. THE SimilarPropertiesService SHALL first select properties in the same `location_area_id` and `property_type_id` with price within ±`PRICE_BAND_PERCENT` of the source property (tier 1)
3. WHEN tier 1 results are fewer than 6, THE SimilarPropertiesService SHALL fill remaining slots with properties from the same city and `property_type_id` (tier 2), excluding properties already in tier 1
4. THE SimilarPropertiesService SHALL return only Properties with `publication_states.status = published`
5. THE SimilarPropertiesService SHALL never include the source property itself in the results

---

### Requirement 14: Lead Capture

**User Story:** As a visitor, I want to submit an enquiry about a property or project, so that a sales representative can contact me with more information.

#### Acceptance Criteria

1. WHEN a visitor submits a valid enquiry form (name, phone in Bangladesh format, optional email and message, at least one of property_id or project_id), THE LeadService SHALL persist the lead record to the database before dispatching any queued notification jobs
2. WHEN a duplicate enquiry is detected (same phone number and property_id submitted within the configured `repeat_window`), THE LeadService SHALL return the existing lead record and dispatch no new notification jobs
3. WHEN a lead is captured, THE LeadService SHALL store `utm_source`, `utm_medium`, and `utm_campaign` query parameters from the HTTP request on the lead record
4. WHEN a lead is captured, THE LeadService SHALL store the submitting visitor's IP address on the lead record
5. WHEN the lead database insert fails, THE System SHALL return an HTTP 500 response with a user-friendly error message and log the error with full context; the HTTP response SHALL NOT depend on the success of downstream notification jobs
6. THE System SHALL enforce a rate limit of 10 lead capture requests per minute per IP address; WHEN the limit is exceeded, THE System SHALL return a 429 response

---

### Requirement 15: Site Visit Requests

**User Story:** As a visitor, I want to request a site visit for a property, so that I can schedule a physical inspection with a sales representative.

#### Acceptance Criteria

1. WHEN a visitor submits a site visit request with a preferred datetime, THE System SHALL create a `site_visit_requests` record linked to the associated lead (creating a new lead if none exists) and send a notification to the assigned sales user
2. THE site_visit_requests record SHALL store `lead_id`, `property_id` or `project_id`, `preferred_at`, `status` (initial value `pending`), and optionally `assigned_to` and `notes`
3. WHEN a staff member updates a site visit request status, THE System SHALL allow only valid transitions: `pending` → `confirmed`, `pending` → `cancelled`, `confirmed` → `completed`, `confirmed` → `cancelled`
4. IF an invalid status transition is attempted, THEN THE System SHALL return a validation error and preserve the current status

---

### Requirement 16: Sales Workflow

**User Story:** As a sales user, I want to log follow-up actions and manage lead assignments, so that the team can track progress and ensure no enquiry is missed.

#### Acceptance Criteria

1. WHEN a sales user logs a follow-up action on a lead, THE LeadService SHALL create a `lead_follow_ups` record with `lead_id`, `user_id`, `action_type`, `notes`, and `scheduled_at`
2. THE `action_type` field SHALL only accept the values: `call`, `email`, `whatsapp`, `meeting`, `note`
3. WHEN a follow-up action is marked completed, THE LeadService SHALL set the `completed_at` timestamp on the `lead_follow_ups` record
4. WHEN an authorized user assigns a lead to a sales user, THE System SHALL update `leads.assigned_to` and record an audit entry with the actor, old assignee, and new assignee
5. THE System SHALL allow a lead's `status` to be updated through the following values: `new`, `contacted`, `qualified`, `site_visit_scheduled`, `negotiating`, `won`, `lost`

---

### Requirement 17: Notifications

**User Story:** As a sales user, I want to receive in-app and email notifications for new leads and site visit requests, so that I can respond promptly without manually checking the admin panel.

#### Acceptance Criteria

1. WHEN a new lead is captured, THE NotifyNewLeadJob SHALL send a database notification to the `assigned_to` sales user; IF no user is assigned, THE NotifyNewLeadJob SHALL send the notification to all users with the `sales_user` role
2. WHEN a site visit request is created or its status changes, THE Notification_System SHALL send a database notification to the assigned staff member
3. THE Notification_System SHALL use Laravel's built-in polymorphic `notifications` table for in-app (database channel) notifications
4. THE Notification_System SHALL also dispatch notifications via the mail channel for new leads and site visit confirmations
5. WHEN a queued notification job fails, THE System SHALL retry the job up to 3 times with exponential backoff; lead persistence SHALL NOT be affected by notification job failures

---

### Requirement 18: Admin Dashboard

**User Story:** As an authenticated staff member, I want a personalized dashboard showing my key metrics and notifications, so that I can prioritize my work at a glance.

#### Acceptance Criteria

1. WHEN an authenticated staff member accesses the Admin_Dashboard, THE System SHALL display summary counts relevant to their role: new leads (sales_user), scheduled site visits (sales_user), and items pending review (content_editor, owner_admin)
2. THE Admin_Dashboard SHALL display a notification bell icon showing the count of unread in-app notifications for the authenticated user
3. WHEN the authenticated user marks a notification as read, THE System SHALL set `notifications.read_at` and decrement the unread count displayed on the bell
4. THE Admin_Dashboard SHALL display a list of recent leads assigned to the authenticated user (sales_user) or all recent leads (owner_admin)

---

### Requirement 19: Lead Export

**User Story:** As an owner administrator, I want to export lead data to CSV, so that I can perform offline analysis and reporting.

#### Acceptance Criteria

1. WHEN an authorized user requests a lead export with a date range, THE Export_System SHALL generate a CSV file containing all leads with `created_at` within the specified date range (inclusive)
2. THE CSV export SHALL include the following columns in order: `name`, `phone`, `email`, `property_title`, `project_name`, `source`, `utm_source`, `utm_medium`, `utm_campaign`, `status`, `assigned_to`, `created_at`
3. WHEN a lead export is requested with no date range filter, THE Export_System SHALL export all leads
4. IF a user without the `lead.export` permission requests a lead export, THEN THE System SHALL return an HTTP 403 response

---

### Requirement 20: CMS Pages and Content Blocks

**User Story:** As a content editor, I want to create and update website pages and content blocks without developer intervention, so that the site's informational content stays current.

#### Acceptance Criteria

1. THE CMS_System SHALL allow authorized staff to create, update, and delete CMS pages with the fields: `slug`, `title`, `body` (HTML), `meta_title`, `meta_description`, `status` (`draft` or `published`)
2. WHEN a CMS page `body` is saved, THE System SHALL pass the HTML through an HTML purifier that removes disallowed tags and attributes before storing it in the database
3. THE CMS_System SHALL support key-based `cms_blocks` records for structured content; WHEN a block's content is updated by an authorized user, THE System SHALL persist the updated `content` JSON and record an audit entry
4. WHEN a CMS page is published, THE System SHALL make it accessible at `/{slug}` and include it in the sitemap
5. WHEN a CMS page is deleted, THE System SHALL remove it from public routes and the sitemap

---

### Requirement 21: Homepage

**User Story:** As a visitor, I want an engaging homepage that highlights featured properties and projects, so that I can quickly discover the company's key offerings.

#### Acceptance Criteria

1. THE Homepage SHALL display featured Projects selected via a CMS block or settings flag, showing project name, featured image, city, and a link to the project detail page
2. THE Homepage SHALL display featured Properties selected via a CMS block or settings flag, showing property title, featured image, price (formatted via `MoneyFormatter::formatBdt`), area, and a link to the property detail page
3. THE Homepage SHALL render CMS content blocks (hero text, about section, contact details) from the `cms_blocks` table using their defined keys
4. THE Homepage response SHALL be cached in Redis to serve repeat visitors without database queries

---

### Requirement 22: SEO and Sitemap

**User Story:** As an owner administrator, I want SEO metadata and an automated sitemap, so that search engines can accurately index the platform's content.

#### Acceptance Criteria

1. THE SEO_System SHALL render `<meta name="title">`, `<meta name="description">`, and `<meta property="og:image">` tags on all public pages; WHEN a `seo_overrides` record exists for a page, THE SEO_System SHALL use the override values; otherwise it SHALL fall back to the model's default title and description
2. WHEN a property or project has `noindex = true` in its `seo_overrides` record, THE SEO_System SHALL render `<meta name="robots" content="noindex">` on that page
3. THE Sitemap_Generator SHALL produce a valid XML sitemap containing URLs only for published Properties, published Projects, and published CMS pages, plus the static routes `/`, `/properties`, and `/projects`
4. WHEN the sitemap is generated, THE System SHALL include `<loc>`, `<lastmod>`, `<changefreq>`, and `<priority>` elements for each URL as specified in the design
5. THE sitemap response SHALL be cached in Redis under tag `sitemap` with a TTL of 3600 seconds; WHEN any Property, Project, or CMS page is published or unpublished, THE System SHALL flush the `sitemap` cache tag
6. WHEN a Property or Project slug changes, THE System SHALL create a `redirects` record from the old slug path to the new slug path with the configured HTTP status code

---

### Requirement 23: Analytics Integration

**User Story:** As an owner administrator, I want to embed analytics and tracking scripts on all public pages, so that visitor behaviour and lead sources can be measured.

#### Acceptance Criteria

1. THE System SHALL embed a configurable analytics script in the public layout `<head>` section; WHEN the `analytics_script` setting contains a non-empty value, THE Public_Layout SHALL render it as a raw (unescaped) script tag
2. WHEN the `analytics_script` setting is empty or null, THE Public_Layout SHALL render no analytics script tag
3. THE System SHALL store UTM attribution fields (`utm_source`, `utm_medium`, `utm_campaign`) on every lead record for source analysis
4. THE admin lead list SHALL allow filtering by `utm_source` and `utm_campaign` to support attribution reporting

---

### Requirement 24: Security

**User Story:** As an owner administrator, I want the platform to implement defence-in-depth security controls, so that staff data, customer leads, and site content are protected against common web attacks.

#### Acceptance Criteria

1. THE System SHALL apply Laravel's CSRF middleware to all state-changing HTTP requests (POST, PUT, PATCH, DELETE); WHEN a request is submitted without a valid CSRF token, THE System SHALL return an HTTP 419 response
2. THE System SHALL escape all user-supplied data rendered in Blade templates using double-brace `{{ }}` syntax; raw `{!! !!}` output SHALL be used only for CMS `body` HTML that has been passed through the HTML purifier
3. THE System SHALL use Eloquent ORM or parameterized query builder bindings for all database queries; no user-supplied values SHALL be interpolated directly into SQL strings
4. THE MediaService SHALL validate MIME type server-side using file content inspection (not filename extension alone); IF an uploaded file has an executable MIME type, THEN THE MediaService SHALL reject it with a 422 error
5. THE MediaService SHALL strip EXIF metadata from all uploaded images before storage
6. THE System SHALL store the `mfa_secret` field using Laravel's `encrypted` cast; the raw secret value SHALL never appear in application logs
7. WHEN an authenticated admin session is active, THE System SHALL set secure, HttpOnly, SameSite=Lax cookie attributes on the session cookie
8. THE System SHALL apply the `EnsureStaffIsActive` middleware to all `/admin/*` routes to terminate sessions of deactivated staff on the next request

---

### Requirement 25: Deployment and Launch Readiness

**User Story:** As a system operator, I want a documented, automated deployment process, so that the application can be reliably deployed and maintained on production infrastructure.

#### Acceptance Criteria

1. THE System SHALL provide Nginx server block configuration and PHP-FPM pool configuration files for the production environment
2. THE System SHALL use environment variables (`.env`) for all environment-specific configuration (database credentials, Redis connection, mail settings, S3 keys, app key)
3. THE System SHALL pass all PHPUnit tests in CI (`composer test`) without failures before deployment
4. WHEN `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache` are run in production, THE System SHALL serve cached configuration, routes, and views without errors
5. THE System SHALL configure the queue worker with `QUEUE_FALLBACK_CONNECTION=database` so that queued jobs are persisted to the database when the Redis queue is unavailable
6. WHEN a queue worker restarts after downtime, THE System SHALL process all accumulated jobs from the database queue without data loss
