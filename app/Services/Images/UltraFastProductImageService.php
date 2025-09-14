<?php

declare(strict_types=1);

namespace App\Services\Images;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

final /**
 * UltraFastProductImageService
 * 
 * Service class containing business logic and external integrations.
 */
class UltraFastProductImageService
{
    private const IMAGE_WIDTH = 600;

    private const IMAGE_HEIGHT = 600;

    private const FONT_SIZE = 5; // Built-in font for maximum speed

    // Pre-computed color palettes for instant access
    private static array $colorPalettes = [
        [[255, 107, 107], [78, 205, 196]],   // Red to Teal
        [[168, 230, 207], [255, 217, 61]],   // Mint to Yellow
        [[108, 92, 231], [162, 155, 254]],   // Purple to Light Purple
        [[253, 121, 168], [253, 203, 110]],  // Pink to Orange
        [[0, 184, 148], [0, 206, 201]],      // Green to Cyan
        [[225, 112, 85], [253, 203, 110]],   // Orange to Yellow
        [[108, 92, 231], [116, 185, 255]],   // Purple to Blue
        [[253, 121, 168], [232, 67, 147]],   // Pink to Dark Pink
        [[0, 206, 201], [85, 163, 255]],     // Cyan to Blue
        [[253, 203, 110], [225, 112, 85]],   // Yellow to Orange
    ];

    // Static cache for reused resources
    private static array $colorCache = [];

    private static int $imageCounter = 0;

    public function generateProductImage(Product $product): string
    {
        if (! \function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('GD extension is required to generate images.');
        }

        // Create canvas with error handling
        $image = imagecreatetruecolor(self::IMAGE_WIDTH, self::IMAGE_HEIGHT);
        if ($image === false) {
            throw new \RuntimeException('Failed to create image canvas.');
        }

        try {
            // Ultra-fast background generation
            $this->createUltraFastBackground($image);

            // Minimal text rendering for maximum speed
            $this->addUltraFastText($image, $product->name);

            // Save with optimized settings
            return $this->saveUltraFastImage($image, $product->id);
        } finally {
            imagedestroy($image);
        }
    }

