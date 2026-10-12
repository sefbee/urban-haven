<?php
$content = file_get_contents('app/Support/PageSections.php');
$content = str_replace("class HomeSections", "class PageSections", $content);
$content = str_replace("namespace App\Support;", "namespace App\Support;\n\nuse App\Models\CmsBlock;\nuse Illuminate\Database\Eloquent\Collection;", $content);
// We will replace HomeSections definitions with actual generic page definitions
file_put_contents('app/Support/PageSections.php', $content);
