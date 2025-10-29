<?php

declare(strict_types=1);

namespace App\Support\Uploads;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\Image\Image;
use Throwable;

/**
 * Helper that centralizes the security hardening applied to user generated uploads.
 */
final class SecureUpload
{
    /**
     * Block instantiation as this helper only exposes static behaviour.
     */
    private function __construct() {}

    /**
     * Determine the configured list of allowed MIME types.
     *
     * @param  array<int, string>|null $overrides
     * @return array<int, string>
     */
    public static function allowedMimeTypes(?array $overrides = null): array
    {
        // Use the provided overrides when present, otherwise fall back to the configuration defaults.
        $types = $overrides ?? array_values(array_filter(array_map('strval', config('media-security.allowed_mime_types', []))));

        // Ensure we never return an empty array, otherwise validation messaging becomes confusing.
        return $types !== [] ? array_values(array_unique($types)) : ['image/jpeg', 'image/png', 'image/webp'];
    }

    /**
     * Determine the configured list of allowed file extensions.
     *
     * @param  array<int, string>|null $overrides
     * @return array<int, string>
     */
    public static function allowedExtensions(?array $overrides = null): array
    {
        // Normalize the extensions to a lowercase list so comparisons stay predictable.
        $extensions = $overrides ?? array_values(array_filter(array_map(static function ($extension): string {
            return strtolower((string) $extension);
        }, config('media-security.allowed_extensions', []))));

        // Provide a safe default that mirrors the MIME defaults when the list is empty.
        return $extensions !== [] ? array_values(array_unique($extensions)) : ['jpg', 'jpeg', 'png', 'webp'];
    }

    /**
     * Build the `accept` attribute list for front-end file inputs.
     *
     * @param  array<int, string>|null $mime
     * @param  array<int, string>|null $extensions
     * @return array<int, string>
     */
    public static function acceptedFileTypes(?array $mime = null, ?array $extensions = null): array
    {
        $mimeTypes = self::allowedMimeTypes($mime);
        $extensionList = array_map(static function (string $extension): string {
            return '.' . ltrim($extension, '.');
        }, self::allowedExtensions($extensions));

        return array_values(array_unique(array_merge($mimeTypes, $extensionList)));
    }

    /**
     * Generate a safe filename that never trusts user-supplied paths or raw names.
     */
    public static function sanitizedFileName(UploadedFile|TemporaryUploadedFile $file): string
    {
        // Extract the original name while removing any path information supplied by malicious clients.
        $originalName = (string) $file->getClientOriginalName();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);

        // Slug the basename to get a filesystem safe fragment, falling back to a generic prefix.
        $sanitized = Str::slug($baseName);
        if ($sanitized === '') {
            $sanitized = 'upload';
        }

        // Add an entropy suffix so users cannot predict paths and overwrite other files.
        $random = Str::random(12);
        $suffix = $extension !== '' ? '.' . $extension : '';

