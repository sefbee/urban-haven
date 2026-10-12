import re

with open('resources/css/public-experience.css', 'r') as f:
    css = f.read()

# Add hover effect for the whole card body
css = css + '''
.uh-listing-body:hover .uh-listing-title span {
    color: var(--uh-primary);
}
'''

with open('resources/css/public-experience.css', 'w') as f:
    f.write(css)
