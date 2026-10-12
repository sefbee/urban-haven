import re

with open('resources/css/public-experience.css', 'r') as f:
    css = f.read()

# Fix the duplicate target
css = css.replace(
    '.uh-listing-body:hover .uh-listing-title span, .uh-listing-body:hover .uh-listing-title span',
    '.uh-listing-body:hover .uh-listing-title span'
)

with open('resources/css/public-experience.css', 'w') as f:
    f.write(css)
