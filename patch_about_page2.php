<?php
$content = file_get_contents('resources/views/public/about.blade.php');
$content = preg_replace('/\{\{ \\\\App\\\\Support\\\\AboutPageSections::visible\(\)\[\'(.*?)_block\'\] \?\? \'\' \}\}/', '{{ $aboutContent[\'${1}\'] ?? \'\' }}', $content); // Revert first regex
$content = str_replace("<?php\n    \$aboutContent = \App\Support\AboutPageContent::resolve(\$page->layout_content);", "<?php\n    \$aboutSections = \App\Support\AboutPageSections::visible();", $content);
$content = str_replace("@php\n    \$aboutImages = \$page->galleryImages();", "@php\n    \$aboutSections = \App\Support\AboutPageSections::visible();\n    \$aboutImages = \$page->galleryImages();", $content);

// Apply real replacements
$content = str_replace("\$aboutContent['hero_eyebrow']", "\$aboutSections['hero']['eyebrow']", $content);
$content = str_replace("\$aboutContent['hero_title']", "\$aboutSections['hero']['title']", $content);
$content = str_replace("\$aboutContent['hero_intro']", "\$aboutSections['hero']['intro']", $content);
$content = str_replace("\$aboutContent['hero_quote']", "\$aboutSections['hero']['quote']", $content);
$content = str_replace("\$aboutContent['hero_quote_kicker']", "\$aboutSections['hero']['quote_kicker']", $content);
$content = str_replace("\$aboutContent['hero_quote_label']", "\$aboutSections['hero']['quote_label']", $content);
$content = str_replace("\$aboutContent['hero_badge']", "\$aboutSections['hero']['badge']", $content);
$content = str_replace("\$aboutContent['hero_primary_cta']", "\$aboutSections['hero']['primary_cta']", $content);
$content = str_replace("\$aboutContent['hero_primary_url']", "\$aboutSections['hero']['primary_url']", $content);
$content = str_replace("\$aboutContent['hero_secondary_cta']", "\$aboutSections['hero']['secondary_cta']", $content);
$content = str_replace("\$aboutContent['hero_secondary_url']", "\$aboutSections['hero']['secondary_url']", $content);

$content = str_replace("\$aboutContent['story_eyebrow']", "\$aboutSections['story']['eyebrow']", $content);
$content = str_replace("\$aboutContent['story_title']", "\$aboutSections['story']['title']", $content);
$content = str_replace("\$aboutContent['stat_one_value']", "\$aboutSections['story']['stat_one_value']", $content);
$content = str_replace("\$aboutContent['stat_one_label']", "\$aboutSections['story']['stat_one_label']", $content);
$content = str_replace("\$aboutContent['stat_two_value']", "\$aboutSections['story']['stat_two_value']", $content);
$content = str_replace("\$aboutContent['stat_two_label']", "\$aboutSections['story']['stat_two_label']", $content);
$content = str_replace("\$aboutContent['stat_three_value']", "\$aboutSections['story']['stat_three_value']", $content);
$content = str_replace("\$aboutContent['stat_three_label']", "\$aboutSections['story']['stat_three_label']", $content);
$content = str_replace("\$aboutContent['stat_four_value']", "\$aboutSections['story']['stat_four_value']", $content);
$content = str_replace("\$aboutContent['stat_four_label']", "\$aboutSections['story']['stat_four_label']", $content);

$content = str_replace("\$aboutContent['purpose_eyebrow']", "\$aboutSections['purpose']['eyebrow']", $content);
$content = str_replace("\$aboutContent['purpose_title']", "\$aboutSections['purpose']['title']", $content);
$content = str_replace("\$aboutContent['mission_title']", "\$aboutSections['purpose']['mission_title']", $content);
$content = str_replace("\$aboutContent['mission_text']", "\$aboutSections['purpose']['mission_text']", $content);
$content = str_replace("\$aboutContent['vision_title']", "\$aboutSections['purpose']['vision_title']", $content);
$content = str_replace("\$aboutContent['vision_text']", "\$aboutSections['purpose']['vision_text']", $content);

