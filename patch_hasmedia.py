import re

with open('app/Models/Concerns/HasMedia.php', 'r') as f:
    content = f.read()

# Replace publicCollection logic to just return all media if they want all images.
# Actually, the user said "remove this logic and make image card show all available image"
# I will change galleryImages to return all media.
# Wait, they might still want it sorted or just the media.
new_content = content.replace("""    public function galleryImages(): Collection
    {
        return $this->publicCollection('gallery');
    }""", """    public function galleryImages(): Collection
    {
        // Return all available images as requested by the user, ignoring is_public and collection filters
        return $this->media;
    }""")

with open('app/Models/Concerns/HasMedia.php', 'w') as f:
    f.write(new_content)

print("Patched HasMedia.php")
