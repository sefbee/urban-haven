<?php

use App\Models\Property;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$prop = Property::with('media')->first();
echo 'Property relation loaded: '.($prop->relationLoaded('media') ? 'Yes' : 'No')."\n";
echo 'Media count: '.$prop->media->count()."\n";
foreach ($prop->media as $m) {
    echo ' - Collection: '.$m->collection.', is_public: '.$m->is_public."\n";
}
