<?php

declare(strict_types=1);

namespace App\Support\Uploads;

use App\Support\Storage\SecureStorage;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
            ));
    }

    /**
     * The heavy lifting now lives inside {@see SecureUpload}; this class simply wires it into Filament.
     */
}
