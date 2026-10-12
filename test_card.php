<?php

use App\Models\Property;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$prop = Property::with('media')->first();
echo 'Total Media Loaded: '.$prop->media->count()."\n";
echo 'Gallery Images: '.$prop->galleryImages()->count()."\n";

foreach ($prop->galleryImages() as $img) {
    echo $img->url(768)."\n";
    echo $img->srcset()."\n";
}
