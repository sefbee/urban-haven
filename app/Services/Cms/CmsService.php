<?php

namespace App\Services\Cms;

use App\Contracts\AuditLogger;
use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Models\Post;
use App\Models\PublicationState;
use App\Models\User;
use App\Services\Seo\RedirectResolver;
use App\Support\TaggedCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Stevebauman\Purify\Facades\Purify;

/**
 * Editors can revise content at any time, but anything already live is held as a
 * pending revision until someone with publish rights releases it.
 */
class CmsService
{
    private const PAGE_FIELDS = ['title', 'template', 'body'];

    private const POST_FIELDS = ['title', 'post_category_id', 'excerpt', 'body', 'author_label', 'related_post_ids'];

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly RedirectResolver $redirects,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createPage(array $validated, User $actor): CmsPage
    {
        return DB::transaction(function () use ($validated, $actor) {
            $page = CmsPage::query()->create([
                ...$this->clean($validated),
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->auditLogger->record($actor->id, 'cms.created', CmsPage::class, $page->id, null, ['slug' => $page->slug], request()->ip());

            return $page;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updatePage(CmsPage $page, array $validated, User $actor): CmsPage
    {
        return $this->updateContent($page, $validated, $actor, self::PAGE_FIELDS, '');
    }

    public function deletePage(CmsPage $page, User $actor): void
    {
        $this->auditLogger->record($actor->id, 'cms.deleted', CmsPage::class, $page->id, ['slug' => $page->slug], null, request()->ip());
        $page->delete();
        $this->flush();
    }

    public function publishPage(CmsPage $page, User $actor): void
    {
        $this->publish($page, $actor, '');
    }

    public function unpublishPage(CmsPage $page, User $actor): void
    {
        $this->unpublish($page, $actor);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createPost(array $validated, User $actor): Post
    {
        return DB::transaction(function () use ($validated, $actor) {
            $post = Post::query()->create([
                ...$this->clean($validated),
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->auditLogger->record($actor->id, 'post.created', Post::class, $post->id, null, ['slug' => $post->slug], request()->ip());

            return $post;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updatePost(Post $post, array $validated, User $actor): Post
    {
        return $this->updateContent($post, $validated, $actor, self::POST_FIELDS, '/articles/');
    }

    public function publishPost(Post $post, User $actor): void
    {
        $this->publish($post, $actor, '/articles/');
    }

    public function unpublishPost(Post $post, User $actor): void
    {
        $this->unpublish($post, $actor);
    }

    public function deletePost(Post $post, User $actor): void
    {
        $this->auditLogger->record($actor->id, 'post.deleted', Post::class, $post->id, ['slug' => $post->slug], null, request()->ip());
        $post->delete();
        $this->flush();
    }

    /**
     * @param  array<string, mixed>  $content
     */
    public function updateBlock(CmsBlock $block, array $content, User $actor, ?bool $isVisible = null, ?int $sortOrder = null): CmsBlock
    {
        $content = $this->cleanBlock($content);
        $direct = $actor->hasPermission('cms.publish');

        $block->forceFill([
            ...($direct ? ['content' => $content, 'draft_content' => null] : ['draft_content' => $content]),
            ...($direct && $isVisible !== null ? ['is_visible' => $isVisible] : []),
            ...($direct && $sortOrder !== null ? ['sort_order' => $sortOrder] : []),
            'updated_at' => now(),
        ])->save();

        $this->auditLogger->record($actor->id, $direct ? 'cms.block_published' : 'cms.block_drafted', CmsBlock::class, $block->id, null, ['key' => $block->key], request()->ip());

        if ($direct) {
            $this->flush();
        }

        return $block;
    }

    public function publishBlock(CmsBlock $block, User $actor): CmsBlock
    {
        if (! $block->hasDraft()) {
            throw ValidationException::withMessages(['block' => 'There are no pending changes to publish.']);
        }

        $block->forceFill([
            'content' => $block->draft_content,
            'draft_content' => null,
            'updated_at' => now(),
        ])->save();

        $this->auditLogger->record($actor->id, 'cms.block_published', CmsBlock::class, $block->id, null, ['key' => $block->key], request()->ip());
        $this->flush();

        return $block;
    }

    /**
     * The record as it will look once pending changes are published, for preview only.
     */
    public function previewOf(CmsPage|Post $model): CmsPage|Post
    {
        $copy = clone $model;

        foreach ((array) $model->pending_changes as $field => $value) {
            $copy->setAttribute($field, $value);
        }

        return $copy;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  list<string>  $fields
     */
    private function updateContent(CmsPage|Post $model, array $validated, User $actor, array $fields, string $pathPrefix): CmsPage|Post
    {
        $validated = $this->clean($validated);
        $changes = array_intersect_key($validated, array_flip($fields));
        $holdForReview = $model->isPublished() && ! $actor->hasPermission('cms.publish');

        return DB::transaction(function () use ($model, $validated, $changes, $actor, $holdForReview, $pathPrefix) {
            if ($holdForReview) {
                $model->pending_changes = [...((array) $model->pending_changes), ...$changes];
            } else {
                $oldSlug = $model->slug;
                $model->fill($validated);
                $model->pending_changes = null;

                if ($model->isDirty('slug') && $model->isPublished() && $oldSlug) {
                    $this->redirects->store($this->path($pathPrefix, $oldSlug), $this->path($pathPrefix, $model->slug), 301, 'Slug changed', $actor);
                }
            }

            $model->updated_by = $actor->id;
            $model->save();

            $this->auditLogger->record($actor->id, ($model instanceof Post ? 'post' : 'cms').($holdForReview ? '.revision_pending' : '.updated'), $model::class, $model->id, null, ['slug' => $model->slug, 'fields' => array_keys($changes)], request()->ip());

            if (! $holdForReview) {
                $this->flush();
            }

            return $model;
        });
    }

    private function publish(CmsPage|Post $model, User $actor, string $pathPrefix): void
    {
        DB::transaction(function () use ($model, $actor, $pathPrefix): void {
            if ($model->hasPendingChanges()) {
                $oldSlug = $model->slug;
                $model->fill($model->pending_changes);
                $model->pending_changes = null;
                $model->save();

                if ($oldSlug !== $model->slug && $model->isPublished()) {
                    $this->redirects->store($this->path($pathPrefix, $oldSlug), $this->path($pathPrefix, $model->slug), 301, 'Slug changed', $actor);
                }
            }

            $state = $model->publicationState()->firstOrCreate([], ['status' => PublicationState::DRAFT]);
            $old = $state->status;
            $state->update([
                'status' => PublicationState::PUBLISHED,
                'published_by' => $actor->id,
                'published_at' => $state->published_at ?? now(),
                'unpublished_at' => null,
            ]);

            $this->auditLogger->record($actor->id, ($model instanceof Post ? 'post' : 'cms').'.published', $model::class, $model->id, ['status' => $old], ['status' => PublicationState::PUBLISHED], request()->ip());
        });

        $this->flush();
    }

    private function unpublish(CmsPage|Post $model, User $actor): void
    {
        $state = $model->publicationState()->firstOrCreate([], ['status' => PublicationState::DRAFT]);
        $state->update(['status' => PublicationState::UNPUBLISHED, 'unpublished_at' => now()]);
        $this->auditLogger->record($actor->id, ($model instanceof Post ? 'post' : 'cms').'.unpublished', $model::class, $model->id, null, null, request()->ip());
        $this->flush();
    }

    private function path(string $prefix, string $slug): string
    {
        return $prefix === '' ? '/'.$slug : $prefix.$slug;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function clean(array $validated): array
    {
        if (isset($validated['body'])) {
            $validated['body'] = Purify::clean($validated['body']);
        }

        foreach (['excerpt', 'title', 'meta_title', 'meta_description', 'author_label'] as $plain) {
            if (isset($validated[$plain]) && is_string($validated[$plain])) {
                $validated[$plain] = trim(strip_tags($validated[$plain]));
            }
        }

        return $validated;
    }

    /**
     * Block fields are plain text or internal links; HTML is stripped and links must stay on-site.
     *
     * @param  array<string, mixed>  $content
     * @return array<string, mixed>
     */
    private function cleanBlock(array $content): array
    {
        array_walk_recursive($content, function (mixed &$value, string|int $key): void {
            if (! is_string($value)) {
                return;
            }

            $value = trim(strip_tags($value));

            if ((is_string($key) && (str_ends_with($key, 'url') || str_ends_with($key, 'href'))) && $value !== '' && ! str_starts_with($value, '/')) {
                throw ValidationException::withMessages(['content' => 'Block links must be internal paths starting with “/”.']);
            }
        });

        return $content;
    }

    private function flush(): void
    {
        TaggedCache::flush(['homepage', 'sitemap', 'cms', 'menus']);
    }
}
