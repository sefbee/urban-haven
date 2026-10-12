import re

with open('resources/css/public-experience.css', 'r') as f:
    css = f.read()

# Make sure .uh-drawer has high enough z-index to be above .uh-scrim-modal z-[1200]
bad_drawer = """    .uh-drawer {
        max-width: 30rem;
        overflow: hidden;
        border-radius: 1.25rem 0 0 1.25rem;
        background: #f6f7f4;
        box-shadow: -20px 0 64px rgb(20 24 22 / 0.2);
    }"""
new_drawer = """    .uh-drawer {
        max-width: 30rem;
        overflow: hidden;
        border-radius: 1.25rem 0 0 1.25rem;
        background: #f6f7f4;
        box-shadow: -20px 0 64px rgb(20 24 22 / 0.2);
        z-index: 1250;
    }"""

if bad_drawer in css:
    css = css.replace(bad_drawer, new_drawer)
    with open('resources/css/public-experience.css', 'w') as f:
        f.write(css)
    print("Patched css drawer z-index")
else:
    print("Could not find drawer block")

