<?php

namespace App\Providers;

use App\Contracts\AuditLogger;
use App\Contracts\InventoryService as InventoryServiceContract;
use App\Contracts\LeadService as LeadServiceContract;
use App\Contracts\MediaService as MediaServiceContract;
use App\Contracts\SearchService as SearchServiceContract;
use App\Contracts\SimilarPropertiesService;
use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Project;
use App\Models\Property;
use App\Models\SiteVisitRequest;
use App\Models\User;
use App\Policies\CmsBlockPolicy;
use App\Policies\CmsPagePolicy;
use App\Policies\LeadPolicy;
use App\Policies\PostPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\SiteVisitRequestPolicy;
use App\Policies\StaffPolicy;
use App\Services\Audit\DatabaseAuditLogger;
use App\Services\Inventory\InventoryService;
use App\Services\Lead\LeadService;
use App\Services\Media\MediaService;
use App\Services\Search\SearchService;
use ArrayObject;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuditLogger::class, DatabaseAuditLogger::class);
        $this->app->singleton(InventoryServiceContract::class, InventoryService::class);
        $this->app->singleton(LeadServiceContract::class, LeadService::class);
        $this->app->singleton(SearchServiceContract::class, SearchService::class);
        $this->app->singleton(MediaServiceContract::class, MediaService::class);
        $this->app->singleton(SimilarPropertiesService::class, \App\Services\Search\SimilarPropertiesService::class);
        $this->app->scoped('uh.settings', fn (): ArrayObject => new ArrayObject);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(DiagnosingHealth::class, function (): void {
            DB::select('select 1');
            Cache::put('uh:health', now()->timestamp, 60);
        });

        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(SiteVisitRequest::class, SiteVisitRequestPolicy::class);
        Gate::policy(CmsPage::class, CmsPagePolicy::class);
        Gate::policy(CmsBlock::class, CmsBlockPolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(User::class, StaffPolicy::class);

        Gate::before(function (User $user): ?bool {
            if ($user->isOwnerAdmin()) {
                return true;
            }

            return null;
        });

        foreach (['audit.view', 'redirect.manage', 'report.view', 'settings.update', 'reference.manage'] as $ability) {
            Gate::define($ability, fn (User $user): bool => $user->hasPermission($ability));
        }

        RateLimiter::for('lead-submissions', fn (Request $request): Limit => Limit::perMinute(6)->by($request->ip())
            ->response(fn () => $request->expectsJson()
                ? response()->json(['message' => 'Too many submissions. Please wait a minute and try again, or call us.'], 429)
                : back()->withInput()->withErrors(['form' => 'Too many submissions. Please wait a minute and try again, or call us.'])));

        RateLimiter::for('public-json', fn (Request $request): Limit => Limit::perMinute(120)->by($request->ip()));
        RateLimiter::for('tracking', fn (Request $request): Limit => Limit::perMinute(60)->by($request->ip()));
    }
}
