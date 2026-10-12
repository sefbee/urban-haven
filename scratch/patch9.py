import re

with open('resources/views/public/partials/property-card.blade.php', 'r') as f:
    html = f.read()

# Fix the duplicate a tag closing which is causing issues
html = html.replace(
    '        </a>\n        <div class="uh-listing-foot">',
    '        <div class="uh-listing-foot">'
)
html = html.replace(
    '    <div class="uh-listing-body">',
    '    </a>\n    <div class="uh-listing-body">'
)

with open('resources/views/public/partials/property-card.blade.php', 'w') as f:
    f.write(html)
