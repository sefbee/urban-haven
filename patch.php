<?php
$content = file_get_contents('app/Support/AdminNavigation.php');

// Remove Articles group
$content = preg_replace("/'Articles' => \[\n.*?\n.*?\n\s+\],/s", "", $content);

// Add Articles to pagesGroup
$articlesLinks = <<<'EOD'
            if ($user->can('viewAny', Post::class)) {
                $pagesGroup[] = self::item('admin.posts.*', 'Articles', route('admin.posts.index'), 'list');
                $pagesGroup[] = self::item('admin.post-categories.*', 'Article categories', route('admin.post-categories.index'), 'tag');
            }
EOD;

$content = str_replace("\$pagesGroup[] = self::item('admin.faqs.*', 'FAQ page', route('admin.faqs.index'), 'info');\n        }", "\$pagesGroup[] = self::item('admin.faqs.*', 'FAQ page', route('admin.faqs.index'), 'info');\n        }\n" . $articlesLinks, $content);

file_put_contents('app/Support/AdminNavigation.php', $content);
