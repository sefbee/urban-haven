<?php

$content = file_get_contents('resources/views/public/partials/property-card.blade.php');
if (strpos($content, 'class="uh-listing-title-link"') === false) {
    $content = str_replace(
        '<a href="{{ $url }}" lang="{{ app()->getLocale() }}" data-track="property_card_click" data-track-property-id="{{ $property->id }}"',
        '<a href="{{ $url }}" lang="{{ app()->getLocale() }}" data-track="property_card_click" data-track-property-id="{{ $property->id }}" class="uh-listing-title-link"',
        $content
    );
    file_put_contents('resources/views/public/partials/property-card.blade.php', $content);
    echo "Added link class.\n";
} else {
    echo "Class already present.\n";
}
