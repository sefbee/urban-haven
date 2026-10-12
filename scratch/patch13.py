import re

with open('resources/css/public-experience.css', 'r') as f:
    css = f.read()

# Make whole article a relative block to contain full link overlay
css = css.replace(
    '.uh-listing {\n        position: relative;\n        display: flex;\n        min-width: 0;\n        flex-direction: column;\n    }',
    '.uh-listing {\n        position: relative;\n        display: flex;\n        min-width: 0;\n        flex-direction: column;\n    }\n\n    .uh-listing:hover .uh-listing-full-link {\n        z-index: 40;\n    }\n\n    .uh-listing-full-link {\n        display: block;\n    }'
)

with open('resources/css/public-experience.css', 'w') as f:
    f.write(css)
