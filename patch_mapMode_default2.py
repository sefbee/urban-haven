import re

with open('resources/js/app.js', 'r') as f:
    content = f.read()

# Change default to mapMode: 'hidden'
# In `resources/js/app.js`, look for `Alpine.data('uhBrowse',`
# Replace mapMode: 'split' with mapMode: 'hidden'
content = content.replace("mapMode: 'split',", "mapMode: 'hidden',")

with open('resources/js/app.js', 'w') as f:
    f.write(content)
