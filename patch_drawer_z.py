import re

with open('resources/views/public/partials/filter-drawer.blade.php', 'r') as f:
    content = f.read()

# Replace z-[70] with z-[1200]
content = content.replace('z-[70]', 'z-[1200]')

with open('resources/views/public/partials/filter-drawer.blade.php', 'w') as f:
    f.write(content)

print("Patched drawer z-index")
