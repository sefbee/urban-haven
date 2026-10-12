import re

with open('resources/css/public-experience.css', 'r') as f:
    content = f.read()

target = "    .select2-container--uh .select2-dropdown {\n        z-index: 1100;"
replace = "    .select2-container--uh .select2-dropdown {\n        z-index: 1300;"

if target in content:
    content = content.replace(target, replace)
    with open('resources/css/public-experience.css', 'w') as f:
        f.write(content)
    print("Fixed z-index of select2 dropdown")
else:
    print("WARNING: Target not found")
