import re

with open('resources/views/public/properties/map.blade.php', 'r') as f:
    content = f.read()

# I will rewrite the button toggle logic from scratch.
# The user wants "hide or full screen, default stays as this size"
# Which means they want it to toggle between 'hidden' and 'full' maybe?
# Let's look at the images.
# In the first image, mapMode is hidden (we only see the list view and a sliver of map). But they clicked a button on the MAP pane. Wait, if mapMode is 'hidden', the map is hidden. In the first image, the map is hidden on the left, but the list takes up full width? No, the list is on the right, map is on the left.
# Wait, look at the first image: map is visible, list is visible. "Showing 1-3 of 3". But wait! The chevron button is on the list container? No, it's on the right edge of the map container.
# If they click the button, the map becomes full screen (second image).
# No, in the second image, the map takes up the left half, and the list takes up the right half. Wait, the first image shows the map takes up the LEFT side and is half-width. Oh!
# In the first image, they are hovering over the button. The map is taking up the left pane, the list is on the right pane. This is 'split' mode.
# If they click the button, what happens?
# "map toggle is perfect just make it hide or full screen, default stays as this size"
# Wait! In my previous fix, I made it toggle between 'hidden' and 'split'.
# The user wants it to toggle between 'hidden' and 'full screen'? No, they want it to toggle between 'split' and 'full'.
# Because 'split' is the default. "default stays as this size".
# Wait, "make it hide or full screen" -> Maybe they mean when you click the button, it hides the LIST and makes the MAP full screen? Yes! That's what it did originally!
# Originally it toggled between 'split' and 'full'. 
# When mapMode is 'full', the list is hidden, map takes 100% width.
# When mapMode is 'hidden', map is hidden, list takes 100% width.
# My previous attempt just messed it up by changing 'full' to 'hidden'.
# But wait, look at the UI in the first image. The button is pointing RIGHT `>`.
# If the map is on the left, and list is on the right, and the button is on the right edge of the MAP.
# If they click `>`, it should push the list away to the right, making the map FULL SCREEN. So `>` means "expand map".
# If the map is full screen, the button should point LEFT `<`. Clicking `<` pulls the list back in, restoring 'split' mode.
# So the toggle should be between 'split' and 'full'.
# Let's restore the logic to what it was in my very first fix (split vs full) which was:
# @click="mapMode = (mapMode === 'full' ? 'split' : 'full'); ..."
# :class="mapMode === 'full' ? '-right-5' : '-right-5 lg:-right-10'"
# Chevron right `>` for split mode (to expand to full).
# Chevron left `<` for full mode (to collapse to split).
# Let's fix the file back to that exact state.

target = """            <button type="button" 
                    class="absolute top-1/2 -right-5 z-[600] flex h-12 w-5 items-center justify-center rounded-r-md bg-white text-slate-600 shadow-md border border-l-0 border-gray-200 hover:text-slate-900 transition-colors -translate-y-1/2"
                    :class="mapMode === 'hidden' ? '-right-5' : '-right-5 lg:-right-10'"
                    @click="mapMode = (mapMode === 'hidden' ? 'split' : 'hidden'); $nextTick(() => { window.dispatchEvent(new Event('uh:refresh-maps')); setTimeout(() => window.dispatchEvent(new Event('uh:refresh-maps')), 300); })"
                    :aria-label="mapMode === 'hidden' ? 'Show map' : 'Hide map'"
                    :title="mapMode === 'hidden' ? 'Show map' : 'Hide map'">
                <x-icon name="chevron-left" class="size-3" x-show="mapMode === 'hidden'" x-cloak />
                <x-icon name="chevron-right" class="size-3" x-show="mapMode === 'split'" />
            </button>"""

replacement = """            <button type="button" 
                    class="absolute top-1/2 -right-5 z-[600] flex h-12 w-5 items-center justify-center rounded-r-md bg-white text-slate-600 shadow-md border border-l-0 border-gray-200 hover:text-slate-900 transition-colors -translate-y-1/2"
                    :class="mapMode === 'full' ? '-right-5' : '-right-5 lg:-right-10'"
                    @click="mapMode = (mapMode === 'full' ? 'split' : 'full'); $nextTick(() => { window.dispatchEvent(new Event('uh:refresh-maps')); setTimeout(() => window.dispatchEvent(new Event('uh:refresh-maps')), 300); })"
                    :aria-label="mapMode === 'full' ? 'Show side panel' : 'Hide side panel'"
                    :title="mapMode === 'full' ? 'Show side panel' : 'Hide side panel'">
                <x-icon name="chevron-right" class="size-3" x-show="mapMode === 'split'" />
                <x-icon name="chevron-left" class="size-3" x-show="mapMode === 'full'" x-cloak />
            </button>"""

if target in content:
    content = content.replace(target, replacement)
else:
    print("WARNING: Could not find target button block!")

with open('resources/views/public/properties/map.blade.php', 'w') as f:
    f.write(content)

