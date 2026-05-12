<?php

namespace App\Support;

use Composer\Script\Event;
use RuntimeException;

class ComposerScripts
{
    public static function copyFluxAssets(Event $event): void
    {
        $vendorPath = $event->getComposer()->getConfig()->get('vendor-dir');
        $publicFluxPath = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'flux';

        if (! is_dir($publicFluxPath) && ! mkdir($publicFluxPath, 0755, true) && ! is_dir($publicFluxPath)) {
            throw new RuntimeException("Unable to create directory [{$publicFluxPath}].");
        }

        foreach (['flux.js', 'flux.min.js', 'editor.js', 'editor.min.js', 'editor.css'] as $asset) {
            $sourcePath = self::firstExistingPath([
                $vendorPath.DIRECTORY_SEPARATOR.'livewire'.DIRECTORY_SEPARATOR.'flux-pro'.DIRECTORY_SEPARATOR.'dist'.DIRECTORY_SEPARATOR.$asset,
                $vendorPath.DIRECTORY_SEPARATOR.'livewire'.DIRECTORY_SEPARATOR.'flux'.DIRECTORY_SEPARATOR.'dist'.DIRECTORY_SEPARATOR.$asset,
            ]);

            if ($sourcePath === null) {
                continue;
            }

            if (! copy($sourcePath, $publicFluxPath.DIRECTORY_SEPARATOR.$asset)) {
                throw new RuntimeException("Unable to copy Flux asset [{$asset}].");
            }
        }
    }

    /**
     * @param  array<int, string>  $paths
     */
    private static function firstExistingPath(array $paths): ?string
    {
        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
