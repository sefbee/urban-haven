<?php
$content = file_get_contents('resources/views/public/contact.blade.php');
$content = str_replace("    \$contactBlock = \$template === 'contact' ? (\App\Models\CmsBlock::contentFor('contact_details') ?? []) : [];\n    \$phone = \App\Models\Setting::get('phone') ?: (\$contactBlock['phone'] ?? null);\n    \$email = \App\Models\Setting::get('email') ?: (\$contactBlock['email'] ?? null);\n    \$address = \App\Models\Setting::get('address') ?: (\$contactBlock['address'] ?? null);", 
"    \$contactSections = \App\Support\ContactPageSections::visible();\n    \$contactBlock = \$template === 'contact' ? (\App\Models\CmsBlock::contentFor('contact_details') ?? []) : [];\n    \$phone = \App\Models\Setting::get('phone') ?: (\$contactBlock['phone'] ?? null);\n    \$email = \App\Models\Setting::get('email') ?: (\$contactBlock['email'] ?? null);\n    \$address = \App\Models\Setting::get('address') ?: (\$contactBlock['address'] ?? null);", $content);

// Contact page currently has hardcoded heading in view, we'll make them dynamic using contactSections

$content = str_replace("<h1 class=\"uh-h1 uh-page-title\">{{ \$template === 'contact' ? __('Talk to our sales team') : \$page->title }}</h1>",
"<h1 class=\"uh-h1 uh-page-title\">{{ \$template === 'contact' ? (\$contactSections['hero']['title'] ?? __('Talk to our sales team')) : \$page->title }}</h1>", $content);

$content = str_replace("<p class=\"uh-page-lede\">{{ __('Call, WhatsApp or send a message. The people who manage our homes will get back to you.') }}</p>",
"<p class=\"uh-page-lede\">{{ \$contactSections['hero']['intro'] ?? __('Call, WhatsApp or send a message. The people who manage our homes will get back to you.') }}</p>", $content);

$content = preg_replace('/<h2 class="text-\[22px\] lg:text-\[26px\] font-semibold text-\[var\(--color-ink\)\]">.*?<\/h2>/s', 
'<h2 class="text-[22px] lg:text-[26px] font-semibold text-[var(--color-ink)]">{{ $contactSections[\'hero\'][\'heading\'] ?? \'General Contact\' }}</h2>', $content, 1);

$content = preg_replace('/<h3 class="text-\[18px\] lg:text-\[22px\] font-semibold text-\[var\(--color-ink\)\] mb-4">.*?<\/h3>/s',
'<h3 class="text-[18px] lg:text-[22px] font-semibold text-[var(--color-ink)] mb-4">{{ $contactSections[\'office\'][\'title\'] ?? \'Corporate Office\' }}</h3>', $content, 1);

$content = str_replace("<h4 class=\"text-[15px] font-semibold text-white tracking-wide\">{{ __('Direct Contact') }}</h4>",
"<h4 class=\"text-[15px] font-semibold text-white tracking-wide\">{{ \$contactSections['direct']['title'] ?? __('Direct Contact') }}</h4>", $content);

$content = str_replace("<p class=\"text-[13px] text-white/70 leading-relaxed max-w-sm mb-6\">{{ __('Reach out to our agents directly for immediate assistance with any property inquiries.') }}</p>",
"<p class=\"text-[13px] text-white/70 leading-relaxed max-w-sm mb-6\">{{ \$contactSections['direct']['intro'] ?? __('Reach out to our agents directly for immediate assistance with any property inquiries.') }}</p>", $content);

file_put_contents('resources/views/public/contact.blade.php', $content);
