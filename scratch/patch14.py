import re

with open('resources/views/public/partials/property-card.blade.php', 'r') as f:
    html = f.read()

# Make the wrapper relative and pointer events auto for carousel
html = html.replace(
    "@include('public.partials.property-carousel'",
    "<div style=\"position: relative; z-index: 50; pointer-events: auto;\">\n            @include('public.partials.property-carousel'"
)

html = html.replace(
    "['sizes' => $sizes ?? '(min-width: 1024px) 30vw, (min-width: 640px) 50vw, 100vw'])",
    "['sizes' => $sizes ?? '(min-width: 1024px) 30vw, (min-width: 640px) 50vw, 100vw'])\n        </div>"
)

with open('resources/views/public/partials/property-card.blade.php', 'w') as f:
    f.write(html)
