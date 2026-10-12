import re

with open('resources/css/public-experience.css', 'r') as f:
    content = f.read()

# Make sure .uh-listing has the right background, box-shadow and border-radius.
# Right now, `body.uh-home .uh-trending-section .uh-listing:not(.uh-listing-row)` has `border-radius: 0.5rem;`
# But let's check `.uh-listing:not(.uh-listing-row)`
new_content = re.sub(
    r'\.uh-listing:not\(\.uh-listing-row\) \{\n\s*height: 100%;\n\s*border-radius: 0\.5rem;\n\s*background: var\(--uh-white\);\n\s*box-shadow: 0 12px 30px -24px rgb\(29 29 31 / 0\.45\), 0 2px 8px rgb\(29 29 31 / 0\.05\);',
    r'.uh-listing:not(.uh-listing-row) {\n        height: 100%;\n        border-radius: 0.5rem;\n        background: var(--uh-white);\n        box-shadow: 0 12px 30px -24px rgb(29 29 31 / 0.45), 0 2px 8px rgb(29 29 31 / 0.05);',
    content
)

# In trending section, the user showed images with white background, rounded corners.
# Let's ensure the `uh-listing` itself has a box shadow so we can see the rounding.
# Actually, the user screenshots show `uh-listing` cards with a shadow.

# Let's just run it to see if we can fix the rounding. The issue the user says is:
# "fix"
# User uploaded 3 images. They are screenshots of the cards on the homepage.
# Looking at the images... the top rounded corners of the image inside the card are gone (they are sharp).
# And there is a small gap or padding issue. Wait, the carousel buttons (the arrows) are missing or cut off.
# Actually, the images show the card images being square on top, with the rest of the card rounded, breaking the corner rounding.
# And wait! The image is not filling the top! There's a white border around the image? No, the image is overflowing or not properly rounded at the top.
# Let's make sure `.uh-listing-frame` has `border-radius: 0.5rem 0.5rem 0 0;` and `.uh-listing` has `border-radius: 0.5rem;`
# Also in `uh-carousel-slide`, `img` needs `border-radius: inherit;` maybe?
# Let's add `border-radius: inherit;` to `.uh-listing-frame` and `.uh-listing-frame img`.
