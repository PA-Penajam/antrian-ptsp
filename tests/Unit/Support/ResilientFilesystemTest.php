<?php

use App\Support\ResilientFilesystem;
use Illuminate\Support\Str;

test('it retries replacing an existing file when the first rename fails', function () {
    $directory = storage_path('framework/testing/filesystem');

    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $path = $directory.'/'.Str::uuid().'.txt';

    file_put_contents($path, 'old');

    $filesystem = new class extends ResilientFilesystem
    {
        public int $renameAttempts = 0;

        public int $prepareCalls = 0;

        protected function shouldRetryReplace(): bool
        {
            return true;
        }

        protected function attemptRename(string $tempPath, string $path): bool
        {
            $this->renameAttempts++;

            if ($this->renameAttempts === 1) {
                return false;
            }

            return parent::attemptRename($tempPath, $path);
        }

        protected function prepareTargetForRetry(string $path): void
        {
            $this->prepareCalls++;

            parent::prepareTargetForRetry($path);
        }
    };

    $filesystem->replace($path, 'new');

    expect(file_get_contents($path))->toBe('new')
        ->and($filesystem->renameAttempts)->toBe(2)
        ->and($filesystem->prepareCalls)->toBe(1);

    @unlink($path);
});

test('it throws a runtime exception when all replacement strategies fail', function () {
    $directory = storage_path('framework/testing/filesystem');

    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $path = $directory.'/'.Str::uuid().'.txt';

    $filesystem = new class extends ResilientFilesystem
    {
        protected function shouldRetryReplace(): bool
        {
            return false;
        }

        protected function attemptRename(string $tempPath, string $path): bool
        {
            return false;
        }

        protected function attemptCopy(string $tempPath, string $path): bool
        {
            return false;
        }
    };

    expect(fn () => $filesystem->replace($path, 'new'))
        ->toThrow(RuntimeException::class, "Unable to replace [{$path}]");
});
