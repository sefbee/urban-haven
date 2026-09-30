<?php

namespace Tests\Feature\Bootstrap;

use App\Contracts\AuditLogger;
use App\Contracts\InventoryService;
use App\Contracts\LeadService;
use App\Contracts\MediaService;
use App\Contracts\SearchService;
use App\Services\Audit\DatabaseAuditLogger;
use App\Services\Inventory\InventoryService as InventoryServiceImplementation;
use App\Services\Lead\LeadService as LeadServiceImplementation;
use App\Services\Media\MediaService as MediaServiceImplementation;
use App\Services\Search\SearchService as SearchServiceImplementation;
use Tests\TestCase;

class FoundationBindingsTest extends TestCase
{
    public function test_audit_logger_contract_resolves_to_database_implementation(): void
    {
        $this->assertInstanceOf(DatabaseAuditLogger::class, app(AuditLogger::class));
    }

    public function test_domain_service_contracts_resolve_to_application_implementations(): void
    {
        $this->assertInstanceOf(InventoryServiceImplementation::class, app(InventoryService::class));
        $this->assertInstanceOf(LeadServiceImplementation::class, app(LeadService::class));
        $this->assertInstanceOf(SearchServiceImplementation::class, app(SearchService::class));
        $this->assertInstanceOf(MediaServiceImplementation::class, app(MediaService::class));
    }
}
