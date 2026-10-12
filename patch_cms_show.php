<?php
$content = file_get_contents('resources/views/public/cms/show.blade.php');

$content = str_replace(
    "@php\n    \$template = \$page->slug === 'contact' ? 'contact' : (\$page->slug === 'about' ? 'about' : (\$page->template ?: 'default'));",
    "@php\n    \$template = \$page->slug === 'contact' ? 'contact' : (\$page->slug === 'about' ? 'about' : (\$page->template ?: 'default'));\n    \$contactSections = \App\Support\ContactPageSections::visible();",
    $content
);

$content = str_replace(
    "<h1 class=\"uh-h1 uh-page-title\">{{ \$template === 'contact' ? __('Talk to our sales team') : \$page->title }}</h1>",
    "<h1 class=\"uh-h1 uh-page-title\">{{ \$template === 'contact' ? (\$contactSections['hero']['title'] ?? __('Talk to our sales team')) : \$page->title }}</h1>",
    $content
);

$content = str_replace(
    "<p class=\"uh-page-lede\">{{ __('Call, WhatsApp or send a message. The people who manage our homes will get back to you.') }}</p>",
    "<p class=\"uh-page-lede\">{{ \$contactSections['hero']['intro'] ?? __('Call, WhatsApp or send a message. The people who manage our homes will get back to you.') }}</p>",
    $content
);

file_put_contents('resources/views/public/cms/show.blade.php', $content);
