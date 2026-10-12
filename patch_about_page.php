<?php
$content = file_get_contents('resources/views/public/about.blade.php');
$content = str_replace("\$aboutContent = \App\Support\AboutPageContent::resolve(\$page->layout_content);", "", $content);
$content = preg_replace('/\{\{\s*\$aboutContent\[\'(.*?)\'\]\s*\}\}/', '{{ \App\Support\AboutPageSections::visible()[\'${1}_block\'] ?? \'\' }}', $content); // Basic regex, we might need a better one
file_put_contents('resources/views/public/about.blade.php', $content);
