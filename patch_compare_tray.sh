#!/bin/bash
sed -i 's|<a href="{{ route('"'"'compare'"'"') }}" class="uh-compare-tray"|<div class="uh-compare-tray" style="padding-right:0.5rem"|' resources/views/layouts/public.blade.php

# Wait, this would ruin the closing tag.
