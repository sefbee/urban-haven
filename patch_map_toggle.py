import re

with open('resources/views/public/properties/map.blade.php', 'r') as f:
    content = f.read()

# Replace 'split' with 'hidden' in the toggle button
# Currently: mapMode = (mapMode === 'full' ? 'split' : 'full');
new_content = content.replace("mapMode = (mapMode === 'full' ? 'split' : 'full');", "mapMode = (mapMode === 'hidden' ? 'split' : 'hidden');")

# Also update the icon logic
# If mapMode is hidden, we want chevron-right (to expand to split)
# If mapMode is split, we want chevron-left (to collapse to hidden)
new_content = new_content.replace("""<x-icon name="chevron-right" class="size-3" x-show="mapMode === 'split'" />
                <x-icon name="chevron-left" class="size-3" x-show="mapMode === 'full'" x-cloak />""", """<x-icon name="chevron-left" class="size-3" x-show="mapMode === 'hidden'" x-cloak />
                <x-icon name="chevron-right" class="size-3" x-show="mapMode === 'split'" />""")

# Also fix the aria labels and title logic
new_content = new_content.replace(""":aria-label="mapMode === 'full' ? 'Show side panel' : 'Collapse side panel'\"""", """:aria-label="mapMode === 'hidden' ? 'Show map' : 'Hide map'\"""")
new_content = new_content.replace(""":title="mapMode === 'full' ? 'Show side panel' : 'Collapse side panel'\"""", """:title="mapMode === 'hidden' ? 'Show map' : 'Hide map'\"""")

with open('resources/views/public/properties/map.blade.php', 'w') as f:
    f.write(new_content)

print("Updated map toggle logic")