$content = str_replace("\$aboutContent['values_eyebrow']", "\$aboutSections['values']['eyebrow']", $content);
$content = str_replace("\$aboutContent['values_title']", "\$aboutSections['values']['title']", $content);
$content = str_replace("\$aboutContent['values_intro']", "\$aboutSections['values']['intro']", $content);
$content = str_replace("\$aboutContent['value_one_title']", "\$aboutSections['values']['value_one_title']", $content);
$content = str_replace("\$aboutContent['value_one_text']", "\$aboutSections['values']['value_one_text']", $content);
$content = str_replace("\$aboutContent['value_two_title']", "\$aboutSections['values']['value_two_title']", $content);
$content = str_replace("\$aboutContent['value_two_text']", "\$aboutSections['values']['value_two_text']", $content);
$content = str_replace("\$aboutContent['value_three_title']", "\$aboutSections['values']['value_three_title']", $content);
$content = str_replace("\$aboutContent['value_three_text']", "\$aboutSections['values']['value_three_text']", $content);
$content = str_replace("\$aboutContent['value_four_title']", "\$aboutSections['values']['value_four_title']", $content);
$content = str_replace("\$aboutContent['value_four_text']", "\$aboutSections['values']['value_four_text']", $content);

$content = str_replace("\$aboutContent['choose_eyebrow']", "\$aboutSections['choose']['eyebrow']", $content);
$content = str_replace("\$aboutContent['choose_title']", "\$aboutSections['choose']['title']", $content);
$content = str_replace("\$aboutContent['choose_list']", "\$aboutSections['choose']['list']", $content);
$content = str_replace("\$aboutContent['choose_cta_label']", "\$aboutSections['choose']['cta_label']", $content);
$content = str_replace("\$aboutContent['choose_cta_url']", "\$aboutSections['choose']['cta_url']", $content);

$content = str_replace("\$aboutContent['approach_eyebrow']", "\$aboutSections['approach']['eyebrow']", $content);
$content = str_replace("\$aboutContent['approach_title']", "\$aboutSections['approach']['title']", $content);
$content = str_replace("\$aboutContent['approach_one_title']", "\$aboutSections['approach']['approach_one_title']", $content);
$content = str_replace("\$aboutContent['approach_one_text']", "\$aboutSections['approach']['approach_one_text']", $content);
$content = str_replace("\$aboutContent['approach_two_title']", "\$aboutSections['approach']['approach_two_title']", $content);
$content = str_replace("\$aboutContent['approach_two_text']", "\$aboutSections['approach']['approach_two_text']", $content);
$content = str_replace("\$aboutContent['approach_three_title']", "\$aboutSections['approach']['approach_three_title']", $content);
$content = str_replace("\$aboutContent['approach_three_text']", "\$aboutSections['approach']['approach_three_text']", $content);
$content = str_replace("\$aboutContent['approach_four_title']", "\$aboutSections['approach']['approach_four_title']", $content);
$content = str_replace("\$aboutContent['approach_four_text']", "\$aboutSections['approach']['approach_four_text']", $content);

$content = str_replace("\$aboutContent['team_eyebrow']", "\$aboutSections['team']['eyebrow']", $content);
$content = str_replace("\$aboutContent['team_title']", "\$aboutSections['team']['title']", $content);

$content = str_replace("\$aboutContent['cta_eyebrow']", "\$aboutSections['cta']['eyebrow']", $content);
$content = str_replace("\$aboutContent['cta_title']", "\$aboutSections['cta']['title']", $content);
$content = str_replace("\$aboutContent['cta_body']", "\$aboutSections['cta']['body']", $content);
$content = str_replace("\$aboutContent['cta_label']", "\$aboutSections['cta']['label']", $content);
$content = str_replace("\$aboutContent['cta_url']", "\$aboutSections['cta']['url']", $content);

file_put_contents('resources/views/public/about.blade.php', $content);
