<?php

namespace App\Console\Commands;

use App\Contracts\InventoryService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('uh:release-reservations')]
#[Description('Return reserved properties whose reservation expiry has passed to available, with history')]
class ReleaseExpiredReservations extends Command
{
    public function handle(InventoryService $inventory): int
    {
        $released = $inventory->releaseExpiredReservations();
        $this->info("Released {$released} expired ".str('reservation')->plural($released).'.');

        return self::SUCCESS;
    }
}
