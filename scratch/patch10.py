import re

with open('resources/views/public/partials/property-card.blade.php', 'r') as f:
    html = f.read()

# Make the card actions relative and clickable
html = html.replace(
    '        <div class="uh-listing-foot">',
    '        </a>\n        <div class="uh-listing-foot" style="position: relative; z-index: 10;">'
)
# Make image part linkable too since it's absolute
html = html.replace(
    '    <div class="uh-listing-frame"',
    '    <a href="{{ $url }}" class="uh-listing-frame" style="display: block; text-decoration: none;"'
)
html = html.replace(
    '    <a href="{{ $url }}" class="uh-listing-body"',
    '    </a>\n    <a href="{{ $url }}" class="uh-listing-body"'
)

with open('resources/views/public/partials/property-card.blade.php', 'w') as f:
    f.write(html)
