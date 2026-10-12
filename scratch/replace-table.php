<?php

$content = file_get_contents('resources/views/public/partials/saved-compare.blade.php');

$newContent = str_replace(
    '<span class="uh-compare-lead">{{ $leaders[$key][1] }}</span>',
    '<span class="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded bg-[var(--uh-accent-ring)] text-[var(--uh-accent)] text-xs font-medium">
                                                <svg class="size-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                                {{ $leaders[$key][1] }}
                                            </span>',
    $content
);

file_put_contents('resources/views/public/partials/saved-compare.blade.php', $newContent);
echo "Replaced table\n";
