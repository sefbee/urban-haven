import re

with open('resources/css/public-experience.css', 'r') as f:
    css = f.read()

if '.uh-listing-title-link::before' not in css:
    css = css.replace('.uh-listing-title-link::after {', '.uh-listing-title-link::before,\n.uh-listing-title-link::after {')

with open('resources/css/public-experience.css', 'w') as f:
    f.write(css)
