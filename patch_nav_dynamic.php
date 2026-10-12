<?php
$content = file_get_contents('app/Support/AdminNavigation.php');

$oldDynamicPagesLoop = <<<'EOD'
        if ($canCms) {
            try {
                $pages = CmsPage::query()->orderBy('title')->get();
                foreach ($pages as $page) {
                    $url = route('admin.cms.edit', $page);
                    $icon = match ($page->template) {
                        'about' => 'user-group',
                        'contact' => 'phone',
                        default => 'document-text',
                    };
                    $dynamicPages[$page->template === 'about' ? 'about' : ($page->template === 'contact' ? 'contact' : $page->id)] = self::item(
                        'admin.cms.edit.'.$page->id,
                        $page->title,
                        $url,
                        $icon,
                        fn () => request()->url() === $url
                    );
                }
            } catch (\Throwable $e) {
                // Ignore DB issues during setup/migration
            }
        }
EOD;

$newDynamicPagesLoop = <<<'EOD'
        if ($canCms) {
            try {
                $pages = CmsPage::query()->orderBy('title')->get();
                foreach ($pages as $page) {
                    if ($page->template === 'about' || $page->template === 'contact') {
                        $url = route('admin.page-sections.index', ['page' => $page->template]);
                        $icon = $page->template === 'about' ? 'user-group' : 'phone';
                        $dynamicPages[$page->template] = self::item(
                            'admin.page-sections.*.'.$page->template,
                            $page->title,
                            $url,
                            $icon,
                            fn () => request()->route('page') === $page->template
                        );
                    } else {
                        $url = route('admin.cms.edit', $page);
                        $icon = 'document-text';
                        $dynamicPages[$page->id] = self::item(
                            'admin.cms.edit.'.$page->id,
                            $page->title,
                            $url,
                            $icon,
                            fn () => request()->url() === $url
                        );
                    }
                }
            } catch (\Throwable $e) {
                // Ignore DB issues during setup/migration
            }
        }
EOD;

$content = str_replace($oldDynamicPagesLoop, $newDynamicPagesLoop, $content);

$content = str_replace(
    "\$pagesGroup[] = self::item('admin.posts.*', 'Articles', route('admin.posts.index'), 'list');",
    "\$pagesGroup[] = self::item('admin.page-sections.*.articles', 'Articles page', route('admin.page-sections.index', ['page' => 'articles']), 'document-text', fn() => request()->route('page') === 'articles');\n                \$pagesGroup[] = self::item('admin.posts.*', 'Articles content', route('admin.posts.index'), 'list');",
    $content
);

file_put_contents('app/Support/AdminNavigation.php', $content);
