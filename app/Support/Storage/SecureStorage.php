<?php

declare(strict_types=1);

namespace App\Support\Storage;

use DateTimeInterface;
use Illuminate\Support\Facades\URL;

final class SecureStorage
{
    private function __construct() {}

    public static function disk(): string
    {
        $disk = config('media-security.disk', 'secure-media');

        return is_string($disk) && $disk !== '' ? $disk : 'secure-media';
    }

    public static function temporarySignedUrl(string $path, ?DateTimeInterface $expiration = null, bool $download = false): string
    {
        $expiresAt = $expiration ?? now()->addMinutes((int) config('media-security.url_lifetime', 30));

        return URL::temporarySignedRoute('media.secure-download', $expiresAt, array_filter([
            'encodedPath' => self::encodePath($path),
            'download'    => $download ? '1' : null,
        ]));
    }

    public static function encodePath(string $path): string
    {
        $normalized = trim(str_replace('\\', '/', $path), '/');

        return rtrim(strtr(base64_encode($normalized), '+/', '-_'), '=');
    }

    public static function decodePath(string $encoded): ?string
    {
        $base64 = strtr($encoded, '-_', '+/');
        $padding = strlen($base64) % 4;
        if ($padding > 0) {
            $base64 .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($base64, true);
        if ($decoded === false) {
            return null;
        }

        $normalized = trim(str_replace('\\', '/', $decoded), '/');

        if ($normalized === '' || str_contains($normalized, '../') || str_starts_with($normalized, '../')) {
            return null;
        }

        return $normalized;
    }

    public static function filename(string $path): string
    {
        $basename = basename($path);

        return $basename !== '' ? $basename : 'download';
    }
}
