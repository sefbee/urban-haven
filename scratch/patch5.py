import re

with open('resources/views/public/partials/property-card.blade.php', 'r') as f:
    html = f.read()

# Add tools z-index
html = html.replace(
    '<span class="uh-listing-tools">',
    '<span class="uh-listing-tools" style="position: absolute; z-index: 50; pointer-events: auto;">'
)

# Convert whole body to link
html = html.replace(
    '<div class="uh-listing-body">',
    '<a href="{{ $url }}" class="uh-listing-body" style="display: block; text-decoration: none; color: inherit;" data-track="property_card_click" data-track-property-id="{{ $property->id }}">'
)
html = html.replace(
    '        <div class="uh-listing-foot">',
    '        </a>\n        <div class="uh-listing-foot">'
)

# Remove nested a tag from title
html = re.sub(
    r'<h3 class="uh-listing-title">\s*<a class="line-clamp-2 uh-listing-title-link"[^>]*>([^<]+)</a>\s*</h3>',
    r'<h3 class="uh-listing-title"><span class="line-clamp-2 uh-listing-title-link">\1</span></h3>',
    html
)

with open('resources/views/public/partials/property-card.blade.php', 'w') as f:
    f.write(html)
