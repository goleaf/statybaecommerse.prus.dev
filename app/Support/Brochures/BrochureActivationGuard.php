<?php

declare(strict_types=1);

namespace App\Support\Brochures;

use Illuminate\Validation\ValidationException;

final class BrochureActivationGuard
{
    /**
     * @param array<string, mixed> $data
     *
     * @throws ValidationException
     */
    public static function ensureActiveBrochureHasActiveFile(array $data, ?string $errorPrefix = null): void
    {
        if (! self::isActive($data['is_active'] ?? null)) {
            return;
        }

        $files = self::normalizeFiles($data['files'] ?? null);
        $activeWithPath = collect($files)->contains(static function (array $file): bool {
            $path = trim((string) ($file['file_path'] ?? ''));
            $isActive = self::isActive($file['is_active'] ?? true);

            return $isActive && $path !== '';
        });

        if ($activeWithPath) {
            return;
        }

        throw ValidationException::withMessages([
            self::errorKey($errorPrefix, 'files') => __('admin.brochures.requires_active_file'),
        ]);
    }

    private static function isActive(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (! is_string($value)) {
            return false;
        }

        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
    }

    private static function errorKey(?string $prefix, string $field): string
    {
        $normalizedPrefix = trim((string) $prefix);

        if ($normalizedPrefix === '') {
            return $field;
        }

        return rtrim($normalizedPrefix, '.') . '.' . ltrim($field, '.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeFiles(mixed $files): array
    {
        if (! is_array($files)) {
            return [];
        }

        return collect($files)
            ->filter(static fn (mixed $file): bool => is_array($file))
            ->map(static fn (array $file): array => $file)
            ->values()
            ->all();
    }
}
