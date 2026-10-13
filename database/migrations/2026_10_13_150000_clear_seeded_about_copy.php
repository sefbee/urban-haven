<?php

use App\Support\TaggedCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, array<string, list<string>>> */
    private const SEEDED_COPY = [
        'about_hero' => [
            'eyebrow' => ['A home is more than an address'],
            'title' => ["Building Trust.\nCreating Better Places to Live."],
            'intro' => [
                'Urban Haven Properties Ltd. helps people discover thoughtfully selected homes and investment opportunities through transparency, expertise and personal service.',
                'Urban Haven Properties Ltd. helps people discover thoughtfully selected homes and investment opportunities through transparency, expertise, and personal service.',
            ],
            'quote' => ['We believe the right property is more than an address — it is the foundation for a better future.'],
            'quote_kicker' => ['Our point of view'],
            'quote_label' => ['The Urban Haven approach'],
            'badge' => ['Made for your next chapter'],
            'primary_cta' => ['Discover Our Properties'],
            'primary_url' => ['/properties'],
            'secondary_cta' => ['Talk to our team'],
            'secondary_url' => ['/contact'],
        ],
        'about_story' => [
            'eyebrow' => ['Our Story'],
            'title' => ['A New Standard in Property Experience'],
            'cta_label' => ['Discover Our Properties'],
            'cta_url' => ['/properties'],
            'stat_one_value' => ['10+', '12+'],
            'stat_one_label' => ['Years of Combined Experience'],
            'stat_two_value' => ['25+', '35+'],
            'stat_two_label' => ['Property Opportunities'],
            'stat_three_value' => ['500+', '850+'],
            'stat_three_label' => ['Client Consultations'],
            'stat_four_value' => ['100%'],
            'stat_four_label' => ['Commitment to Transparency'],
        ],
        'about_purpose' => [
            'eyebrow' => ['Our Purpose'],
            'title' => ['Driven by Purpose, Guided by Trust'],
            'mission_title' => ['Mission'],
            'mission_text' => ['To simplify the property journey through expert guidance, transparent communication and carefully selected real-estate opportunities.'],
            'vision_title' => ['Vision'],
            'vision_text' => ['To become a trusted real-estate name in Bangladesh, known for quality properties, lasting relationships and responsible growth.'],
        ],
        'about_values' => [
            'eyebrow' => ['What Guides Us'],
            'title' => ['Values Behind Every Decision'],
            'value_one_title' => ['Integrity'],
            'value_one_text' => ['We communicate clearly and act responsibly.'],
            'value_two_title' => ['Client First'],
            'value_two_text' => ['Every recommendation begins with your needs.'],
            'value_three_title' => ['Quality'],
            'value_three_text' => ['We focus on properties with lasting value.'],
            'value_four_title' => ['Long-Term Partnership'],
            'value_four_text' => ['Our relationship continues beyond the transaction.'],
        ],
        'about_choose' => [
            'eyebrow' => ['Why Choose Us'],
            'title' => ['Property Guidance You Can Rely On'],
            'list' => ["Carefully selected property portfolio\nClear and transparent communication\nDedicated property consultants\nSupport from enquiry to handover\nLocal market knowledge"],
            'cta_label' => ['Talk to Our Team'],
            'cta_url' => ['/contact'],
        ],
        'about_approach' => [
            'eyebrow' => ['Our Approach'],
            'title' => ['A Simpler Way to Find Your Property'],
            'approach_one_title' => ['Understand'],
            'approach_one_text' => ['We listen to your goals, preferences and budget.'],
            'approach_two_title' => ['Recommend'],
            'approach_two_text' => ['We shortlist relevant property opportunities.'],
            'approach_three_title' => ['Experience'],
            'approach_three_text' => ['We arrange visits and provide complete information.'],
            'approach_four_title' => ['Support'],
            'approach_four_text' => ['We guide you through the decision and handover.'],
        ],
        'about_cta' => [
            'eyebrow' => ['Your next chapter'],
            'title' => ['Let’s Build Your Property Journey Together.'],
            'body' => ['Speak with our team and discover an opportunity that matches your goals.'],
            'label' => ['Book a Consultation'],
            'url' => ['/contact'],
            'secondary_label' => ['Contact Us'],
            'secondary_url' => ['/contact'],
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

        $seededBody = '<p>Urban Haven Properties Ltd. develops, manages, and advises on premier residential and commercial real estate across Dhaka. Built on integrity, transparent legal due diligence, and considered architectural design, we simplify your journey home.</p>';
        DB::table('cms_pages')->where('slug', 'about')->where('body', $seededBody)->update(['body' => '', 'updated_at' => now()]);

        TaggedCache::flush(['cms', 'aboutpage']);
    }

    public function down(): void
    {
        // Keep cleared text empty so rollback cannot overwrite later content.
    }
};
