<?php

use App\Http\Controllers\Admin\AdminSearchController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\MfaController;
use App\Http\Controllers\Admin\CmsPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\LeadExportController;
use App\Http\Controllers\Admin\LocationAreaController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PageSeoController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Admin\PropertyTypeController;
use App\Http\Controllers\Admin\PublicationController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SiteUrlController;
use App\Http\Controllers\Admin\SiteVisitController as AdminSiteVisitController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\CmsController;
use App\Http\Controllers\Public\CustomerAccountController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LeadController;
use App\Http\Controllers\Public\LocaleController;
use App\Http\Controllers\Public\LocationController;
use App\Http\Controllers\Public\LocationSuggestionController;
use App\Http\Controllers\Public\MapDataController;
use App\Http\Controllers\Public\MediaDownloadController;
use App\Http\Controllers\Public\PropertyController;
use App\Http\Controllers\Public\PropertyCountController;
use App\Http\Controllers\Public\PropertySearchController;
use App\Http\Controllers\Public\RobotsController;
use App\Http\Controllers\Public\ShortlistController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\ToolsController;
use App\Http\Controllers\Public\TrackingController;
use App\Support\HomeSections;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/properties', [PropertySearchController::class, 'index'])->name('properties.index');
Route::get('/map', [PropertySearchController::class, 'map'])->name('map');
Route::get('/search/locations', LocationSuggestionController::class)->middleware('throttle:public-json')->name('search.locations');
Route::get('/properties/map', MapDataController::class)->middleware('throttle:public-json')->name('properties.map');
Route::get('/properties/count', PropertyCountController::class)->middleware('throttle:public-json')->name('properties.count');
Route::get('/properties/{slug}', [PropertyController::class, 'show'])->name('properties.show');
Route::permanentRedirect('/projects', '/properties');
Route::permanentRedirect('/projects/{slug}', '/properties');
Route::get('/locations/{slug}', [LocationController::class, 'show'])->name('locations.show');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/faq', [FaqController::class, 'index'])->name('faq');
Route::get('/shortlist', [ShortlistController::class, 'shortlist'])->name('shortlist');
Route::get('/compare', [ShortlistController::class, 'compare'])->name('compare');
Route::get('/saved/cards', [ShortlistController::class, 'cards'])->middleware('throttle:public-json')->name('saved.cards');
Route::post('/inquiries', [LeadController::class, 'store'])->middleware('throttle:lead-submissions')->name('inquiries.store');
Route::post('/visit-requests', [LeadController::class, 'visit'])->middleware('throttle:lead-submissions')->name('visits.store');
Route::post('/account/register', [CustomerAccountController::class, 'register'])->middleware('throttle:5,1')->name('account.register');
Route::post('/account/login', [CustomerAccountController::class, 'login'])->middleware('throttle:5,1')->name('account.login');
Route::middleware('auth')->prefix('account')->name('account.')->group(function (): void {
    Route::get('/', [CustomerAccountController::class, 'show'])->name('show');
    Route::post('/logout', [CustomerAccountController::class, 'logout'])->name('logout');
});
Route::get('/thank-you', [LeadController::class, 'thankYou'])->name('thank-you');
Route::post('/track', TrackingController::class)->middleware('throttle:tracking')->name('track');
Route::get('/media/{media}/download', MediaDownloadController::class)->middleware('throttle:public-json')->name('media.download');
Route::post('/locale', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemaps/{section}.xml', [SitemapController::class, 'section'])->whereIn('section', SitemapController::SECTIONS)->name('sitemap.section');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/tools', [ToolsController::class, 'index'])->name('tools');
Route::get('/legal', [ToolsController::class, 'legal'])->name('legal');
Route::permanentRedirect('/map-data', '/properties/map');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'showLogin'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    });

    Route::middleware(['auth', 'staff.active'])->group(function () {
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('mfa/setup', [MfaController::class, 'setup'])->name('mfa.setup');
        Route::post('mfa/setup', [MfaController::class, 'confirm'])->middleware('throttle:10,1')->name('mfa.confirm');
        Route::get('mfa/challenge', [MfaController::class, 'challenge'])->name('mfa.challenge');
        Route::post('mfa/challenge', [MfaController::class, 'verify'])->middleware('throttle:10,1')->name('mfa.verify');
    });

    Route::middleware(['auth', 'staff.active', 'staff.mfa'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('notifications/{notification}', [NotificationController::class, 'markRead'])->name('notifications.read');

        Route::resource('staff', StaffController::class)->except(['show', 'destroy']);
        Route::post('staff/{staff}/deactivate', [StaffController::class, 'deactivate'])->name('staff.deactivate');
        Route::post('staff/{staff}/mfa-reset', [StaffController::class, 'resetMfa'])->name('staff.mfa.reset');
        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit.index');

        Route::get('search', AdminSearchController::class)->name('search');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('settings/theme', [SettingsController::class, 'theme'])->name('settings.theme');
        Route::get('settings/contact', [SettingsController::class, 'contact'])->name('settings.contact');
        Route::get('settings/enquiries', [SettingsController::class, 'enquiries'])->name('settings.enquiries');
        Route::get('settings/analytics', [SettingsController::class, 'analytics'])->name('settings.analytics');
        Route::get('settings/seo', [SettingsController::class, 'seo'])->name('settings.seo');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::put('settings/social', [SettingsController::class, 'updateSocial'])->name('settings.social');
        Route::post('settings/test-email', [SettingsController::class, 'testEmail'])->middleware('throttle:5,1')->name('settings.test-email');
        Route::get('listing-display', [SettingsController::class, 'listings'])->name('listing-display');

        Route::get('home-sections', [HomeSectionController::class, 'index'])->name('home-sections.index');
        Route::prefix('home-sections/{section}')->whereIn('section', HomeSections::keys())->name('home-sections.')->group(function () {
            Route::get('/', [HomeSectionController::class, 'edit'])->name('edit');
            Route::put('/', [HomeSectionController::class, 'update'])->name('update');
            Route::post('visibility', [HomeSectionController::class, 'visibility'])->name('visibility');
            Route::post('move', [HomeSectionController::class, 'move'])->name('move');
            Route::post('publish', [HomeSectionController::class, 'publish'])->name('publish');
            Route::post('discard', [HomeSectionController::class, 'discard'])->name('discard');
            Route::post('reset', [HomeSectionController::class, 'reset'])->name('reset');
        });

        Route::get('areas', [LocationAreaController::class, 'index'])->name('areas.index');
        Route::post('areas', [LocationAreaController::class, 'store'])->name('areas.store');
        Route::put('areas/{area}', [LocationAreaController::class, 'update'])->name('areas.update');
        Route::post('areas/{area}/deactivate', [LocationAreaController::class, 'deactivate'])->name('areas.deactivate');

        Route::get('property-types', [PropertyTypeController::class, 'index'])->name('property-types.index');
        Route::post('property-types', [PropertyTypeController::class, 'store'])->name('property-types.store');
        Route::put('property-types/{type}', [PropertyTypeController::class, 'update'])->name('property-types.update');
        Route::post('property-types/{type}/deactivate', [PropertyTypeController::class, 'deactivate'])->name('property-types.deactivate');

        Route::get('amenities', [AmenityController::class, 'index'])->name('amenities.index');
        Route::post('amenities', [AmenityController::class, 'store'])->name('amenities.store');
        Route::put('amenities/{amenity}', [AmenityController::class, 'update'])->name('amenities.update');
        Route::post('amenities/{amenity}/deactivate', [AmenityController::class, 'deactivate'])->name('amenities.deactivate');

        Route::post('settings/areas', [LocationAreaController::class, 'store'])->name('settings.areas.store');
        Route::put('settings/areas/{area}', [LocationAreaController::class, 'update'])->name('settings.areas.update');
        Route::post('settings/areas/{area}/deactivate', [LocationAreaController::class, 'deactivate'])->name('settings.areas.deactivate');
        Route::post('settings/types', [PropertyTypeController::class, 'store'])->name('settings.types.store');
        Route::put('settings/types/{type}', [PropertyTypeController::class, 'update'])->name('settings.types.update');
        Route::post('settings/types/{type}/deactivate', [PropertyTypeController::class, 'deactivate'])->name('settings.types.deactivate');
        Route::post('settings/amenities', [AmenityController::class, 'store'])->name('settings.amenities.store');
        Route::post('settings/amenities/{amenity}/deactivate', [AmenityController::class, 'deactivate'])->name('settings.amenities.deactivate');

        Route::get('review', [PublicationController::class, 'reviewQueue'])->name('review.index');

        Route::resource('properties', AdminPropertyController::class)->except(['show']);
        Route::put('properties/{property}/availability', [AdminPropertyController::class, 'updateAvailability'])->name('properties.availability');
        Route::post('properties/{property}/submit', [PublicationController::class, 'submitProperty'])->name('properties.submit');
        Route::post('properties/{property}/approve', [PublicationController::class, 'approveProperty'])->name('properties.approve');
        Route::post('properties/{property}/publish', [PublicationController::class, 'publishProperty'])->name('properties.publish');
        Route::post('properties/{property}/unpublish', [PublicationController::class, 'unpublishProperty'])->name('properties.unpublish');
        Route::post('properties/{property}/return', [PublicationController::class, 'returnProperty'])->name('properties.return');
        Route::get('properties/{property}/units/create', [UnitController::class, 'create'])->name('units.create');
        Route::post('properties/{property}/units', [UnitController::class, 'store'])->name('units.store');
        Route::put('units/{unit}', [UnitController::class, 'update'])->name('units.update');
        Route::delete('units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

        Route::resource('projects', AdminProjectController::class)->except(['show']);
        Route::post('projects/{project}/submit', [PublicationController::class, 'submitProject'])->name('projects.submit');
        Route::post('projects/{project}/approve', [PublicationController::class, 'approveProject'])->name('projects.approve');
        Route::post('projects/{project}/publish', [PublicationController::class, 'publishProject'])->name('projects.publish');
        Route::post('projects/{project}/unpublish', [PublicationController::class, 'unpublishProject'])->name('projects.unpublish');
        Route::post('projects/{project}/return', [PublicationController::class, 'returnProject'])->name('projects.return');

        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::put('media/reorder', [MediaController::class, 'reorder'])->name('media.reorder');
        Route::patch('media/{medium}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

        Route::get('leads', [AdminLeadController::class, 'index'])->name('leads.index');
        Route::get('leads/export', [LeadExportController::class, 'export'])->name('leads.export');
        Route::get('leads/export/{file}', [LeadExportController::class, 'download'])->middleware('signed')->name('leads.export.download');
        Route::get('leads/{lead}', [AdminLeadController::class, 'show'])->name('leads.show');
        Route::post('leads/{lead}/assign', [AdminLeadController::class, 'assign'])->name('leads.assign');
        Route::post('leads/{lead}/status', [AdminLeadController::class, 'updateStatus'])->name('leads.status');
        Route::post('leads/{lead}/priority', [AdminLeadController::class, 'updatePriority'])->name('leads.priority');
        Route::post('leads/{lead}/notes', [AdminLeadController::class, 'addNote'])->name('leads.notes');
        Route::post('leads/{lead}/follow-ups', [AdminLeadController::class, 'addFollowUp'])->name('leads.follow-ups');
        Route::get('follow-ups', [AdminLeadController::class, 'followUps'])->name('follow-ups.index');
        Route::post('follow-ups/{followUp}/complete', [AdminLeadController::class, 'completeFollowUp'])->name('follow-ups.complete');
        Route::post('follow-ups/{followUp}/cancel', [AdminLeadController::class, 'cancelFollowUp'])->name('follow-ups.cancel');

        Route::get('visits', [AdminSiteVisitController::class, 'index'])->name('visits.index');
        Route::post('visits/{visit}/status', [AdminSiteVisitController::class, 'updateStatus'])->name('visits.status');

        Route::get('cms', [CmsPageController::class, 'index'])->name('cms.index');
        Route::get('cms/create', [CmsPageController::class, 'create'])->name('cms.create');
        Route::post('cms', [CmsPageController::class, 'store'])->name('cms.store');
        Route::get('cms/{page}/edit', [CmsPageController::class, 'edit'])->name('cms.edit');
        Route::get('cms/{page}/preview', [CmsPageController::class, 'preview'])->name('cms.preview');
        Route::put('cms/{page}', [CmsPageController::class, 'update'])->name('cms.update');
        Route::post('cms/{page}/publish', [CmsPageController::class, 'publish'])->name('cms.publish');
        Route::post('cms/{page}/unpublish', [CmsPageController::class, 'unpublish'])->name('cms.unpublish');
        Route::delete('cms/{page}', [CmsPageController::class, 'destroy'])->name('cms.destroy');
        Route::put('cms/blocks/{block}', [CmsPageController::class, 'updateBlock'])->name('cms.blocks.update');
        Route::post('cms/blocks/{block}/publish', [CmsPageController::class, 'publishBlock'])->name('cms.blocks.publish');

        Route::resource('posts', AdminPostController::class)->except(['show']);
        Route::get('posts/{post}/preview', [AdminPostController::class, 'preview'])->name('posts.preview');
        Route::post('posts/{post}/publish', [AdminPostController::class, 'publish'])->name('posts.publish');
        Route::post('posts/{post}/unpublish', [AdminPostController::class, 'unpublish'])->name('posts.unpublish');
        Route::post('post-categories', [AdminPostController::class, 'storeCategory'])->name('post-categories.store');
        Route::get('post-categories', [PostCategoryController::class, 'index'])->name('post-categories.index');
        Route::put('post-categories/{category}', [PostCategoryController::class, 'update'])->name('post-categories.update');
        Route::delete('post-categories/{category}', [PostCategoryController::class, 'destroy'])->name('post-categories.destroy');

        Route::get('page-seo', [PageSeoController::class, 'index'])->name('page-seo.index');
        Route::put('page-seo', [PageSeoController::class, 'update'])->name('page-seo.update');
        Route::get('site-urls', SiteUrlController::class)->name('site-urls');

        Route::resource('faqs', AdminFaqController::class)->except(['show', 'create', 'edit']);
        Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
        Route::post('menus', [MenuController::class, 'store'])->name('menus.store');
        Route::put('menus/{item}', [MenuController::class, 'update'])->name('menus.update');
        Route::post('menus/{item}/visibility', [MenuController::class, 'visibility'])->name('menus.visibility');
        Route::post('menus/{item}/move', [MenuController::class, 'move'])->name('menus.move');
        Route::delete('menus/{item}', [MenuController::class, 'destroy'])->name('menus.destroy');

        Route::get('redirects', [RedirectController::class, 'index'])->name('redirects.index');
        Route::post('redirects', [RedirectController::class, 'store'])->name('redirects.store');
        Route::post('redirects/import', [RedirectController::class, 'import'])->name('redirects.import');
        Route::post('redirects/{redirect}/toggle', [RedirectController::class, 'toggle'])->name('redirects.toggle');
        Route::delete('redirects/{redirect}', [RedirectController::class, 'destroy'])->name('redirects.destroy');
    });
});

Route::get('/{slug}', [CmsController::class, 'show'])
    ->where('slug', '^(?!admin|properties|map|search|projects|locations|articles|faq|shortlist|compare|saved|inquiries|visit-requests|thank-you|track|media|locale|sitemap|sitemaps|robots\.txt|map-data|tools|legal|up|build|storage)[a-z0-9-]+$')
    ->name('cms.show');
