import re

with open('resources/js/public-selects.js', 'r') as f:
    content = f.read()

target = "    if (!document.body.classList.contains('uh-home')) {\n        return;\n    }"
if target in content:
    content = content.replace(target, "    // if (!document.body.classList.contains('uh-home')) {\n    //     return;\n    // }")
else:
    print("Could not find body class check")

with open('resources/js/public-selects.js', 'w') as f:
    f.write(content)

print("Patched public-selects.js")