    public function generateMultipleImagesUltraFast(Product $product, int $count = 3): array
    {
        $images = [];

        for ($i = 0; $i < $count; $i++) {
            try {
                $imagePath = $this->generateProductImageVariant($product, $i + 1);
                if ($imagePath) {
                    $images[] = $imagePath;
                }
            } catch (\Throwable $e) {
                Log::warning('Ultra-fast image generation failed', [
                    'product_id' => $product->id,
                    'variant' => $i + 1,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $images;
    }

    public function generateProductImageVariant(Product $product, int $variant): string
    {
        if (! \function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('GD extension is required to generate images.');
        }

        $image = imagecreatetruecolor(self::IMAGE_WIDTH, self::IMAGE_HEIGHT);
        if ($image === false) {
            throw new \RuntimeException('Failed to create image canvas.');
        }

        try {
            // Use different color palette for each variant
            $this->createVariantBackground($image, $variant);

            // Add text with variant-specific styling
            $this->addVariantText($image, $product->name, $variant);

            return $this->saveUltraFastImage($image, $product->id, $variant);
        } finally {
            imagedestroy($image);
        }
    }

    private function createUltraFastBackground($image): void
    {
        // Use pre-computed palette for maximum speed
        $paletteIndex = self::$imageCounter % count(self::$colorPalettes);
        [$startRgb, $endRgb] = self::$colorPalettes[$paletteIndex];

        // Create simple gradient with minimal operations
        $this->createSimpleGradient($image, $startRgb, $endRgb);

        self::$imageCounter++;
    }

    private function createVariantBackground($image, int $variant): void
    {
        $paletteIndex = $variant % count(self::$colorPalettes);
        [$startRgb, $endRgb] = self::$colorPalettes[$paletteIndex];

        // Different gradient direction for variants
        match ($variant % 3) {
            0 => $this->createSimpleGradient($image, $startRgb, $endRgb),
            1 => $this->createHorizontalGradient($image, $startRgb, $endRgb),
            2 => $this->createDiagonalGradient($image, $startRgb, $endRgb),
        };
    }

    private function createSimpleGradient($image, array $startRgb, array $endRgb): void
    {
        [$r1, $g1, $b1] = $startRgb;
        [$r2, $g2, $b2] = $endRgb;

        // Vertical gradient with reduced steps for speed
        $steps = 10;
        $stepHeight = self::IMAGE_HEIGHT / $steps;

        for ($i = 0; $i < $steps; $i++) {
            $ratio = $i / ($steps - 1);
            $r = (int) ($r1 + ($r2 - $r1) * $ratio);
            $g = (int) ($g1 + ($g2 - $g1) * $ratio);
            $b = (int) ($b1 + ($b2 - $b1) * $ratio);

            $color = $this->getCachedColor($image, $r, $g, $b);
            $y1 = (int) ($i * $stepHeight);
            $y2 = (int) (($i + 1) * $stepHeight);

            imagefilledrectangle($image, 0, $y1, self::IMAGE_WIDTH, $y2, $color);
        }
    }

    private function createHorizontalGradient($image, array $startRgb, array $endRgb): void
    {
        [$r1, $g1, $b1] = $startRgb;
        [$r2, $g2, $b2] = $endRgb;

        $steps = 10;
        $stepWidth = self::IMAGE_WIDTH / $steps;

        for ($i = 0; $i < $steps; $i++) {
            $ratio = $i / ($steps - 1);
            $r = (int) ($r1 + ($r2 - $r1) * $ratio);
            $g = (int) ($g1 + ($g2 - $g1) * $ratio);
            $b = (int) ($b1 + ($b2 - $b1) * $ratio);

            $color = $this->getCachedColor($image, $r, $g, $b);
            $x1 = (int) ($i * $stepWidth);
            $x2 = (int) (($i + 1) * $stepWidth);

            imagefilledrectangle($image, $x1, 0, $x2, self::IMAGE_HEIGHT, $color);
        }
    }

    private function createDiagonalGradient($image, array $startRgb, array $endRgb): void
    {
        [$r1, $g1, $b1] = $startRgb;
        [$r2, $g2, $b2] = $endRgb;

        // Simplified diagonal with solid colors for speed
        $color1 = $this->getCachedColor($image, $r1, $g1, $b1);
        $color2 = $this->getCachedColor($image, $r2, $g2, $b2);

        // Fill with primary color
        imagefill($image, 0, 0, $color1);

        // Add diagonal accent
        $points = [
            0, 0,
            self::IMAGE_WIDTH, 0,
            0, self::IMAGE_HEIGHT / 2,
        ];
        imagefilledpolygon($image, $points, 3, $color2);
    }

    private function addUltraFastText($image, string $productName): void
    {
        // Use built-in fonts only for maximum speed
        $white = $this->getCachedColor($image, 255, 255, 255);
        $shadow = $this->getCachedColor($image, 0, 0, 0);

        $text = strtoupper(substr($productName, 0, 18)); // Limit length
        $font = self::FONT_SIZE;

        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);

        $x = (self::IMAGE_WIDTH - $textWidth) / 2;
        $y = (self::IMAGE_HEIGHT - $textHeight) / 2;

        // Shadow for readability
        imagestring($image, $font, (int) $x + 2, (int) $y + 2, $text, $shadow);
        // Main text
        imagestring($image, $font, (int) $x, (int) $y, $text, $white);
    }

    private function addVariantText($image, string $productName, int $variant): void
    {
        $white = $this->getCachedColor($image, 255, 255, 255);
        $shadow = $this->getCachedColor($image, 0, 0, 0);

        $text = strtoupper(substr($productName, 0, 18));
        $font = self::FONT_SIZE;

        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);

        // Different text positions for variants
        [$x, $y] = match ($variant % 3) {
            0 => [(self::IMAGE_WIDTH - $textWidth) / 2, (self::IMAGE_HEIGHT - $textHeight) / 2],
            1 => [(self::IMAGE_WIDTH - $textWidth) / 2, self::IMAGE_HEIGHT * 0.3],
            2 => [(self::IMAGE_WIDTH - $textWidth) / 2, self::IMAGE_HEIGHT * 0.7],
        };

        imagestring($image, $font, (int) $x + 2, (int) $y + 2, $text, $shadow);
        imagestring($image, $font, (int) $x, (int) $y, $text, $white);
    }

    private function saveUltraFastImage($image, int $productId, ?int $variant = null): string
    {
        $suffix = $variant ? "_{$variant}" : '';
        $filename = "product_{$productId}{$suffix}_".uniqid('', true);

        // Prefer WebP for smaller file size and faster processing
        if (function_exists('imagewebp')) {
            $filename .= '.webp';
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;

            if (! imagewebp($image, $path, 85)) { // Balanced quality/speed
                throw new \RuntimeException('Failed to save WebP image.');
            }
        } else {
            $filename .= '.png';
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;

            if (! imagepng($image, $path, 6)) { // Fast compression
                throw new \RuntimeException('Failed to save PNG image.');
            }
        }

        return $path;
    }

    private function getCachedColor($image, int $r, int $g, int $b): int
    {
        $key = "{$r},{$g},{$b}";

        if (! isset(self::$colorCache[$key])) {
            self::$colorCache[$key] = imagecolorallocate($image, $r, $g, $b);

            // Prevent memory bloat
            if (count(self::$colorCache) > 100) {
                self::$colorCache = array_slice(self::$colorCache, -50, 50, true);
            }
        }

        return self::$colorCache[$key];
    }

    public static function clearCache(): void
    {
        self::$colorCache = [];
        self::$imageCounter = 0;
    }

    public function convertToWebP(string $imagePath): string
    {
        if (! function_exists('imagewebp')) {
            throw new \RuntimeException('WebP support is not available in GD extension.');
        }

        $info = getimagesize($imagePath);
        if (! $info) {
            throw new \RuntimeException('Invalid image file.');
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
            IMAGETYPE_PNG => imagecreatefrompng($imagePath),
            IMAGETYPE_GIF => imagecreatefromgif($imagePath),
            default => throw new \RuntimeException('Unsupported image format.'),
        };

        if (! $image) {
            throw new \RuntimeException('Failed to create image from file.');
        }

        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $imagePath);

        if (! imagewebp($image, $webpPath, 85)) {
            imagedestroy($image);
            throw new \RuntimeException('Failed to convert image to WebP.');
        }

        imagedestroy($image);

        return $webpPath;
    }
}
