<?php
$content = file_get_contents('app/Support/AdminNavigation.php');

$content = str_replace("self::item('admin.home-sections.*', 'Home', route('admin.home-sections.index'), 'home')", "self::item('admin.page-sections.*', 'Home', route('admin.page-sections.index', ['page' => 'home']), 'home')", $content);

file_put_contents('app/Support/AdminNavigation.php', $content);
