<?php

namespace App\Models\Concerns;

use App\Models\SeoOverride;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    public function seoOverride(): MorphOne
    {
        return $this->morphOne(SeoOverride::class, 'seoable');
    }
}
