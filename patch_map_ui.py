import re

with open('resources/views/public/properties/map.blade.php', 'r') as f:
    content = f.read()

# 1. Update uh-mapview-fullscreen
target_fullscreen = """                <button type="button" class="uh-mapview-fullscreen"
                        @click="toggleMapMode()"
                        :aria-label="mapMode === 'full' ? '{{ __('Exit full screen map') }}' : '{{ __('Show full screen map') }}'"
                        :title="mapMode === 'full' ? '{{ __('Exit full screen map') }}' : '{{ __('Show full screen map') }}'">
                    <x-icon name="minimize" class="size-4" x-show="mapMode === 'full'" x-cloak />
                    <x-icon name="expand" class="size-4" x-show="mapMode !== 'full'" />
                </button>"""
replace_fullscreen = """                <button type="button" 
                        class="absolute bottom-4 right-4 z-[1000] flex size-10 items-center justify-center bg-white rounded-md shadow-[0_2px_8px_rgb(0,0,0,0.1)] border border-gray-200 text-gray-700 hover:text-black hover:bg-gray-50 transition-colors"
                        @click="mapMode = (mapMode === 'full' ? 'split' : 'full'); $nextTick(() => { window.dispatchEvent(new Event('uh:refresh-maps')); setTimeout(() => window.dispatchEvent(new Event('uh:refresh-maps')), 300); })"
                        :aria-label="mapMode === 'full' ? '{{ __('Exit full screen map') }}' : '{{ __('Show full screen map') }}'"
                        :title="mapMode === 'full' ? '{{ __('Exit full screen map') }}' : '{{ __('Show full screen map') }}'">
                    <x-icon name="minimize" class="size-4" x-show="mapMode === 'full'" x-cloak />
                    <x-icon name="expand" class="size-4" x-show="mapMode !== 'full'" />
                </button>"""

if target_fullscreen in content:
    content = content.replace(target_fullscreen, replace_fullscreen)
else:
    print("WARNING: Could not find target_fullscreen")

# 2. Update uh-mapview-toggle
target_toggle = """            <button type="button" class="uh-mapview-toggle" x-show="mapMode !== 'full'" x-cloak
                    @click="mapMode = (mapMode === 'hidden' ? 'split' : 'hidden')"
                    :aria-label="mapMode === 'hidden' ? '{{ __('Show map') }}' : '{{ __('Hide map') }}'"
                    :title="mapMode === 'hidden' ? '{{ __('Show map') }}' : '{{ __('Hide map') }}'">
                <x-icon name="chevron-left" class="size-3" />
            </button>"""
replace_toggle = """            <button type="button" 
                    class="absolute top-1/2 -right-5 z-[600] flex h-12 w-5 items-center justify-center rounded-r-md bg-white text-slate-600 shadow-md border border-l-0 border-gray-200 hover:text-slate-900 transition-colors -translate-y-1/2"
                    x-show="mapMode === 'split'" x-cloak
                    @click="mapMode = 'hidden'"
                    aria-label="{{ __('Hide map') }}" title="{{ __('Hide map') }}">
                <x-icon name="chevron-left" class="size-3" />
            </button>"""

if target_toggle in content:
    content = content.replace(target_toggle, replace_toggle)
else:
    print("WARNING: Could not find target_toggle")

# 3. Update uh-mapview-show-map
target_show_map = """            <button type="button" class="uh-mapview-show-map" x-show="mapMode === 'hidden'" x-cloak
                    @click="mapMode = 'split'; $nextTick(() => { window.dispatchEvent(new Event('uh:refresh-maps')); setTimeout(() => window.dispatchEvent(new Event('uh:refresh-maps')), 300); })"
                    aria-label="{{ __('Show map') }}" title="{{ __('Show map') }}">
                <x-icon name="chevron-right" class="size-3" />
                <span>{{ __('Show map') }}</span>
            </button>"""
replace_show_map = """            <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[1500]" x-show="mapMode === 'hidden'" x-cloak>
                <button type="button" 
                        class="flex items-center gap-3 bg-black text-white rounded-full pl-5 pr-1.5 py-1.5 shadow-lg hover:bg-gray-900 transition-colors"
                        @click="mapMode = 'split'; $nextTick(() => { window.dispatchEvent(new Event('uh:refresh-maps')); setTimeout(() => window.dispatchEvent(new Event('uh:refresh-maps')), 300); })"
                        aria-label="{{ __('Show map') }}">
                    <span class="font-medium text-[0.9375rem]">{{ __('Show map') }}</span>
                    <span class="bg-white text-black rounded-full size-8 flex items-center justify-center">
                        <i class="fa-solid fa-map"></i>
                    </span>
                </button>
            </div>"""

if target_show_map in content:
    content = content.replace(target_show_map, replace_show_map)
else:
    print("WARNING: Could not find target_show_map")


with open('resources/views/public/properties/map.blade.php', 'w') as f:
    f.write(content)

print("Patched map.blade.php")
