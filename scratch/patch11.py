import re

with open('resources/views/public/partials/property-card.blade.php', 'r') as f:
    html = f.read()

# Make tools overlay explicitly on top and pointer-events auto
html = html.replace(
    '<span class="uh-listing-tools">',
    '<span class="uh-listing-tools" style="position: absolute; z-index: 50; pointer-events: auto;">'
)

# Convert whole article into a relatively positioned container to trap the absolute links
html = html.replace(
    'class="uh-listing" data-spotlight="{{ $property->id }}"',
    'class="uh-listing" data-spotlight="{{ $property->id }}" style="position: relative;"'
)

# Replace the title a tag with a full overlay a tag inside the article but pushed to bottom of stacking context
html = html.replace(
    '<article',
    '<article'
)
html = html.replace(
    '    <div class="uh-listing-frame"',
    '    <a href="{{ $url }}" class="uh-listing-full-link" style="position: absolute; inset: 0; z-index: 1;" data-track="property_card_click" data-track-property-id="{{ $property->id }}"></a>\n    <div class="uh-listing-frame"'
)

# Make sure frame has higher z-index than the link, but pointer-events none except for carousel buttons
html = html.replace(
    '    <div class="uh-listing-frame"',
    '    <div class="uh-listing-frame" style="position: relative; z-index: 2; pointer-events: none;"'
)

# Revert title to just a span
html = re.sub(
    r'<h3 class="uh-listing-title">\s*<a class="line-clamp-2 uh-listing-title-link"[^>]*>([^<]+)</a>\s*</h3>',
    r'<h3 class="uh-listing-title"><span class="line-clamp-2 uh-listing-title-link">\1</span></h3>',
    html
)

# Make footer relative and pointer events auto
html = html.replace(
    '        <div class="uh-listing-foot">',
    '        <div class="uh-listing-foot" style="position: relative; z-index: 10; pointer-events: auto;">'
)

with open('resources/views/public/partials/property-card.blade.php', 'w') as f:
    f.write(html)
