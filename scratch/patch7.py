import re

with open('resources/css/public-experience.css', 'r') as f:
    css = f.read()

# Add transition to the span
css = css.replace(
    '.uh-listing-title {\n        font-size: 1.125rem;\n        font-weight: 600;\n        line-height: 1.3;\n        letter-spacing: -0.012em;\n    }',
    '.uh-listing-title {\n        font-size: 1.125rem;\n        font-weight: 600;\n        line-height: 1.3;\n        letter-spacing: -0.012em;\n    }\n\n    .uh-listing-title span {\n        transition: color var(--uh-dur-2) var(--uh-ease);\n    }'
)

with open('resources/css/public-experience.css', 'w') as f:
    f.write(css)
