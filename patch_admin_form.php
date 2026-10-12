<?php

$file = 'resources/views/admin/cms/pages/_form.blade.php';
$content = file_get_contents($file);

$fields = <<<'FIELDS'
        'hero_eyebrow' => ['Small label', 'text', 80], 'hero_title' => ['Main headline', 'textarea', 200], 'hero_intro' => ['Intro line', 'textarea', 300],
        'hero_quote' => ['Quote', 'textarea', 240], 'hero_quote_kicker' => ['Quote eyebrow', 'text', 80], 'hero_quote_label' => ['Quote attribution', 'text', 80], 'hero_badge' => ['Corner label', 'text', 100],
        'hero_primary_cta' => ['Primary button text', 'text', 40], 'hero_primary_url' => ['Primary button link', 'text', 255],
        'hero_secondary_cta' => ['Secondary button text', 'text', 40], 'hero_secondary_url' => ['Secondary button link', 'text', 255],
        'story_eyebrow' => ['Story label', 'text', 80], 'story_title' => ['Story heading', 'text', 140],
        
        'stat_one_value' => ['Stat 1 Value', 'text', 40], 'stat_one_label' => ['Stat 1 Label', 'text', 100],
        'stat_two_value' => ['Stat 2 Value', 'text', 40], 'stat_two_label' => ['Stat 2 Label', 'text', 100],
        'stat_three_value' => ['Stat 3 Value', 'text', 40], 'stat_three_label' => ['Stat 3 Label', 'text', 100],
        'stat_four_value' => ['Stat 4 Value', 'text', 40], 'stat_four_label' => ['Stat 4 Label', 'text', 100],

        'purpose_eyebrow' => ['Purpose label', 'text', 80], 'purpose_title' => ['Purpose heading', 'text', 140],
        'mission_title' => ['Mission title', 'text', 80], 'mission_text' => ['Mission text', 'textarea', 300],
        'vision_title' => ['Vision title', 'text', 80], 'vision_text' => ['Vision text', 'textarea', 300],

        'values_eyebrow' => ['Values label', 'text', 80], 'values_title' => ['Values heading', 'text', 140], 'values_intro' => ['Values introduction', 'textarea', 300],
        'value_one_title' => ['First value heading', 'text', 80], 'value_one_text' => ['First value text', 'textarea', 240],
        'value_two_title' => ['Second value heading', 'text', 80], 'value_two_text' => ['Second value text', 'textarea', 240],
        'value_three_title' => ['Third value heading', 'text', 80], 'value_three_text' => ['Third value text', 'textarea', 240],
        'value_four_title' => ['Fourth value heading', 'text', 80], 'value_four_text' => ['Fourth value text', 'textarea', 240],

        'choose_eyebrow' => ['Why Choose Us label', 'text', 80], 'choose_title' => ['Why Choose Us heading', 'text', 140],
        'choose_list' => ['Why Choose Us list (one per line)', 'textarea', 500],
        'choose_cta_label' => ['Why Choose button text', 'text', 40], 'choose_cta_url' => ['Why Choose button link', 'text', 255],

        'approach_eyebrow' => ['Approach label', 'text', 80], 'approach_title' => ['Approach heading', 'text', 140],
        'approach_one_title' => ['Step 1 title', 'text', 80], 'approach_one_text' => ['Step 1 text', 'textarea', 240],
        'approach_two_title' => ['Step 2 title', 'text', 80], 'approach_two_text' => ['Step 2 text', 'textarea', 240],
        'approach_three_title' => ['Step 3 title', 'text', 80], 'approach_three_text' => ['Step 3 text', 'textarea', 240],
        'approach_four_title' => ['Step 4 title', 'text', 80], 'approach_four_text' => ['Step 4 text', 'textarea', 240],

        'team_eyebrow' => ['Team label', 'text', 80], 'team_title' => ['Team heading', 'text', 140],

        'cta_eyebrow' => ['Closing label', 'text', 80], 'cta_title' => ['Closing heading', 'text', 140],
        'cta_body' => ['Closing text', 'textarea', 300], 'cta_label' => ['Closing button text', 'text', 40], 'cta_url' => ['Closing button link', 'text', 255],
FIELDS;

$content = preg_replace('~\'hero_eyebrow\'.*?\'cta_url\' => \[\'Closing button link\', \'text\', 255\],~s', $fields, $content);
file_put_contents($file, $content);
