import re

with open('resources/views/public/partials/property-card.blade.php', 'r') as f:
    html = f.read()

# Make link explicit with explicit z-index
html = html.replace(
    '<a class="line-clamp-2 uh-listing-title-link"',
    '<a class="line-clamp-2 uh-listing-title-link" style="position: static; z-index: auto;"'
)

# Make whole body relative and clickable instead of the link pseudo element
html = html.replace(
    '<div class="uh-listing-body">',
    '<a href="{{ $url }}" class="uh-listing-body" style="display: block; text-decoration: none; color: inherit;">'
)
html = html.replace(
    '        <div class="uh-listing-foot">',
    '        </a>\n        <div class="uh-listing-foot">'
)

# Remove the a tag from inside the body
html = re.sub(
    r'<h3 class="uh-listing-title">\s*<a class="line-clamp-2 uh-listing-title-link" [^>]+>([^<]+)</a>\s*</h3>',
    r'<h3 class="uh-listing-title"><span class="line-clamp-2 uh-listing-title-link">\1</span></h3>',
    html
)

with open('resources/views/public/partials/property-card.blade.php', 'w') as f:
    f.write(html)
