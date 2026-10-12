import re

with open('app/Http/Controllers/Public/HomeController.php', 'r') as f:
    content = f.read()

# Make sure we didn't break HomeController.
# Let's verify if the image query eager loading works for home page.
