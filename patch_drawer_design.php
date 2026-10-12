<?php
$content = file_get_contents('resources/views/public/partials/filter-drawer.blade.php');

// Replace the drawer head icon
$headSearch = '<span class="uh-drawer-mark"><x-icon name="filter" class="size-5" /></span>';
$headReplace = '<span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm text-primary-500"><x-icon name="filter" class="size-5" /></span>';
$content = str_replace($headSearch, $headReplace, $content);

// Replace intro
$introSearch = '<p class="uh-drawer-intro">{{ __(\'Fine tune your search with the details that matter most.\') }}</p>';
$introReplace = '<p class="text-sm text-slate-500 mb-6">{{ __(\'Find your perfect property with the right filters\') }}</p>';
$content = str_replace($introSearch, $introReplace, $content);

// The user wants cards for groups.
// Instead of replacing everything, let's wrap each fieldset in a card.
// We can define a wrapper CSS class for cards.
// But we also need icons and descriptions.
