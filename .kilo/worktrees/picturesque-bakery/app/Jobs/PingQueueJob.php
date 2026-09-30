<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PingQueueJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $marker = 'urban-haven-queue-ok') {}

    public function handle(): void
    {
        Log::info('Queue ping processed.', ['marker' => $this->marker]);
    }
}
