<?php

declare(strict_types=1);

namespace App\Support\Uploads;

use App\Support\Storage\SecureStorage;

use function array_filter;
use function array_map;
use function array_values;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Arr;

use function is_array;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

use function str_contains;
use function str_starts_with;

final class SecureUploadHandler
{
    private function __construct() {}

    public static function configure(FileUpload $component): void
    {
        $disk = SecureStorage::disk();
        $directory = (string) config('media-security.directory', 'admin-uploads');
        $maxSizeKb = (int) config('media-security.max_size_kb', 5 * 1024);

        $component
            ->disk($disk)
            ->directory($directory)
            ->visibility('private')
            ->preserveFilenames(false)
            ->maxSize($maxSizeKb)
            ->acceptedFileTypes(SecureUpload::acceptedFileTypes())
            ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => SecureUpload::sanitizedFileName($file))
            ->saveUploadedFileUsing(fn (FileUpload $component, TemporaryUploadedFile $file): string => SecureUpload::storeUploadedFile(
                $file,
                (string) ($component->getDirectory() ?? ''),
                $component->getDiskName(),
                self::allowedMimeTypesForComponent($component),
                self::allowedExtensionsForComponent($component),
                self::maxSizeForComponent($component),
            ));
    }

    /**
     * The heavy lifting now lives inside {@see SecureUpload}; this class simply wires it into Filament.
     */

    /**
     * @return array<int, string>|null
     */
    private static function allowedMimeTypesForComponent(FileUpload $component): ?array
    {
        $accepted = self::acceptedTypesForComponent($component);

        $mimeTypes = array_values(array_filter($accepted, static function (string $value): bool {
            return str_contains($value, '/');
        }));

        return $mimeTypes !== [] ? $mimeTypes : null;
    }

    /**
     * @return array<int, string>|null
     */
    private static function allowedExtensionsForComponent(FileUpload $component): ?array
    {
        $accepted = self::acceptedTypesForComponent($component);

        $extensions = array_values(array_filter(array_map(static function (string $value): string {
            $normalized = strtolower($value);
            if (str_starts_with($normalized, '.')) {
                return ltrim($normalized, '.');
            }

            return '';
        }, $accepted)));

        return $extensions !== [] ? $extensions : null;
    }

    private static function maxSizeForComponent(FileUpload $component): ?int
    {
        $maxSize = $component->getMaxSize();

        return is_numeric($maxSize) ? (int) $maxSize : null;
    }

    /**
     * @return array<int, string>
     */
    private static function acceptedTypesForComponent(FileUpload $component): array
    {
        $accepted = $component->getAcceptedFileTypes();

        if (! is_array($accepted)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn ($value): string => trim((string) $value), Arr::flatten($accepted))));
    }
}
