import re

with open('resources/views/public/partials/filter-drawer.blade.php', 'r') as f:
    content = f.read()

# 2. Remove Furnishing
furnishing_pattern = r'<fieldset class="uh-drawer-group">\s*<legend class="uh-legend">\{\{ __\(\'Furnishing Status\'\) \}\}</legend>.*?</fieldset>\s*'
content = re.sub(furnishing_pattern, '', content, flags=re.DOTALL)

# 4. Remove Road Type
road_type_pattern = r'<fieldset class="uh-drawer-group">\s*<legend class="uh-legend">\{\{ __\(\'Road Type\'\) \}\}</legend>.*?</fieldset>\s*'
content = re.sub(road_type_pattern, '', content, flags=re.DOTALL)

with open('resources/views/public/partials/filter-drawer.blade.php', 'w') as f:
    f.write(content)

print("Patched filter-drawer.blade.php again")
