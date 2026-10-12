<?php
$content = file_get_contents('resources/views/public/articles/index.blade.php');

$content = str_replace(
    "@extends('layouts.public')\n\n@section('content')",
    "@extends('layouts.public')\n\n@php\n    \$articlesSections = \App\Support\ArticlesPageSections::visible();\n@endphp\n\n@section('content')",
    $content
);

$content = str_replace(
    "<p class=\"uh-editorial-overline\">{{ __('Urban Haven Journal') }}</p>",
    "<p class=\"uh-editorial-overline\">{{ \$articlesSections['masthead']['eyebrow'] ?? __('Urban Haven Journal') }}</p>",
    $content
);

$content = str_replace(
    "<h1 class=\"uh-editorial-title\">{{ \$activeCategory?->name ?? __('Explore News, Insights and Videos') }}</h1>",
    "<h1 class=\"uh-editorial-title\">{{ \$activeCategory?->name ?? (\$articlesSections['masthead']['title'] ?? __('Explore News, Insights and Videos')) }}</h1>",
    $content
);

$content = str_replace(
    "<p class=\"uh-editorial-intro\">{{ __('Ideas, places and perspectives shaping the way Bangladesh lives.') }}</p>",
    "<p class=\"uh-editorial-intro\">{{ \$articlesSections['masthead']['intro'] ?? __('Ideas, places and perspectives shaping the way Bangladesh lives.') }}</p>",
    $content
);

file_put_contents('resources/views/public/articles/index.blade.php', $content);
