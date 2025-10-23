<?php

declare(strict_types=1);

namespace App\Support\Uploads;

use App\Support\Storage\SecureStorage;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\Image\Image;
use Throwable;

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
            ->acceptedFileTypes(self::acceptedFileTypes())
            ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => self::sanitizedFileName($file))
            ->saveUploadedFileUsing(fn (FileUpload $component, TemporaryUploadedFile $file): string => self::storeUploadedFile($file, (string) ($component->getDirectory() ?? ''), $component->getDiskName()));
    }

    /**
     * @return array<int, string>
     */
    private static function allowedMimeTypes(): array
    {
        $types = array_values(array_filter(array_map('strval', config('media-security.allowed_mime_types', []))));

        return $types !== [] ? $types : ['image/jpeg', 'image/png', 'image/webp'];
    }

    /**
     * @return array<int, string>
     */
    private static function allowedExtensions(): array
    {
        $extensions = array_values(array_filter(array_map('strval', config('media-security.allowed_extensions', []))));

        return $extensions !== [] ? $extensions : ['jpg', 'jpeg', 'png', 'webp'];
    }

    /**
     * @return array<int, string>
     */
    private static function acceptedFileTypes(): array
    {
        $types = array_merge(
            self::allowedMimeTypes(),
            array_map(
                static fn (string $extension): string => '.' . ltrim($extension, '.'),
                self::allowedExtensions(),
            ),
        );

        return array_values(array_unique($types));
    }

    private static function storeUploadedFile(TemporaryUploadedFile $file, string $directory, string $disk): string
    {
        self::assertAllowed($file);
        self::sanitizeImage($file);

        $filename = self::sanitizedFileName($file);
        $path = $file->storeAs($directory, $filename, $disk);

        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages([
                'file' => __('validation.uploaded', ['attribute' => 'file']),
            ]);
        }

        return $path;
    }

    private static function assertAllowed(TemporaryUploadedFile $file): void
    {
        $mimeType = $file->getMimeType();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $allowedMime = self::allowedMimeTypes();
        $allowedExtensions = self::allowedExtensions();

        if ($mimeType === null || ! in_array($mimeType, $allowedMime, true)) {
            throw ValidationException::withMessages([
                'file' => __('validation.mimetypes', ['attribute' => 'file', 'values' => implode(', ', $allowedMime)]),
            ]);
        }

        if ($extension === '' || ! in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'file' => __('validation.mimes', ['attribute' => 'file', 'values' => implode(', ', $allowedExtensions)]),
            ]);
        }

        $maxSizeKb = (int) config('media-security.max_size_kb', 5 * 1024);
        $maxSizeBytes = $maxSizeKb * 1024;
        if ($file->getSize() !== null && $file->getSize() > $maxSizeBytes) {
            throw ValidationException::withMessages([
                'file' => __('validation.max.file', ['attribute' => 'file', 'max' => (string) $maxSizeKb]),
            ]);
        }
    }

    private static function sanitizeImage(TemporaryUploadedFile $file): void
    {
        $mimeType = $file->getMimeType();
        if ($mimeType === null || ! str_starts_with($mimeType, 'image/')) {
            return;
        }

        $realPath = $file->getRealPath();
        if (! is_string($realPath) || $realPath === '' || ! file_exists($realPath)) {
            return;
        }

        if (@getimagesize($realPath) === false) {
            throw ValidationException::withMessages([
                'file' => __('validation.image', ['attribute' => 'file']),
            ]);
        }

        try {
            Image::load($realPath)
                ->strip()
                ->save();
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'file' => __('validation.image', ['attribute' => 'file']),
            ]);
        }
    }

    private static function sanitizedFileName(TemporaryUploadedFile $file): string
    {
        $original = (string) $file->getClientOriginalName();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $name = pathinfo($original, PATHINFO_FILENAME);
        $sanitized = Str::slug($name);

        if ($sanitized === '') {
            $sanitized = 'upload';
        }

        $random = Str::random(12);
        $suffix = $extension !== '' ? '.' . $extension : '';

        return $sanitized . '-' . $random . $suffix;
    }
}
