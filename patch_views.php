<?php
$files = ['resources/views/admin/page-sections/index.blade.php', 'resources/views/admin/page-sections/edit.blade.php'];
foreach ($files as $file) {
    $content = file_get_contents($file);
    $content = str_replace("admin.home-sections", "admin.page-sections", $content);
    $content = str_replace("route('admin.page-sections.edit', \$section)", "route('admin.page-sections.edit', ['page' => \$page, 'section' => \$section])", $content);
    $content = str_replace("route('admin.page-sections.visibility', \$section)", "route('admin.page-sections.visibility', ['page' => \$page, 'section' => \$section])", $content);
    $content = str_replace("route('admin.page-sections.move', \$section)", "route('admin.page-sections.move', ['page' => \$page, 'section' => \$section])", $content);
    $content = str_replace("route('admin.page-sections.update', \$section)", "route('admin.page-sections.update', ['page' => \$page, 'section' => \$section])", $content);
    $content = str_replace("route('admin.page-sections.reset', \$section)", "route('admin.page-sections.reset', ['page' => \$page, 'section' => \$section])", $content);
    $content = str_replace("route('admin.page-sections.discard', \$section)", "route('admin.page-sections.discard', ['page' => \$page, 'section' => \$section])", $content);
    $content = str_replace("route('admin.page-sections.publish', \$section)", "route('admin.page-sections.publish', ['page' => \$page, 'section' => \$section])", $content);
    $content = str_replace("route('admin.page-sections.index')", "route('admin.page-sections.index', ['page' => \$page])", $content);
    
    // Fix page headers dynamically
    if ($file === 'resources/views/admin/page-sections/index.blade.php') {
        $content = str_replace("<x-ui.page-header compact title=\"Home page\">", "<x-ui.page-header compact title=\"{{ ucfirst(\$page) }} page\">", $content);
        $content = str_replace("href=\"{{ route('home') }}\"", "href=\"{{ \$page === 'home' ? route('home') : url('/' . \$page) }}\"", $content);
        $content = str_replace("Preview home page", "Preview {{ \$page }} page", $content);
    }
    if ($file === 'resources/views/admin/page-sections/edit.blade.php') {
        $content = str_replace("<x-ui.page-header compact :title=\"\$definition['label']\">", "<x-ui.page-header compact :title=\"\$definition['label']\">\n        <x-slot:eyebrow>{{ ucfirst(\$page) }} page · Section {{ \$position }} of {{ \$total }}</x-slot:eyebrow>", $content);
        // remove old eyebrow
        $content = preg_replace("/<x-slot:eyebrow>Home page .*?<\/x-slot:eyebrow>/s", "", $content);
        $content = str_replace("On the homepage", "On the {{ \$page }} page", $content);
        $content = str_replace("updated on the homepage", "updated on the {{ \$page }} page", $content);
    }
    
    file_put_contents($file, $content);
}
