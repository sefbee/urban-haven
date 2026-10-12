import re

with open('resources/css/public-experience.css', 'r') as f:
    css = f.read()

# remove old before and after link target which now breaks the layout
css = re.sub(
    r'\.uh-listing-title-link::before,\n\.uh-listing-title-link::after \{\n    content: "";\n    position: absolute;\n    inset: 0;\n    z-index: 1;\n\}',
    '',
    css
)

with open('resources/css/public-experience.css', 'w') as f:
    f.write(css)
