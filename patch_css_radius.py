import re

with open('resources/css/public-experience.css', 'r') as f:
    content = f.read()

# I see `border-radius: 0;` on line 3779 in `.uh-listing:not(.uh-listing-row) .uh-listing-frame`.
# Also the user wants exactly the layout seen in their first image, and the earlier images.
# In the trending section, `.uh-listing:not(.uh-listing-row) .uh-listing-frame` has `aspect-ratio: 16 / 10;`.
# We need to make sure the border-radius is preserved for the top corners, or just the same as the user's layout.
# Based on the user's latest images:
# - Top left and top right corners of the image are rounded.
# - The bottom of the image is flat.
# Wait, look at `resources/css/public-experience.css`:
# `.uh-listing:not(.uh-listing-row)` has `border-radius: 0.5rem;` and `overflow: hidden;` in `body.uh-home .uh-trending-section .uh-listing:not(.uh-listing-row)`.
# But `border-radius: 0;` inside `.uh-listing:not(.uh-listing-row) .uh-listing-frame` overrides `.uh-listing-frame`'s `border-radius: var(--uh-radius-sm);` (which was probably 0.5rem).

# Let's fix this by adding `border-radius: 0.5rem 0.5rem 0 0;` to `.uh-listing:not(.uh-listing-row) .uh-listing-frame`.
new_content = re.sub(
    r'\.uh-listing:not\(\.uh-listing-row\) \.uh-listing-frame \{\n\s*isolation: isolate;\n\s*aspect-ratio: 16 / 9;\n\s*border-radius: 0;\n\s*\}',
    r'.uh-listing:not(.uh-listing-row) .uh-listing-frame {\n        isolation: isolate;\n        aspect-ratio: 16 / 9;\n        border-radius: 0.5rem 0.5rem 0 0;\n    }',
    content
)

new_content = re.sub(
    r'body\.uh-home \.uh-trending-section \.uh-listing:not\(\.uh-listing-row\) \.uh-listing-frame \{\n\s*isolation: isolate;\n\s*flex: 0 0 auto;\n\s*aspect-ratio: 16 / 10;\n\}',
    r'body.uh-home .uh-trending-section .uh-listing:not(.uh-listing-row) .uh-listing-frame {\n    isolation: isolate;\n    flex: 0 0 auto;\n    aspect-ratio: 16 / 10;\n    border-radius: 0.5rem 0.5rem 0 0;\n}',
    new_content
)

with open('resources/css/public-experience.css', 'w') as f:
    f.write(new_content)

print("Patched CSS radii")
