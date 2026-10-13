<?php

use App\Support\TaggedCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, array<string, list<string>>> */
    private const SEEDED_COPY = [
        'contact_hero' => [
            'eyebrow' => ['Contact Us'],
            'title' => ['Talk to our sales team', 'Talk to our property advisory team'],
            'intro' => [
                'Call, WhatsApp or send a message. The people who manage our homes will get back to you.',
                'Call, WhatsApp or send a message. The specialists who manage our developments will get back to you promptly.',
            ],
            'heading' => ['General Contact', 'Corporate Office'],
        ],
        'contact_office' => ['title' => ['Corporate Office']],
        'contact_direct' => [
            'title' => ['Direct Contact'],
            'intro' => ['Reach out to our agents directly for immediate assistance with any property inquiries.'],
        ],
        'contact_cta' => [
            'title' => ['Ready to Make Your Next Property Move?'],
            'intro' => [
                'Schedule a confidential consultation with our real-estate team and let us guide you home.',
                'Schedule a confidential consultation with our real estate team and let us guide you home.',
            ],
            'primary_cta' => ['Book a Consultation'],
            'primary_url' => ['#page-form-heading'],
            'secondary_cta' => ['Browse Properties'],
            'secondary_url' => ['/properties'],
        ],
    ];

    public function up(): void
    {
        foreach (DB::table('cms_blocks')->whereIn('key', array_keys(self::SEEDED_COPY))->get(['id', 'key', 'content', 'draft_content']) as $block) {
            $updates = [];

            foreach (['content', 'draft_content'] as $column) {
                $content = json_decode($block->{$column} ?? '', true);
                if (! is_array($content)) {
                    continue;
                }

                $changed = false;
                foreach (self::SEEDED_COPY[$block->key] as $field => $defaults) {
                    if (is_string($content[$field] ?? null) && in_array($content[$field], $defaults, true)) {
                        $content[$field] = '';
                        $changed = true;
                    }
                }

                if ($changed) {
                    $updates[$column] = json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                }
            }

            if ($updates !== []) {
                $updates['updated_at'] = now();
                DB::table('cms_blocks')->where('id', $block->id)->update($updates);
            }
        }

        TaggedCache::flush(['cms', 'contactpage']);
    }

    public function down(): void
    {
        // Cleared copy stays empty to avoid overwriting an intentional blank or later edit.
    }
};
