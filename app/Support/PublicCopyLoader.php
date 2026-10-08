<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Translation\Loader;

final class PublicCopyLoader implements Loader
{
    public function __construct(private Loader $loader) {}

    public function load($locale, $group, $namespace = null): array
    {
        $lines = $this->loader->load($locale, $group, $namespace);

        if ($group !== '*' || $namespace !== '*' || app()->runningInConsole()) {
            return $lines;
        }

        $overrides = Setting::get('public_copy', []);

        if (! is_array($overrides)) {
            return $lines;
        }

        foreach ($overrides as $source => $replacement) {
            if (is_string($source) && is_string($replacement) && $replacement !== '') {
                $lines[$source] = $replacement;
            }
        }

        return $lines;
    }

    public function addNamespace($namespace, $hint): void
    {
        $this->loader->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path): void
    {
        $this->loader->addJsonPath($path);
    }

    public function namespaces(): array
    {
        return $this->loader->namespaces();
    }
}
