<?php

declare(strict_types=1);

namespace App\Support\Media\UrlGenerator;

use DateTimeInterface;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Support\UrlGenerator\BaseUrlGenerator;

final class CdnUrlGenerator extends BaseUrlGenerator
{
    public function getUrl(): string
    {
        $path = $this->getPathRelativeToRoot();
        $cdnUrl = $this->cdnUrlForPath($path);

        if ($cdnUrl !== null) {
            return $this->versionUrl($cdnUrl);
        }

        $disk = $this->getDisk();

        if ($this->shouldUseTemporaryUrls() && method_exists($disk, 'temporaryUrl')) {
            return $this->versionUrl(
                $disk->temporaryUrl($path, $this->temporaryExpiration(), $this->temporaryOptions())
            );
        }

        return $this->versionUrl($disk->url($path));
    }

    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        $disk = $this->getDisk();

        if ($this->shouldUseTemporaryUrls() && method_exists($disk, 'temporaryUrl')) {
            return $disk->temporaryUrl(
                $this->getPathRelativeToRoot(),
                $expiration,
                array_merge($this->temporaryOptions(), $options)
            );
        }

        return $disk->url($this->getPathRelativeToRoot());
    }

    public function getBaseMediaDirectoryUrl(): string
    {
        $cdnBase = $this->cdnBaseUrl();
        if ($cdnBase !== null) {
            return Str::finish($cdnBase, '/');
        }

        $disk = $this->getDisk();

        if ($this->shouldUseTemporaryUrls() && method_exists($disk, 'temporaryUrl')) {
            return Str::finish(
                $disk->temporaryUrl('', $this->temporaryExpiration(), $this->temporaryOptions()),
                '/'
            );
        }

        return Str::finish($disk->url('/'), '/');
    }

    public function getPath(): string
    {
        return $this->getRootOfDisk() . $this->getPathRelativeToRoot();
    }

    public function getResponsiveImagesDirectoryUrl(): string
    {
        $path = $this->pathGenerator->getPathForResponsiveImages($this->media);
        $cdnUrl = $this->cdnUrlForPath($path);

        if ($cdnUrl !== null) {
            return Str::finish($cdnUrl, '/');
        }

        $disk = $this->getDisk();

        if ($this->shouldUseTemporaryUrls() && method_exists($disk, 'temporaryUrl')) {
            return Str::finish(
                $disk->temporaryUrl($path, $this->temporaryExpiration(), $this->temporaryOptions()),
                '/'
            );
        }

        return Str::finish($disk->url($path), '/');
    }

    protected function getRootOfDisk(): string
    {
        return $this->getDisk()->path('/');
    }

    private function temporaryExpiration(): DateTimeInterface
    {
        $minutes = (int) config('media.urls.temporary_url_ttl', config('media-library.temporary_url_default_lifetime', 5));

        return now()->addMinutes(max($minutes, 1));
    }

    private function temporaryOptions(): array
    {
        $cacheControl = config('media.urls.response_cache_control');

        $options = [];
        if (is_string($cacheControl) && $cacheControl !== '') {
            $options['ResponseCacheControl'] = $cacheControl;
        }

        if (is_string($this->media->mime_type) && $this->media->mime_type !== '') {
            $options['ResponseContentType'] = $this->media->mime_type;
        }

        return $options;
    }

    private function shouldUseTemporaryUrls(): bool
    {
        return (bool) config('media.urls.use_temporary_urls', true);
    }

    private function cdnBaseUrl(): ?string
    {
        $cdn = config('media.urls.cdn');

        if (! is_string($cdn) || trim($cdn) === '') {
            return null;
        }

        return rtrim($cdn, '/');
    }

    private function cdnUrlForPath(string $path): ?string
    {
        $cdnBase = $this->cdnBaseUrl();

        if ($cdnBase === null) {
            return null;
        }

        $relativePath = ltrim($path, '/');

        return $cdnBase . '/' . $relativePath;
    }
}
