import re

with open('resources/css/public-experience.css', 'r') as f:
    css = f.read()

bad_grid = """        .uh-pd-gallery.has-thumbs {
            grid-template-columns: minmax(0, 85fr) minmax(0, 15fr);
            gap: 0;
        }

        .uh-pd-gallery.has-two {
            grid-template-rows: repeat(2, minmax(0, 1fr));
        }

        .uh-pd-gallery.has-two .uh-pd-shot.is-main {
            grid-row: span 2;
        }"""

new_grid = """        .uh-pd-gallery {
            gap: 0.5rem;
        }

        .uh-pd-gallery.has-thumbs {
            grid-template-columns: minmax(0, 75fr) minmax(0, 25fr);
        }

        .uh-pd-gallery.has-two {
            grid-template-rows: repeat(2, minmax(0, 1fr));
        }

        .uh-pd-gallery.has-two .uh-pd-shot.is-main {
            grid-row: span 2;
        }

        .uh-pd-gallery.has-three {
            grid-template-rows: repeat(3, minmax(0, 1fr));
        }

        .uh-pd-gallery.has-three .uh-pd-shot.is-main {
            grid-row: span 3;
        }
        
        .uh-pd-shot {
            border-radius: 0.5rem;
            overflow: hidden;
        }"""

if bad_grid in css:
    css = css.replace(bad_grid, new_grid)
    with open('resources/css/public-experience.css', 'w') as f:
        f.write(css)
    print("Patched css")
else:
    print("Could not find bad_grid block!")

# Also fix the font size of the more photos overlay
old_more = """    .uh-pd-shot-more {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        background: rgb(0 0 0 / 0.42);
        color: var(--uh-white);
        font-size: 1.5rem;
        font-weight: 600;
    }"""
new_more = """    .uh-pd-shot-more {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        background: rgb(0 0 0 / 0.42);
        color: var(--uh-white);
        font-size: 1rem;
        font-weight: 500;
    }"""
css = css.replace(old_more, new_more)

with open('resources/css/public-experience.css', 'w') as f:
    f.write(css)

