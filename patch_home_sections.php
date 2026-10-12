<?php
$files = [
    'resources/views/admin/dashboard.blade.php',
    'resources/views/admin/cms/pages/index.blade.php',
    'resources/views/admin/menus/index.blade.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $content = str_replace("route('admin.home-sections.index')", "route('admin.page-sections.index', ['page' => 'home'])", $content);
    file_put_contents($file, $content);
}
