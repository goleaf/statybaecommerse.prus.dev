<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\GenerateMediaVariantsJob;
use App\Support\Storage\SecureStorage;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

final class MediaService
{
    /**
     * Default responsive variants.
     *
     * @var array<string, array<string, int>>
     */
    public const DEFAULT_VARIANTS = [
        'thumb'  => ['width' => 180, 'height' => 180],
        'medium' => ['width' => 720, 'height' => 720],
        'large'  => ['width' => 1440, 'height' => 1440],
    ];

    public function __construct(
        private readonly Dispatcher $dispatcher
    ) {}

    /**
     * Store an uploaded file and queue responsive variants.
     */
    public function upload(HasMedia $model, UploadedFile $file, string $collection = 'images', ?array $variants = null): Media
    {
        $this->assertValidFile($file);

        $media = $model->addMedia($file)
            ->sanitizingFileName(fn (string $fileName) => $this->sanitizeFileName($fileName))
            ->usingName($this->deriveMediaName($file))
            ->withCustomProperties(['variants' => []])
            ->toMediaCollection($collection);

        $media->refresh();
        $this->storeOriginalMetadata($media);

        $this->dispatcher->dispatch(
            new GenerateMediaVariantsJob(
                $media->getKey(),
                $variants ?? config('media.variants', self::DEFAULT_VARIANTS)
            )
        );

        return $media;
    }

    /**
     * Generate responsive variants synchronously.
     *
     * @return array<string, array<string, mixed>>
     */
    public function processVariants(Media $media, ?array $variants = null): array
    {
        $variants = $variants ?? config('media.variants', self::DEFAULT_VARIANTS);

        if (! $this->canProcessImage($media)) {
            return [];
        }

        $disk = Storage::disk($media->disk);
        $variantDirectory = $this->variantDirectory($media);
        $disk->makeDirectory($variantDirectory);

        $metadata = [];
        $originalPath = $media->getPath();

        foreach ($variants as $name => $dimensions) {
            $relativePath = $variantDirectory . '/' . $this->variantFileName($media, (string) $name);
            $absolutePath = $this->absolutePath($disk, $relativePath);

            try {
                Image::load($originalPath)
                    ->fit(Fit::Contain, (int) ($dimensions['width'] ?? 0), (int) ($dimensions['height'] ?? 0))
                    ->format('webp')
                    ->save($absolutePath);
            } catch (Throwable $exception) {
                Log::warning('Media variant generation failed', [
                    'media_id' => $media->getKey(),
                    'variant'  => $name,
                    'message'  => $exception->getMessage(),
                ]);

                continue;
            }

            $metadata[$name] = $this->variantMetadata($disk, $relativePath);
        }

        if ($metadata !== []) {
            $media->setCustomProperty('variants', $metadata);
            $media->save();
        }

        return $metadata;
    }

    /**
     * Remove a media record and all responsive variants.
     */
    public function deleteMedia(Media $media): void
    {
        $disk = Storage::disk($media->disk);
        $variants = $media->getCustomProperty('variants', []);

        foreach ($variants as $details) {
            $path = Arr::get($details, 'path');
            if (is_string($path) && $disk->exists($path)) {
                $disk->delete($path);
            }
        }

        $originalPath = $media->getPathRelativeToRoot();
        if ($disk->exists($originalPath)) {
            $disk->delete($originalPath);
        }

        $media->delete();
    }

    /**
     * Determine the storage path for a specific variant.
     */
    public function variantPath(Media $media, string $variant): ?string
    {
        $variants = $media->getCustomProperty('variants', []);
        $path = Arr::get($variants, $variant . '.path');

        return is_string($path) ? $path : null;
    }

    /**
     * Capture metadata about the original upload.
     */
    private function storeOriginalMetadata(Media $media): void
    {
        try {
            $path = $media->getPathRelativeToRoot();
            $disk = Storage::disk($media->disk);
            if (! $disk->exists($path)) {
                return;
            }

            $absolutePath = $this->absolutePath($disk, $path);
            $dimensions = @getimagesize($absolutePath) ?: null;

            $metadata = [
                'path'      => $path,
                'url'       => SecureStorage::temporarySignedUrl($path),
                'width'     => $dimensions[0] ?? null,
                'height'    => $dimensions[1] ?? null,
                'size'      => $disk->size($path),
                'format'    => pathinfo($media->file_name, PATHINFO_EXTENSION),
                'mime_type' => $media->mime_type,
            ];

            $media->setCustomProperty('original', $metadata);
            $media->save();
        } catch (FileNotFoundException) {
            Log::warning('Unable to store media metadata; file missing', [
                'media_id' => $media->getKey(),
            ]);
        }
    }

    private function assertValidFile(UploadedFile $file): void
    {
        $allowed = config('media.allowed_mime_types', ['image/jpeg', 'image/png', 'image/webp']);
        $mimeType = $file->getMimeType();

        if ($mimeType === null || ! in_array($mimeType, $allowed, true)) {
            throw new InvalidArgumentException('Unsupported file type for media upload.');
        }

        $maxSize = (int) config('media.max_upload_size', 10 * 1024 * 1024);
        if ($file->getSize() !== null && $file->getSize() > $maxSize) {
            throw new InvalidArgumentException('Uploaded file exceeds the configured size limit.');
        }
    }

    private function sanitizeFileName(string $fileName): string
    {
        $extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
        $name = pathinfo($fileName, PATHINFO_FILENAME);
        $sanitized = Str::slug($name);

        if ($sanitized === '') {
            $sanitized = 'media';
        }

        return $sanitized . '-' . Str::random(8) . ($extension !== '' ? '.' . $extension : '');
    }

    private function deriveMediaName(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = trim(preg_replace('/[^\pL\pN\s-]+/u', '', (string) $name));

        return $name !== '' ? $name : 'Media Asset';
    }

    private function variantDirectory(Media $media): string
    {
        $relative = trim(dirname($media->getPathRelativeToRoot()), '/');
        $directory = $relative === '.' || $relative === '' ? '' : $relative . '/';

        return $directory . 'variants';
    }

    private function variantFileName(Media $media, string $variant): string
    {
        $basename = pathinfo($media->file_name, PATHINFO_FILENAME);

        return $basename . '-' . $variant . '.webp';
    }

    /**
     * @return array<string, mixed>
     */
    private function variantMetadata(Filesystem $disk, string $relativePath): array
    {
        $absolutePath = $this->absolutePath($disk, $relativePath);
        $dimensions = @getimagesize($absolutePath) ?: null;

        return [
            'path'      => $relativePath,
            'url'       => SecureStorage::temporarySignedUrl($relativePath),
            'width'     => $dimensions[0] ?? null,
            'height'    => $dimensions[1] ?? null,
            'size'      => $disk->size($relativePath),
            'format'    => 'webp',
            'mime_type' => 'image/webp',
        ];
    }

    private function absolutePath(Filesystem $disk, string $relativePath): string
    {
        $adapter = $disk->getAdapter();
        if (method_exists($adapter, 'getPathPrefix')) {
            /** @var string $prefix */
            $prefix = $adapter->getPathPrefix();

            return $prefix . ltrim($relativePath, '/');
        }

        if (method_exists($disk, 'path')) {
            return (string) $disk->path($relativePath);
        }

        throw new InvalidArgumentException('Unable to resolve media storage path for variant generation.');
    }

    private function canProcessImage(Media $media): bool
    {
        $path = $media->getPathRelativeToRoot();

        return Storage::disk($media->disk)->exists($path);
    }
}
