import re

with open('resources/views/public/contact.blade.php', 'r') as f:
    content = f.read()

# Replace hex colors
content = content.replace('#a68456', 'var(--color-accent)')
content = content.replace('#c5a676', 'var(--color-accent)')
content = content.replace('#fbf9f6', 'var(--color-dominant)')
content = content.replace('#f4f2ef', 'var(--color-dominant)')

# Replace Tailwind classes
content = re.sub(r'\bbg-paper\b', 'bg-[var(--color-dominant)]', content)
content = re.sub(r'\bbg-night\b', 'bg-[var(--color-secondary)]', content)
content = re.sub(r'\bbg-ink\b', 'bg-[var(--color-secondary)]', content)
content = re.sub(r'\btext-ink\b', 'text-[var(--color-body-text)]', content)
content = re.sub(r'\btext-muted\b', 'text-[var(--color-muted-text)]', content)

# Wait, the hex colors might be in arbitrary values like `bg-[#a68456]`. 
# Because we replaced the hex directly, `bg-[#a68456]` became `bg-[var(--color-accent)]`, which works perfectly in Tailwind v4!

with open('resources/views/public/contact.blade.php', 'w') as f:
    f.write(content)

