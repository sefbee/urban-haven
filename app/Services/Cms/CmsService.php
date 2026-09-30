<?php

namespace App\Services\Cms;

use App\Contracts\AuditLogger;
use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Models\PublicationState;
use App\Models\User;
use App\Support\TaggedCache;
use Illuminate\Support\Facades\DB;
use Stevebauman\Purify\Facades\Purify;

class CmsService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createPage(array $validated, User $actor): CmsPage
    {
        return DB::transaction(function () use ($validated, $actor) {
            if (isset($validated['body'])) {
                $validated['body'] = Purify::clean($validated['body']);
            }

            $page = CmsPage::query()->create([
                ...$validated,
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
        return DB::transaction(function () use ($page, $validated, $actor) {
            if (isset($validated['body'])) {
                $validated['body'] = Purify::clean($validated['body']);
            }

            $page->fill($validated);
            $page->updated_by = $actor->id;
            $page->save();
            $this->auditLogger->record($actor->id, 'cms.updated', CmsPage::class, $page->id, null, ['slug' => $page->slug], request()->ip());
            TaggedCache::flush(['homepage', 'sitemap']);

            return $page;
        });
    }

    public function deletePage(CmsPage $page, User $actor): void
    {
        $this->auditLogger->record($actor->id, 'cms.deleted', CmsPage::class, $page->id, ['slug' => $page->slug], null, request()->ip());
        $page->delete();
        TaggedCache::flush(['homepage', 'sitemap']);
    }

    public function publishPage(CmsPage $page, User $actor): void
    {
        $state = $page->publicationState()->firstOrCreate([], ['status' => PublicationState::DRAFT]);
        $state->update([
            'status' => PublicationState::PUBLISHED,
            'published_by' => $actor->id,
            'published_at' => now(),
        ]);
        TaggedCache::flush(['homepage', 'sitemap']);
    }

    /**
     * @param  array<string, mixed>  $content
     */
    public function updateBlock(CmsBlock $block, array $content, User $actor): CmsBlock
    {
        $block->forceFill([
            'content' => $content,
            'updated_at' => now(),
        ])->save();

        $this->auditLogger->record($actor->id, 'cms.block_updated', CmsBlock::class, $block->id, null, ['key' => $block->key], request()->ip());
        TaggedCache::flush(['homepage']);

        return $block;
    }
}
