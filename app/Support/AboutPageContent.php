<?php

namespace App\Support;

final class AboutPageContent
{
    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'hero_eyebrow' => 'A home is more than an address',
            'hero_title' => "Building Trust.\nCreating Better Places to Live.",
            'hero_intro' => 'Urban Haven Properties Ltd. helps people discover thoughtfully selected homes and investment opportunities through transparency, expertise and personal service.',
            'hero_quote' => 'We believe the right property is more than an address — it is the foundation for a better future.',
            'hero_quote_kicker' => 'Our point of view',
            'hero_quote_label' => 'The Urban Haven approach',
            'hero_badge' => 'Made for your next chapter',
            'hero_primary_cta' => 'Discover Our Properties',
            'hero_primary_url' => '/properties',
            'hero_secondary_cta' => 'Talk to our team',
            'hero_secondary_url' => '/contact',

            'story_eyebrow' => 'Our Story',
            'story_title' => 'A New Standard in Property Experience',

            'stat_one_value' => '10+', 'stat_one_label' => 'Years of Combined Experience',
            'stat_two_value' => '25+', 'stat_two_label' => 'Property Opportunities',
            'stat_three_value' => '500+', 'stat_three_label' => 'Client Consultations',
            'stat_four_value' => '100%', 'stat_four_label' => 'Commitment to Transparency',

            'purpose_eyebrow' => 'Our Purpose',
            'purpose_title' => 'Driven by Purpose, Guided by Trust',
            'mission_title' => 'Mission',
            'mission_text' => 'To simplify the property journey through expert guidance, transparent communication and carefully selected real-estate opportunities.',
            'vision_title' => 'Vision',
            'vision_text' => 'To become a trusted real-estate name in Bangladesh, known for quality properties, lasting relationships and responsible growth.',

            'values_eyebrow' => 'What Guides Us',
            'values_title' => 'Values Behind Every Decision',
            'values_intro' => '',
            'value_one_title' => 'Integrity',
            'value_one_text' => 'We communicate clearly and act responsibly.',
            'value_two_title' => 'Client First',
            'value_two_text' => 'Every recommendation begins with your needs.',
            'value_three_title' => 'Quality',
            'value_three_text' => 'We focus on properties with lasting value.',
            'value_four_title' => 'Long-Term Partnership',
            'value_four_text' => 'Our relationship continues beyond the transaction.',

            'choose_eyebrow' => 'Why Choose Us',
            'choose_title' => 'Property Guidance You Can Rely On',
            'choose_list' => "Carefully selected property portfolio\nClear and transparent communication\nDedicated property consultants\nSupport from enquiry to handover\nLocal market knowledge",
            'choose_cta_label' => 'Talk to Our Team',
            'choose_cta_url' => '/contact',

            'approach_eyebrow' => 'Our Approach',
            'approach_title' => 'A Simpler Way to Find Your Property',
            'approach_one_title' => 'Understand', 'approach_one_text' => 'We listen to your goals, preferences and budget.',
            'approach_two_title' => 'Recommend', 'approach_two_text' => 'We shortlist relevant property opportunities.',
            'approach_three_title' => 'Experience', 'approach_three_text' => 'We arrange visits and provide complete information.',
            'approach_four_title' => 'Support', 'approach_four_text' => 'We guide you through the decision and handover.',

            'team_eyebrow' => 'Our People',
            'team_title' => 'Meet the Team Behind Urban Haven',

            'cta_eyebrow' => 'Your next chapter',
            'cta_title' => 'Let’s Build Your Property Journey Together.',
            'cta_body' => 'Speak with our team and discover an opportunity that matches your goals.',
            'cta_label' => 'Book a Consultation',
            'cta_url' => '/contact',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $content
     * @return array<string, string>
     */
    public static function resolve(?array $content): array
    {
        $resolved = self::defaults();

        foreach ($resolved as $field => $default) {
            $value = $content[$field] ?? null;
            if (is_string($value) && $value !== '') {
                $resolved[$field] = $value;
            }
        }

        return $resolved;
    }
}
