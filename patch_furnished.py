import re

with open('resources/views/public/partials/filter-drawer.blade.php', 'r') as f:
    content = f.read()

furnished_block = """
        <fieldset class="uh-drawer-group">
            <legend class="uh-legend">{{ __('Furnished') }}</legend>
            <div class="grid gap-1">
                <label class="uh-choice">
                    <input type="radio" form="property-filters" name="is_furnished" value="" @checked($furnished === null)>
                    <span>{{ __('Any') }}</span>
                </label>
                <label class="uh-choice">
                    <input type="radio" form="property-filters" name="is_furnished" value="1" @checked($furnished === true || $furnished === '1')>
                    <span>{{ __('Yes') }}</span>
                </label>
                <label class="uh-choice">
                    <input type="radio" form="property-filters" name="is_furnished" value="0" @checked($furnished === false || $furnished === '0')>
                    <span>{{ __('No') }}</span>
                </label>
            </div>
        </fieldset>
"""

# Insert before Property Facing
target = """<fieldset class="uh-drawer-group">
            <legend class="uh-legend">{{ __('Property Facing') }}</legend>"""

if target in content:
    content = content.replace(target, furnished_block + target)
else:
    print("WARNING: Could not find target to insert Furnished")

with open('resources/views/public/partials/filter-drawer.blade.php', 'w') as f:
    f.write(content)

print("Restored actual furnished filter")
