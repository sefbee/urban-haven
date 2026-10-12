import re

with open('resources/css/public-experience.css', 'r') as f:
    content = f.read()

# Fix the trending section border radius.
# .uh-listing:not(.uh-listing-row) { border-radius: 0.5rem; }
# .uh-listing:not(.uh-listing-row) .uh-listing-frame { border-radius: 0.5rem 0.5rem 0 0; }
# .uh-listing:not(.uh-listing-row) .uh-listing-frame img { border-radius: 0.5rem 0.5rem 0 0; } (or just let overflow: hidden do it, but we need overflow hidden on the frame).

new_content = re.sub(
    r'body\.uh-home \.uh-trending-section \.uh-listing:not\(\.uh-listing-row\) \.uh-listing-frame \{\n\s*isolation: isolate;\n\s*flex: 0 0 auto;\n\s*aspect-ratio: 16 / 10;\n\s*border-radius: 0\.5rem 0\.5rem 0 0;\n\}',
    r'body.uh-home .uh-trending-section .uh-listing:not(.uh-listing-row) .uh-listing-frame {\n    isolation: isolate;\n    flex: 0 0 auto;\n    aspect-ratio: 16 / 10;\n    border-radius: 0.5rem 0.5rem 0 0;\n    overflow: hidden;\n}',
    content
)

# And the base one:
new_content = re.sub(
    r'\.uh-listing:not\(\.uh-listing-row\) \.uh-listing-frame \{\n\s*isolation: isolate;\n\s*aspect-ratio: 16 / 9;\n\s*border-radius: 0\.5rem 0\.5rem 0 0;\n\s*\}',
    r'.uh-listing:not(.uh-listing-row) .uh-listing-frame {\n        isolation: isolate;\n        aspect-ratio: 16 / 9;\n        border-radius: inherit;\n        border-bottom-left-radius: 0;\n        border-bottom-right-radius: 0;\n        overflow: hidden;\n    }',
    new_content
)

# Also let's just make sure `.uh-listing-frame` itself has overflow hidden.
new_content = re.sub(
    r'\.uh-listing-frame \{\n\s*isolation: isolate;\n\s*position: relative;\n\s*aspect-ratio: 4 / 3;\n\s*overflow: hidden;\n\s*border-radius: var\(--uh-radius-sm\);\n\s*background: var\(--uh-paper\);\n\s*\}',
    r'.uh-listing-frame {\n        isolation: isolate;\n        position: relative;\n        aspect-ratio: 4 / 3;\n        overflow: hidden;\n        border-radius: inherit;\n        border-bottom-left-radius: 0;\n        border-bottom-right-radius: 0;\n        background: var(--uh-paper);\n    }',
    new_content
)

with open('resources/css/public-experience.css', 'w') as f:
    f.write(new_content)

print("Patched")
