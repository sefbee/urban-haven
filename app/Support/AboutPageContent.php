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
            'hero_title' => 'Find a place for the life you want to live.',
            'hero_intro' => 'Urban Haven brings property discovery and real human guidance together, so your next move can feel clear from the very first search.',
            'hero_quote' => 'Good decisions start with honest details and people who listen.',
            'hero_quote_kicker' => 'Our point of view',
            'hero_quote_label' => 'The Urban Haven approach',
            'hero_badge' => 'Made for your next chapter',
            'hero_primary_cta' => 'Explore properties',
            'hero_primary_url' => '/properties',
            'hero_secondary_cta' => 'Talk to our team',
            'hero_secondary_url' => '/contact',
            'story_eyebrow' => 'Why we are here',
            'story_title' => 'A more thoughtful journey to your next home.',
            'values_eyebrow' => 'What matters to us',
            'values_title' => 'The right home starts with the right experience.',
            'values_intro' => 'Finding a home can feel like a big decision. We make the search easier to navigate with useful information, considered choices and a team ready to help.',
            'value_one_title' => 'Clarity at every step',
            'value_one_text' => 'Straightforward property information helps you compare options and focus on what fits your life.',
            'value_two_title' => 'People who listen',
            'value_two_text' => 'Our team takes time to understand your priorities and answer the questions that matter to you.',
            'value_three_title' => 'A search that feels personal',
            'value_three_text' => 'From the first shortlist to arranging a visit, we help make your next move feel more manageable.',
            'cta_eyebrow' => 'Your next chapter',
            'cta_title' => 'Let’s find a place that feels like yours.',
            'cta_body' => 'Browse homes at your own pace, or tell us what you are looking for and we’ll help you take the next step.',
            'cta_label' => 'Get in touch',
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
