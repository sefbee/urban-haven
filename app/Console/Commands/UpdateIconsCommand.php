<?php

namespace App\Console\Commands;

use App\Models\Amenity;
use Illuminate\Console\Command;

class UpdateIconsCommand extends Command
{
    protected $signature = 'icons:update';

    protected $description = 'Update legacy icons in DB to pro';

    public function handle()
    {
        $count = 0;
        foreach (Amenity::all() as $a) {
            $a->icon_class = str_replace(['fa-solid', 'fa-regular', 'fa-light'], 'fa-duotone', $a->icon_class);
            if ($a->isDirty()) {
                $a->save();
                $count++;
            }
        }
        $this->info("Updated {$count} amenities.");
    }
}
