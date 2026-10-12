import re

with open('resources/views/public/properties/map.blade.php', 'r') as f:
    content = f.read()

# Update :class logic
# It used to be :class="mapMode === 'full' ? '-right-5' : '-right-5 lg:-right-10'"
new_content = content.replace(""":class="mapMode === 'full' ? '-right-5' : '-right-5 lg:-right-10'\"""", """:class="mapMode === 'hidden' ? '-right-5' : '-right-5 lg:-right-10'\"""")

with open('resources/views/public/properties/map.blade.php', 'w') as f:
    f.write(new_content)

print("Updated map toggle logic (class)")
