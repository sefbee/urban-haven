<?php

namespace App\Models;

use App\Support\TaggedCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['question', 'answer', 'group', 'sort_order', 'is_visible'];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('group')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Visible questions from the shared CMS cache, flushed whenever FAQs change.
     *
     * @return Collection<int, static>
     */
    public static function cachedVisible(): Collection
    {
        return static::hydrate(TaggedCache::remember(['cms'], 'faqs.visible.rows', 3600, fn (): array => static::query()
            ->visible()
            ->get()
            ->map(fn (Faq $faq): array => $faq->getAttributes())
            ->all()));
    }
}
