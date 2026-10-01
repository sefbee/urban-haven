<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('uh:prune-exports')]
#[Description('Delete queued lead export files older than 24 hours')]
class PruneExports extends Command
{
    public function handle(): int
    {
        $disk = Storage::disk('local');
        $deleted = 0;

        foreach ($disk->allFiles('exports') as $file) {
            if ($disk->lastModified($file) < now()->subDay()->timestamp) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} expired export files.");

        return self::SUCCESS;
    }
}
