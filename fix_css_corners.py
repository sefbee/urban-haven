import re

with open('resources/css/public-experience.css', 'r') as f:
    css = f.read()

bad_block = """        .uh-pd-shot {
            border-radius: 1rem;
            overflow: hidden;
        }"""

if bad_block in css:
    css = css.replace(bad_block, "")
    with open('resources/css/public-experience.css', 'w') as f:
        f.write(css)
    print("Fixed bad block")
else:
    print("Bad block not found")

