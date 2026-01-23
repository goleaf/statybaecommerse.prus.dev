<?php

declare(strict_types=1);

namespace App\Support\Filesystem;

/**
 * Centralized filesystem permissions configuration.
 */
final class FilesystemPermissions
{
    public const DEFAULT_DIRECTORY_MODE = 0755;

    public const DEFAULT_FILE_MODE = 0644;

    public const SECURE_DIRECTORY_MODE = 0750;

    public const SECURE_FILE_MODE = 0640;

    public function __construct(
        private readonly int $directoryMode = self::DEFAULT_DIRECTORY_MODE,
        private readonly int $fileMode = self::DEFAULT_FILE_MODE
    ) {}

    public function getDirectoryMode(): int
    {
        return $this->directoryMode;
    }

    public function getFileMode(): int
    {
        return $this->fileMode;
    }

    public static function secure(): self
    {
        return new self(self::SECURE_DIRECTORY_MODE, self::SECURE_FILE_MODE);
    }

    public static function default(): self
    {
        return new self;
    }
}
