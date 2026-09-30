<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CmsPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\LeadExportController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Admin\PublicationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SiteVisitController as AdminSiteVisitController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Public\CmsController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LeadController;
use App\Http\Controllers\Public\LocaleController;
use App\Http\Controllers\Public\MapDataController;
use App\Http\Controllers\Public\ProjectController;
use App\Http\Controllers\Public\PropertyController;
use App\Http\Controllers\Public\PropertySearchController;
use App\Http\Controllers\Public\ShortlistController;
use App\Http\Controllers\Public\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/properties', [PropertySearchController::class, 'index'])->name('properties.index');
Route::get('/properties/{slug}', [PropertyController::class, 'show'])->name('properties.show');
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/compare', [ShortlistController::class, 'index'])->name('compare');
Route::post('/shortlist', [ShortlistController::class, 'add'])->name('shortlist.add');
Route::delete('/shortlist/{property}', [ShortlistController::class, 'remove'])->name('shortlist.remove');
Route::post('/inquiries', [LeadController::class, 'store'])->middleware('throttle:10,1')->name('inquiries.store');
Route::post('/visits', [LeadController::class, 'visit'])->middleware('throttle:10,1')->name('visits.store');
Route::post('/locale', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/map-data', MapDataController::class)->name('map.data');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'showLogin'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware(['auth', 'staff.active'])->group(function () {
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('notifications/{notification}', [NotificationController::class, 'markRead'])->name('notifications.read');

        Route::resource('staff', StaffController::class)->except(['show', 'destroy']);
        Route::post('staff/{staff}/deactivate', [StaffController::class, 'deactivate'])->name('staff.deactivate');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/areas', [SettingsController::class, 'storeArea'])->name('settings.areas.store');
        Route::post('settings/areas/{area}/deactivate', [SettingsController::class, 'deactivateArea'])->name('settings.areas.deactivate');
        Route::post('settings/types', [SettingsController::class, 'storeType'])->name('settings.types.store');
        Route::post('settings/types/{type}/deactivate', [SettingsController::class, 'deactivateType'])->name('settings.types.deactivate');
        Route::post('settings/amenities', [SettingsController::class, 'storeAmenity'])->name('settings.amenities.store');
        Route::post('settings/amenities/{amenity}/deactivate', [SettingsController::class, 'deactivateAmenity'])->name('settings.amenities.deactivate');

        Route::resource('properties', AdminPropertyController::class)->except(['show']);
        Route::post('properties/{property}/submit', [PublicationController::class, 'submitProperty'])->name('properties.submit');
        Route::post('properties/{property}/approve', [PublicationController::class, 'approveProperty'])->name('properties.approve');
        Route::post('properties/{property}/publish', [PublicationController::class, 'publishProperty'])->name('properties.publish');
        Route::post('properties/{property}/unpublish', [PublicationController::class, 'unpublishProperty'])->name('properties.unpublish');
        Route::get('properties/{property}/units/create', [UnitController::class, 'create'])->name('units.create');
        Route::post('properties/{property}/units', [UnitController::class, 'store'])->name('units.store');
        Route::delete('units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

        Route::resource('projects', AdminProjectController::class)->except(['show']);
        Route::post('projects/{project}/submit', [PublicationController::class, 'submitProject'])->name('projects.submit');
        Route::post('projects/{project}/approve', [PublicationController::class, 'approveProject'])->name('projects.approve');
        Route::post('projects/{project}/publish', [PublicationController::class, 'publishProject'])->name('projects.publish');
        Route::post('projects/{project}/unpublish', [PublicationController::class, 'unpublishProject'])->name('projects.unpublish');

        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::put('media/reorder', [MediaController::class, 'reorder'])->name('media.reorder');
        Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

        Route::get('leads', [AdminLeadController::class, 'index'])->name('leads.index');
        Route::get('leads/export', [LeadExportController::class, 'export'])->name('leads.export');
        Route::get('leads/{lead}', [AdminLeadController::class, 'show'])->name('leads.show');
        Route::post('leads/{lead}/assign', [AdminLeadController::class, 'assign'])->name('leads.assign');
        Route::post('leads/{lead}/status', [AdminLeadController::class, 'updateStatus'])->name('leads.status');
        Route::post('leads/{lead}/notes', [AdminLeadController::class, 'addNote'])->name('leads.notes');
        Route::post('leads/{lead}/follow-ups', [AdminLeadController::class, 'addFollowUp'])->name('leads.follow-ups');
        Route::post('follow-ups/{followUp}/complete', [AdminLeadController::class, 'completeFollowUp'])->name('follow-ups.complete');

        Route::get('visits', [AdminSiteVisitController::class, 'index'])->name('visits.index');
        Route::post('visits/{visit}/status', [AdminSiteVisitController::class, 'updateStatus'])->name('visits.status');

        Route::get('cms', [CmsPageController::class, 'index'])->name('cms.index');
        Route::get('cms/create', [CmsPageController::class, 'create'])->name('cms.create');
        Route::post('cms', [CmsPageController::class, 'store'])->name('cms.store');
        Route::get('cms/{page}/edit', [CmsPageController::class, 'edit'])->name('cms.edit');
        Route::put('cms/{page}', [CmsPageController::class, 'update'])->name('cms.update');
        Route::post('cms/{page}/publish', [CmsPageController::class, 'publish'])->name('cms.publish');
        Route::delete('cms/{page}', [CmsPageController::class, 'destroy'])->name('cms.destroy');
        Route::put('cms/blocks/{block}', [CmsPageController::class, 'updateBlock'])->name('cms.blocks.update');
    });
});

Route::get('/{slug}', [CmsController::class, 'show'])->where('slug', '^(?!admin|properties|projects|compare|inquiries|visits|locale|sitemap\.xml|map-data|up|build|storage).*$')->name('cms.show');
