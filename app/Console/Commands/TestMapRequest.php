<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

class TestMapRequest extends Command
{
    protected $signature = 'test:map';

    public function handle(Kernel $kernel)
    {
        $request = Request::create('/map', 'GET', ['facing' => 'north']);
        $response = $kernel->handle($request);
        $this->info('Status: '.$response->getStatusCode());
        if ($response->getStatusCode() === 500) {
            $this->error($response->exception->getMessage());
            $this->error($response->exception->getTraceAsString());
        }
    }
}
