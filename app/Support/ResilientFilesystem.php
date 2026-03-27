<?php

namespace App\Support;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class ResilientFilesystem extends Filesystem
{
    public function replace($path, $content, $mode = null): void
    {
        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        $tempPath = tempnam(dirname($path), basename($path));

        if ($tempPath === false) {
            throw new RuntimeException("Unable to create a temporary file for [{$path}].");
        }

        try {
            file_put_contents($tempPath, $content);

            if ($mode !== null) {
                @chmod($tempPath, $mode);
            }

            $this->replaceTemporaryFile($tempPath, $path);
        } finally {
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    protected function replaceTemporaryFile(string $tempPath, string $path): void
    {
        $attempts = $this->shouldRetryReplace() ? 5 : 1;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            if ($this->attemptRename($tempPath, $path)) {
                return;
            }

            clearstatcache(true, $path);
            $this->prepareTargetForRetry($path);

            if ($this->attemptRename($tempPath, $path)) {
                return;
            }

            if ($attempt < $attempts) {
                usleep(100000 * $attempt);
            }
        }

        if ($this->attemptCopy($tempPath, $path)) {
            return;
        }

        throw new RuntimeException($this->buildReplaceFailureMessage($path));
    }

    protected function shouldRetryReplace(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }

    protected function prepareTargetForRetry(string $path): void
    {
        if (! $this->exists($path)) {
            return;
        }

        @chmod($path, 0666);
        @unlink($path);
    }

    protected function attemptRename(string $tempPath, string $path): bool
    {
        return @rename($tempPath, $path);
    }

    protected function attemptCopy(string $tempPath, string $path): bool
    {
        if (! @copy($tempPath, $path)) {
            return false;
        }

        return @unlink($tempPath) || ! is_file($tempPath);
    }

    protected function buildReplaceFailureMessage(string $path): string
    {
        $error = error_get_last();
        $message = $error['message'] ?? 'Unknown filesystem error.';

        return "Unable to replace [{$path}]: {$message}";
    }
}
