<?php

namespace App\Providers;

use App\Contracts\AuditLogger;
use App\Contracts\InventoryService as InventoryServiceContract;
use App\Contracts\LeadService as LeadServiceContract;
use App\Contracts\MediaService as MediaServiceContract;
use App\Contracts\SearchService as SearchServiceContract;
use App\Contracts\SimilarPropertiesService;
use App\Models\CmsPage;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use App\Policies\CmsPagePolicy;
use App\Policies\LeadPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\StaffPolicy;
use App\Services\Audit\DatabaseAuditLogger;
use App\Services\Inventory\InventoryService;
use App\Services\Lead\LeadService;
use App\Services\Media\MediaService;
use App\Services\Search\SearchService;
use Illuminate\Support\Facades\Gate;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(CmsPage::class, CmsPagePolicy::class);
        Gate::policy(User::class, StaffPolicy::class);

        Gate::before(function (User $user): ?bool {
            if ($user->isOwnerAdmin()) {
                return true;
            }

            return null;
        });
    }
}
