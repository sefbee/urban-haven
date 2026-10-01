<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\Publishable;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasMedia, HasSlug, Publishable;

    protected $fillable = [
        'title',
        'slug',
        'post_category_id',
        'excerpt',
        'body',
        'pending_changes',
        'author_label',
        'related_post_ids',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'pending_changes' => 'array',
            'related_post_ids' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Post $post): void {
            $post->publicationState()->create(['status' => PublicationState::DRAFT]);
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->preventOverwrite();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function hasPendingChanges(): bool
    {
        return ! empty($this->pending_changes);
    }

    /**
     * Manually linked articles first, then others from the same category.
     *
     * @return Collection<int, Post>
     */
    public function relatedPosts(int $limit = 3): Collection
    {
        $ids = array_map('intval', $this->related_post_ids ?? []);

        $related = $ids === []
            ? collect()
            : self::query()->published()->whereKey($ids)->whereKeyNot($this->id)->with('media')->get();

        if ($related->count() < $limit && $this->post_category_id) {
            $related = $related->concat(
                self::query()->published()
                    ->where('post_category_id', $this->post_category_id)
                    ->whereKeyNot([$this->id, ...$related->pluck('id')->all()])
                    ->with('media')
                    ->latest()
                    ->limit($limit - $related->count())
                    ->get()
            );
        }

        return $related->take($limit)->values();
    }
}
