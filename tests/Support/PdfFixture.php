<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Support\Pdf\LoremPdfGenerator;
use App\Support\Storage\SecureStorage;
use Illuminate\Support\Facades\Storage;

final class PdfFixture
{
    private function __construct() {}

    public static function binary(string $context = 'Test PDF'): string
    {
        return LoremPdfGenerator::testBinary($context);
    }

    public static function putOnSecureDisk(string $path, string $context = 'Test PDF'): string
    {
        $binary = self::binary($context);
        Storage::disk(SecureStorage::disk())->put($path, $binary);

        return $binary;
    }
}
