<?php

declare (strict_types=1);
namespace App\Services\Images;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
/**
 * ProductImageService
 * 
 * Service class containing ProductImageService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 * @property array $backgroundColors
 */
final class ProductImageService
{
    private const IMAGE_WIDTH = 800;
    private const IMAGE_HEIGHT = 800;
    private const FONT_SIZE = 48;
    private const FONT_SIZE_SMALL = 32;
    private array $backgroundColors = [
        ['#FF6B6B', '#4ECDC4'],
        // Red to Teal
        ['#A8E6CF', '#FFD93D'],
        // Mint to Yellow
        ['#6C5CE7', '#A29BFE'],
        // Purple to Light Purple
        ['#FD79A8', '#FDCB6E'],
        // Pink to Orange
        ['#00B894', '#00CEC9'],
        // Green to Cyan
        ['#E17055', '#FDCB6E'],
        // Orange to Yellow
        ['#6C5CE7', '#74B9FF'],
        // Purple to Blue
        ['#FD79A8', '#E84393'],
        // Pink to Dark Pink
        ['#00CEC9', '#55A3FF'],
        // Cyan to Blue
        ['#FDCB6E', '#E17055'],
        // Yellow to Orange
        ['#74B9FF', '#0984E3'],
        // Light Blue to Blue
        ['#A29BFE', '#6C5CE7'],
    ];
    /**
     * Handle generateProductImage functionality with proper error handling.
     * @param Product $product
     * @return string
     */
    public function generateProductImage(Product $product): string
    {
        if (!\function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('GD extension is required to generate images.');
        }
        // Create canvas
        $image = imagecreatetruecolor(self::IMAGE_WIDTH, self::IMAGE_HEIGHT);
        if ($image === false) {
            throw new \RuntimeException('Failed to create image canvas.');
        }
        // Select random background colors
        $colorPair = $this->backgroundColors[array_rand($this->backgroundColors)];
        [$startHex, $endHex] = $colorPair;
        // Create gradient background
        $this->createGradientBackground($image, $startHex, $endHex);
        // Add product name text
        $this->addProductNameText($image, $product->name);
        // Add decorative elements
        $this->addDecorativeElements($image);
        // Save to temporary file in WebP format
        $tmpDir = sys_get_temp_dir();
        $filename = 'product_' . $product->id . '_' . uniqid('', true) . '.webp';
        $path = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        if (!function_exists('imagewebp')) {
            // Fallback to PNG if WebP not available
            $filename = 'product_' . $product->id . '_' . uniqid('', true) . '.png';
            $path = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
            if (!imagepng($image, $path, 6)) {
                imagedestroy($image);
                throw new \RuntimeException('Failed to save PNG image.');
            }
        } else if (!imagewebp($image, $path, 90)) {
            imagedestroy($image);
            throw new \RuntimeException('Failed to save WebP image.');
        }
        imagedestroy($image);
        return $path;
    }
    /**
     * Handle generateRandomProductImage functionality with proper error handling.
     * @param string $productName
     * @param int|null $productId
     * @return string
     */
    public function generateRandomProductImage(string $productName, ?int $productId = null): string
    {
        if (!\function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('GD extension is required to generate images.');
        }
        // Create canvas
        $image = imagecreatetruecolor(self::IMAGE_WIDTH, self::IMAGE_HEIGHT);
        if ($image === false) {
            throw new \RuntimeException('Failed to create image canvas.');
        }
        // Select random background colors
        $colorPair = $this->backgroundColors[array_rand($this->backgroundColors)];
        [$startHex, $endHex] = $colorPair;
        // Create gradient background
        $this->createGradientBackground($image, $startHex, $endHex);
        // Add product name text
        $this->addProductNameText($image, $productName);
        // Add decorative elements
        $this->addDecorativeElements($image);
        // Save to temporary file in WebP format
        $tmpDir = sys_get_temp_dir();
        $filename = 'product_' . ($productId ?? 'random') . '_' . uniqid('', true) . '.webp';
        $path = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        if (!function_exists('imagewebp')) {
            // Fallback to PNG if WebP not available
            $filename = 'product_' . ($productId ?? 'random') . '_' . uniqid('', true) . '.png';
            $path = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
            if (!imagepng($image, $path, 6)) {
                imagedestroy($image);
                throw new \RuntimeException('Failed to save PNG image.');
            }
        } else if (!imagewebp($image, $path, 90)) {
            imagedestroy($image);
            throw new \RuntimeException('Failed to save WebP image.');
        }
        imagedestroy($image);
        return $path;
    }
    /**
     * Handle createGradientBackground functionality with proper error handling.
     * @param mixed $image
     * @param string $startHex
     * @param string $endHex
     * @return void
     */
    private function createGradientBackground($image, string $startHex, string $endHex): void
    {
        [$sr, $sg, $sb] = $this->hexToRgb($startHex);
        [$er, $eg, $eb] = $this->hexToRgb($endHex);
        // Create diagonal gradient for more dynamic look
        for ($y = 0; $y < self::IMAGE_HEIGHT; $y++) {
            for ($x = 0; $x < self::IMAGE_WIDTH; $x++) {
                // Calculate gradient position (diagonal)
                $t = ($x + $y) / (self::IMAGE_WIDTH + self::IMAGE_HEIGHT);
                $t = min(1.0, max(0.0, $t));
                // Clamp to 0-1
                $r = (int) round($sr + ($er - $sr) * $t);
                $g = (int) round($sg + ($eg - $sg) * $t);
                $b = (int) round($sb + ($eb - $sb) * $t);
                $color = imagecolorallocate($image, $r, $g, $b);
                imagesetpixel($image, $x, $y, $color);
            }
        }
    }
    /**
     * Handle addProductNameText functionality with proper error handling.
     * @param mixed $image
     * @param string $productName
     * @return void
     */
    private function addProductNameText($image, string $productName): void
    {
        // Prepare text
        $text = Str::upper($productName);
        $words = explode(' ', $text);
        // Colors for text
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $shadow = imagecolorallocate($image, 0, 0, 0);
        // Try to use a system font, fallback to built-in
        $fontPath = $this->findSystemFont();
        if ($fontPath && function_exists('imagettftext')) {
            $this->addTTFText($image, $text, $fontPath, $white, $shadow);
        } else {
            $this->addBuiltInText($image, $words, $white, $shadow);
        }
    }
    /**
     * Handle addTTFText functionality with proper error handling.
     * @param mixed $image
     * @param string $text
     * @param string $fontPath
     * @param mixed $white
     * @param mixed $shadow
     * @return void
     */
    private function addTTFText($image, string $text, string $fontPath, $white, $shadow): void
    {
        $fontSize = self::FONT_SIZE;
        $angle = 0;
        // Calculate text dimensions
        $bbox = imagettfbbox($fontSize, $angle, $fontPath, $text);
        $textWidth = $bbox[4] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[7];
        // Center position
        $x = (self::IMAGE_WIDTH - $textWidth) / 2;
        $y = (self::IMAGE_HEIGHT + $textHeight) / 2;
        // Add shadow
        imagettftext($image, $fontSize, $angle, (int) $x + 3, (int) $y + 3, $shadow, $fontPath, $text);
        // Add main text
        imagettftext($image, $fontSize, $angle, (int) $x, (int) $y, $white, $fontPath, $text);
    }
    /**
     * Handle addBuiltInText functionality with proper error handling.
     * @param mixed $image
     * @param array $words
     * @param mixed $white
     * @param mixed $shadow
     * @return void
     */
    private function addBuiltInText($image, array $words, $white, $shadow): void
    {
        $font = 5;
        // Built-in font size
        $lineHeight = 20;
        // Calculate total text height
        $totalLines = count($words);
        $totalHeight = $totalLines * $lineHeight;
        // Starting Y position (centered)
        $startY = (self::IMAGE_HEIGHT - $totalHeight) / 2;
        foreach ($words as $index => $word) {
            $textWidth = imagefontwidth($font) * strlen($word);
            $x = (self::IMAGE_WIDTH - $textWidth) / 2;
            $y = $startY + $index * $lineHeight;
            // Add shadow
            imagestring($image, $font, (int) $x + 2, (int) $y + 2, $word, $shadow);
            // Add main text
            imagestring($image, $font, (int) $x, (int) $y, $word, $white);
        }
    }
    /**
     * Handle addDecorativeElements functionality with proper error handling.
     * @param mixed $image
     * @return void
     */
    private function addDecorativeElements($image): void
    {
        // Add subtle decorative circles
        $decorativeColor = imagecolorallocatealpha($image, 255, 255, 255, 100);
        // Random circles for decoration
        for ($i = 0; $i < 5; $i++) {
            $x = random_int(50, self::IMAGE_WIDTH - 50);
            $y = random_int(50, self::IMAGE_HEIGHT - 50);
            $radius = random_int(20, 60);
            imagefilledellipse($image, $x, $y, $radius, $radius, $decorativeColor);
        }
    }
    /**
     * Handle findSystemFont functionality with proper error handling.
     * @return string|null
     */
    private function findSystemFont(): ?string
    {
        $fontPaths = ['/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', '/usr/share/fonts/TTF/arial.ttf', '/System/Library/Fonts/Arial.ttf', '/Windows/Fonts/arial.ttf', '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf'];
        foreach ($fontPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }
    /**
     * Handle hexToRgb functionality with proper error handling.
     * @param string $hex
     * @return array
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $int = hexdec($hex);
        return [$int >> 16 & 255, $int >> 8 & 255, $int & 255];
    }
    /**
     * Handle convertToWebP functionality with proper error handling.
     * @param string $imagePath
     * @return string
     */
    public function convertToWebP(string $imagePath): string
    {
        if (!function_exists('imagewebp')) {
            throw new \RuntimeException('WebP support is not available in GD extension.');
        }
        $info = getimagesize($imagePath);
        if (!$info) {
            throw new \RuntimeException('Invalid image file.');
        }
        $image = match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
            IMAGETYPE_PNG => imagecreatefrompng($imagePath),
            IMAGETYPE_GIF => imagecreatefromgif($imagePath),
            default => throw new \RuntimeException('Unsupported image format.'),
        };
        if (!$image) {
            throw new \RuntimeException('Failed to create image from file.');
        }
        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $imagePath);
        if (!imagewebp($image, $webpPath, 85)) {
            imagedestroy($image);
            throw new \RuntimeException('Failed to convert image to WebP.');
        }
        imagedestroy($image);
        return $webpPath;
    }
    /**
     * Handle generateMultipleImages functionality with proper error handling.
     * @param Product $product
     * @param int $count
     * @return array
     */
    public function generateMultipleImages(Product $product, int $count = 3): array
    {
        $images = [];
        for ($i = 0; $i < $count; $i++) {
            try {
                $imagePath = $this->generateProductImage($product);
                $images[] = $imagePath;
            } catch (\Throwable $e) {
                Log::warning('Failed to generate image for product', ['product_id' => $product->id, 'error' => $e->getMessage()]);
            }
        }
        return $images;
    }
}