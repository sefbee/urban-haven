<?php

use App\Support\TaggedCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Homepage text copied from the old built-in defaults and catalog seeder.
     * Custom text is left untouched.
     *
     * @var array<string, array<string, list<string>>>
     */
    private const SEEDED_COPY = [
        'hero' => [
            'eyebrow' => ['Urban Haven Properties Ltd.'],
            'title' => ['Considered places to live in Dhaka.'],
            'body' => [
                'Search houses, apartments, and land across Dhaka at prices you can verify with our desk.',
                'Search verified houses, apartments, and land across Dhaka with honest specifications and direct sales desk support.',
            ],
            'cta_label' => ['Explore properties'],
            'cta_url' => ['/properties'],
        ],
        'home_housing' => [
            'title' => ['Housing & Apartment Projects in Bangladesh'],
            'link_label' => ['View all'],
        ],
        'home_projects' => [
            'title' => ['Featured Developments'],
            'subtitle' => ['Explore considered developments from our team.', 'Explore considered developments from our in-house architectural team.'],
            'link_label' => ['View all projects'],
        ],
        'home_featured' => [
            'title' => ['Featured Properties'],
            'subtitle' => ['A closer look at the places our team is featuring right now.', 'A closer look at the premier residences our team is showcasing right now.'],
            'link_label' => ['See every property'],
        ],
        'home_trending' => [
            'title' => ['Trending Properties'],
            'link_label' => ['View All'],
        ],
        'home_locations' => [
            'title' => ['Explore Properties by Location'],
            'subtitle' => ['Prime residential enclaves and thriving commercial hubs across Dhaka.'],
            'link_label' => ['See them on the map'],
        ],
        'home_types' => [
            'title' => ['Explore Real Estate in Bangladesh'],
            'link_label' => ['View all'],
        ],
        'home_latest' => [
            'title' => ['Latest Properties'],
            'subtitle' => ['New to Urban Haven this week.', 'New residential and commercial arrivals added to Urban Haven this week.'],
            'link_label' => ['See everything new'],
        ],
        'home_requirements' => [
            'eyebrow' => ['Personal property search'],
            'title' => ['Can’t find what you’re looking for?'],
            'body' => [
                'Tell us your preferred area, budget and needs. Our team will help you find a property that fits.',
                'Tell us your preferred area, budget and requirements. Our advisory desk will assist you in finding a property that fits your life.',
            ],
            'cta_label' => ['Tell us what you need'],
            'cta_url' => ['/contact'],
        ],
        'about' => [
            'title' => ['Why Urban Haven?'],
            'body' => [
                'One place to find Urban Haven properties, with the details you need to decide. The same team that publishes each listing answers your questions and walks you through the door.',
                'One place to find verified properties in Dhaka with the details you need to decide. The same team that publishes each listing answers your calls, verifies title documentation, and walks you through the door.',
            ],
            'link_label' => ['More about us'],
            'link_url' => ['/about'],
        ],
        'home_articles' => [
            'title' => ['Latest Articles & Blog'],
            'subtitle' => [
                'Practical notes on buying, renting and investing in property from the Urban Haven team.',
                'Practical notes on buying, renting, and investing in property from the Urban Haven research team.',
            ],
            'link_label' => ['Read all articles'],
            'videos_title' => ['Property TV'],
        ],
        'home_faq' => [
            'title' => ['Frequently asked questions.'],
            'subtitle' => ['Answers to your questions, every step of the way.', 'Clear answers to your property questions, every step of the way.'],
            'cta_label' => ['Get in touch'],
            'link_label' => ['View all'],
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->transformHomepageContent();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep cleared copy empty when rolling back; restoring it could overwrite a deliberate blank.
    }

    private function transformHomepageContent(): void
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
                    $value = $content[$field] ?? null;

                    if (is_string($value) && in_array($value, $defaults, true)) {
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

        TaggedCache::flush(['cms', 'homepage']);
    }
};
