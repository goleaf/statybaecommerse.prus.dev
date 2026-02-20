<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Importers;

use App\Filament\Imports\ProductImporter;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Http;
use ReflectionClass;
use ReflectionMethod;

it('resolves image extension from content type and url fallback', function (): void {
    $importer = new ProductImporter(new Import, [], []);
    $method = new ReflectionMethod($importer, 'resolveImageExtension');
    $method->setAccessible(true);

    expect($method->invoke($importer, 'https://example.com/a.jpg', 'image/png'))->toBe('png')
        ->and($method->invoke($importer, 'https://example.com/a.webp', null))->toBe('webp')
        ->and($method->invoke($importer, 'https://example.com/a.unknown', null))->toBe('jpg')
        ->and($method->invoke($importer, 'https://example.com/a.jpeg?x=1', null))->toBe('jpg');
});

it('downloads image contents successfully from http source', function (): void {
    Http::fake([
        'https://example.com/image.png' => Http::response('image-bytes', 200, ['Content-Type' => 'image/png']),
    ]);

    $importer = new ProductImporter(new Import, [], []);
    $method = new ReflectionMethod($importer, 'downloadImageContents');
    $method->setAccessible(true);

    $result = $method->invoke($importer, 'https://example.com/image.png');

    expect($result)->toBeArray()
        ->and($result['body'])->toBe('image-bytes')
        ->and($result['content_type'])->toBe('image/png');
});

it('returns null when image download fails', function (): void {
    Http::fake([
        'https://example.com/missing.png' => Http::response('', 404),
    ]);

    $importer = new ProductImporter(new Import, [], []);
    $method = new ReflectionMethod($importer, 'downloadImageContents');
    $method->setAccessible(true);

    $result = $method->invoke($importer, 'https://example.com/missing.png');

    expect($result)->toBeNull();
});

it('uses a 300-second timeout for image downloads during product import', function (): void {
    $reflection = new ReflectionClass(ProductImporter::class);

    expect($reflection->getConstant('IMPORT_IMAGE_DOWNLOAD_TIMEOUT_SECONDS'))->toBe(300)
        ->and($reflection->getConstant('IMPORT_IMAGE_CONNECT_TIMEOUT_SECONDS'))->toBe(30);
});

it('resizes oversized downloaded image contents', function (): void {
    if (! function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension is not available.');
    }

    $canvas = imagecreatetruecolor(2400, 1800);
    imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 0, 0));
    ob_start();
    imagejpeg($canvas, null, 95);
    $largeImageContents = (string) ob_get_clean();
    imagedestroy($canvas);

    $importer = new ProductImporter(new Import, [], []);
    $method = new ReflectionMethod($importer, 'resizeImageContents');
    $method->setAccessible(true);

    $resizedContents = $method->invoke($importer, $largeImageContents, 'jpg');
    $resizedImage = imagecreatefromstring($resizedContents);

    expect($resizedImage)->not->toBeFalse();

    if ($resizedImage === false) {
        return;
    }

    $resizedWidth = imagesx($resizedImage);
    $resizedHeight = imagesy($resizedImage);
    imagedestroy($resizedImage);

    expect($resizedWidth)->toBeLessThanOrEqual(1600)
        ->and($resizedHeight)->toBeLessThanOrEqual(1600);
});
