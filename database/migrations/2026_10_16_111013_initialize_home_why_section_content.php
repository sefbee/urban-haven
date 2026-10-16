<?php

use App\Support\TaggedCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Initial copy previously rendered as a public fallback, now stored in the CMS block.
     *
     * @var array<string, mixed>
     */
    private const INITIAL_CONTENT = [
        'eyebrow' => 'Our Advantage',
        'title' => 'Why Urban Haven?',
        'body' => 'One place to find verified properties in Dhaka with the details you need to decide. The same team that publishes each listing answers your calls, verifies title documentation, and walks you through the door.',
        'link_label' => 'More about us',
        'link_url' => '/about',
        'items' => [
            ['icon' => 'home', 'title' => 'Verified Listings', 'text' => 'All properties are checked and documented, so you get accurate information you can trust.'],
            ['icon' => 'users', 'title' => 'Expert Team', 'text' => 'Professional guidance at every step.'],
            ['icon' => 'document', 'title' => 'Clear Information', 'text' => 'Accurate details you can trust.'],
            ['icon' => 'phone', 'title' => 'Personal Support', 'text' => "We're here from first call to final step."],
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $updated = false;

        foreach (DB::table('cms_blocks')->where('key', 'about')->get(['id', 'content']) as $block) {
            $updates = [];

            $content = json_decode($block->content ?? '[]', true);
            if (! is_array($content)) {
                continue;
            }

            $changed = false;
            foreach (self::INITIAL_CONTENT as $field => $value) {
                $current = $content[$field] ?? null;
                $isEmptyRepeater = $field === 'items' && (! is_array($current) || $current === []);
                $isEmptyScalar = $field !== 'items' && blank($current);

                if ($isEmptyRepeater || $isEmptyScalar) {
                    $content[$field] = $value;
                    $changed = true;
                }
            }

            if ($changed) {
                $updates['content'] = json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            }

            if ($updates !== []) {
                DB::table('cms_blocks')->where('id', $block->id)->update([...$updates, 'updated_at' => now()]);
                $updated = true;
            }
        }

        if ($updated) {
            TaggedCache::flush(['cms', 'homepage', 'aboutpage', 'contactpage', 'articlespage', 'menus', 'sitemap']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep the initialized copy in place; rollback must not overwrite subsequent CMS edits.
    }
};