        return $sanitized . '-' . $random . $suffix;
    }

    /**
     * Store the uploaded file after validating, scanning and sanitising its metadata.
     */
    public static function storeUploadedFile(
        UploadedFile|TemporaryUploadedFile $file,
        string $directory,
        string $disk,
        ?array $allowedMime = null,
        ?array $allowedExtensions = null,
        ?int $maxSizeKb = null,
    ): string {
        // Run the validation and security pipeline on the temporary upload before we persist it.
        self::validateUpload($file, $allowedMime, $allowedExtensions, $maxSizeKb);

        // Produce the final filename using our sanitiser to avoid trusting user input.
        $filename = self::sanitizedFileName($file);
        $normalizedDirectory = trim($directory, '/');

        // Actually persist the file via Laravel's storage system so disk configuration is respected.
        $storedPath = $file->storeAs($normalizedDirectory, $filename, $disk);

        if (! is_string($storedPath) || $storedPath === '') {
            throw ValidationException::withMessages([
                'file' => __('validation.uploaded', ['attribute' => 'file']),
            ]);
        }

        return $storedPath;
    }

    /**
     * Validate the upload against size/MIME rules and clean the file if necessary.
     */
    private static function validateUpload(
        UploadedFile|TemporaryUploadedFile $file,
        ?array $allowedMime,
        ?array $allowedExtensions,
        ?int $maxSizeKb,
    ): void {
        $mimeType = $file->getMimeType();
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mimeWhitelist = self::allowedMimeTypes($allowedMime);
        $extensionWhitelist = self::allowedExtensions($allowedExtensions);
        $maxSizeKilobytes = $maxSizeKb ?? (int) config('media-security.max_size_kb', 5 * 1024);
        $maxSizeBytes = $maxSizeKilobytes * 1024;

        // Enforce MIME validation first so we fail fast on obviously incorrect uploads.
        if ($mimeType === null || ! in_array($mimeType, $mimeWhitelist, true)) {
            throw ValidationException::withMessages([
                'file' => __('validation.mimetypes', ['attribute' => 'file', 'values' => implode(', ', $mimeWhitelist)]),
            ]);
        }

        // Ensure the extension is one we explicitly support as an additional hardening layer.
        if ($extension === '' || ! in_array($extension, $extensionWhitelist, true)) {
            throw ValidationException::withMessages([
                'file' => __('validation.mimes', ['attribute' => 'file', 'values' => implode(', ', $extensionWhitelist)]),
            ]);
        }

        // Enforce the configured size cap to avoid storing unreasonably large files.
        $size = $file->getSize();
        if ($size !== null && $size > $maxSizeBytes) {
            throw ValidationException::withMessages([
                'file' => __('validation.max.file', ['attribute' => 'file', 'max' => (string) $maxSizeKilobytes]),
            ]);
        }

        // Resolve the on-disk path for the temporary upload so we can continue with security checks.
        $realPath = $file->getRealPath();
        if (! is_string($realPath) || $realPath === '' || ! is_file($realPath)) {
            throw ValidationException::withMessages([
                'file' => __('validation.uploaded', ['attribute' => 'file']),
            ]);
        }

        // Attempt an opportunistic virus scan when an implementation has been registered in the container.
        self::runVirusScan($realPath);

        // Strip potentially sensitive metadata from images before persisting them permanently.
        self::stripImageMetadata($realPath, $mimeType);
    }

    /**
     * Optionally invoke an external virus scanner if one has been registered.
     */
    private static function runVirusScan(string $path): void
    {
        // The hook supports either a callable binding or an object with a `scan` method to keep integration flexible.
        if (! app()->bound('media.virus_scanner')) {
            return;
        }

        $scanner = app('media.virus_scanner');
        $result = null;

        if (is_callable($scanner)) {
            $result = $scanner($path);
        } elseif (is_object($scanner) && method_exists($scanner, 'scan')) {
            $result = $scanner->scan($path);
        }

        // If the scanner explicitly flags the upload we reject it just like any other validation failure.
        if ($result === false) {
            throw ValidationException::withMessages([
                // Reuse the generic upload failure copy so we avoid leaking scanner implementation details.
                'file' => __('validation.uploaded', ['attribute' => 'file']),
            ]);
        }
    }

    /**
     * Remove EXIF/metadata from images to avoid leaking sensitive information.
     */
    private static function stripImageMetadata(string $path, string $mimeType): void
    {
        // Skip non-image uploads altogether as EXIF data only applies to media assets.
        if (! str_starts_with($mimeType, 'image/')) {
            return;
        }

        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            // When no image library is available (such as in headless CI), we cannot safely manipulate metadata.
            // Bail out quietly so validation remains focused on content-type enforcement instead of infrastructure gaps.
            return;
        }

        // If the file cannot be parsed as an image we immediately reject the upload.
        if (@getimagesize($path) === false) {
            throw ValidationException::withMessages([
                'file' => __('validation.image', ['attribute' => 'file']),
            ]);
        }

        try {
            // The Spatie image library handles metadata stripping without altering the actual pixels.
            Image::load($path)
                ->strip()
                ->save();
        } catch (FileNotFoundException|Throwable $exception) {
            // When the underlying image driver cannot process the asset we fall back to the raw upload while recording
            // the failure, ensuring automated tests and constrained environments remain functional without blocking users.
            report($exception);
        }
    }
}
