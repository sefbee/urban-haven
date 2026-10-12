import re

with open('resources/views/public/partials/filter-drawer.blade.php', 'r') as f:
    content = f.read()

# 1. Remove Verification
verification_pattern = r'        @php.*?\$verification = array_map\(.*?<fieldset class="uh-drawer-group">.*?<legend class="uh-legend">\{\{ __\(\'Property Verification\'\) \}\}</legend>.*?</fieldset>\s*'
content = re.sub(verification_pattern, '', content, flags=re.DOTALL)

# 2. Remove Furnishing
furnishing_pattern = r'        <fieldset class="uh-drawer-group">\s*<legend class="uh-legend">\{\{ __\(\'Furnishing Status\'\) \}\}</legend>.*?@php.*?\$furnishing = array_map\(.*?</fieldset>\s*'
content = re.sub(furnishing_pattern, '', content, flags=re.DOTALL)

# 3. Remove Property Age
age_pattern = r'        <fieldset class="uh-drawer-group">\s*<legend class="uh-legend">\{\{ __\(\'Property Age\'\) \}\}</legend>.*?@php.*?\$ageChips = \[.*?</fieldset>\s*'
content = re.sub(age_pattern, '', content, flags=re.DOTALL)

# 4. Remove Road Type
road_type_pattern = r'        <fieldset class="uh-drawer-group">\s*<legend class="uh-legend">\{\{ __\(\'Road Type\'\) \}\}</legend>.*?</fieldset>\s*'
content = re.sub(road_type_pattern, '', content, flags=re.DOTALL)

with open('resources/views/public/partials/filter-drawer.blade.php', 'w') as f:
    f.write(content)

print("Patched filter-drawer.blade.php")
