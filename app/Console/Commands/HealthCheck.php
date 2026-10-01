<?php

namespace App\Console\Commands;

use App\Support\HealthStatus;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('uh:health')]
#[Description('Check database, cache, storage, queue backlog and mail configuration')]
class HealthCheck extends Command
{
    public function handle(): int
    {
        $checks = HealthStatus::run();

        $this->table(['Check', 'Status', 'Detail'], collect($checks)->map(fn (array $check, string $name): array => [$name, $check['ok'] ? 'OK' : 'FAIL', $check['detail']])->values()->all());

        return collect($checks)->every(fn (array $check): bool => $check['ok'] || ! $check['critical']) ? self::SUCCESS : self::FAILURE;
    }
}
