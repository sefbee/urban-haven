<?php

namespace App\Models\Concerns;

use App\Models\PublicationState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Publishable
{
    use HasSeo;

    public function publicationState(): MorphOne
    {
        return $this->morphOne(PublicationState::class, 'publishable');
    }

    public function editorialStatus(): string
    {
        return $this->publicationState?->status ?? PublicationState::DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->editorialStatus() === PublicationState::PUBLISHED;
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereHas('publicationState', function (Builder $builder): void {
            $builder->where('status', PublicationState::PUBLISHED);
        });
    }
}
