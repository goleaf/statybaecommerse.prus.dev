<?php

declare(strict_types=1);

namespace App\Services\Images;

use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * LocalImageGeneratorService
 *
 * Service class containing LocalImageGeneratorService business logic, external integrations, and complex operations with proper
 * error handling and logging.
 */
final class LocalImageGeneratorService
{
    private const DEFAULT_WIDTH = 800;

    private const DEFAULT_HEIGHT = 600;

    private const WEBP_QUALITY = 90;

    /**
     * Handle generateWebPImage functionality with proper error handling.
     */
    public function generateWebPImage(string $text, int $width = self::DEFAULT_WIDTH, int $height = self::DEFAULT_HEIGHT, ?string $backgroundColor = null, ?string $textColor = null, ?string $filename = null): string
    {
        // Reuse the generic generator so ImageMagick support flows through automatically.
        if (! $filename) {
            $filename = Str::slug($text) . '_' . time() . '.webp';
        } elseif (! str_ends_with($filename, '.webp')) {
            $filename .= '.webp';
        }

        $tempPath = storage_path('app/temp/' . $filename);

        $this->generatePlaceholderImageFile(
            text: $text,
            width: $width,
            height: $height,
            targetPath: $tempPath,
            backgroundColor: $backgroundColor,
            textColor: $textColor,
        );

        return $tempPath;
    }

    /**
     * Handle generateProductImage functionality with proper error handling.
     */
    public function generateProductImage(string $productName, string $categoryName): string
    {
        $colors = $this->getCategoryColors($categoryName);

        return $this->generateWebPImage(text: $productName, width: 600, height: 600, backgroundColor: $colors['background'], textColor: $colors['text'], filename: 'product_' . Str::slug($productName));
    }

    /**
     * Handle generateCategoryImage functionality with proper error handling.
     */
    public function generateCategoryImage(string $categoryName): string
    {
        $colors = $this->getCategoryColors($categoryName);

        return $this->generateWebPImage(text: $categoryName, width: 400, height: 300, backgroundColor: $colors['background'], textColor: $colors['text'], filename: 'category_' . Str::slug($categoryName));
    }

    /**
     * Handle generateBrandLogo functionality with proper error handling.
     */
    public function generateBrandLogo(string $brandName): string
    {
        return $this->generateWebPImage(text: $brandName, width: 300, height: 200, backgroundColor: '#FFFFFF', textColor: '#333333', filename: 'brand_logo_' . Str::slug($brandName));
    }

    /**
     * Handle generateBrandBanner functionality with proper error handling.
     */
    public function generateBrandBanner(string $brandName): string
    {
        return $this->generateWebPImage(text: $brandName, width: 1200, height: 400, backgroundColor: $this->getRandomGradientColor(), textColor: '#FFFFFF', filename: 'brand_banner_' . Str::slug($brandName));
    }

    /**
     * Handle generateCollectionImage functionality with proper error handling.
     */
    public function generateCollectionImage(string $collectionName): string
    {
        return $this->generateWebPImage(text: $collectionName, width: 800, height: 500, backgroundColor: $this->getRandomPastelColor(), textColor: '#333333', filename: 'collection_' . Str::slug($collectionName));
    }

    /**
     * Handle convertToWebP functionality with proper error handling.
     */
    public function convertToWebP(string $sourcePath, ?string $outputPath = null): string
    {
        if (! file_exists($sourcePath)) {
            throw new InvalidArgumentException("Source file does not exist: {$sourcePath}");
        }
        $imageInfo = getimagesize($sourcePath);
        if (! $imageInfo) {
            throw new InvalidArgumentException("Invalid image file: {$sourcePath}");
        }
        $mimeType = $imageInfo['mime'];
        // Create image resource based on type
        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png'  => imagecreatefrompng($sourcePath),
            'image/gif'  => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default      => throw new InvalidArgumentException("Unsupported image type: {$mimeType}"),
        };
        if (! $image) {
            throw new RuntimeException("Failed to create image resource from: {$sourcePath}");
        }
        // Generate output path if not provided
        if (! $outputPath) {
            $pathInfo = pathinfo($sourcePath);
            $outputPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
        }
        $this->ensureDirectoryExists(dirname($outputPath));
        // Save as WebP
        $success = imagewebp($image, $outputPath, self::WEBP_QUALITY);
        imagedestroy($image);
        if (! $success) {
            throw new RuntimeException("Failed to save WebP image: {$outputPath}");
        }

        return $outputPath;
    }

    /**
     * Generate an image at an arbitrary path, preferring ImageMagick when available.
     */
    public function generatePlaceholderImageFile(string $text, int $width, int $height, string $targetPath, ?string $backgroundColor = null, ?string $textColor = null): void
    {
        if ($text === '') {
            throw new InvalidArgumentException('Placeholder text cannot be empty.');
        }

        $format = strtolower((string) pathinfo($targetPath, PATHINFO_EXTENSION));
        if ($format === '') {
            throw new InvalidArgumentException('Target path must include a file extension.');
        }

        $bgColor = $this->parseColor($backgroundColor ?? $this->getRandomColor());
        $txtColor = $this->parseColor($textColor ?? '#FFFFFF');

        $this->ensureDirectoryExists(dirname($targetPath));

        if ($this->createImageUsingImagick($text, $width, $height, $targetPath, $format, $bgColor, $txtColor)) {
            return;
        }

        if ($this->createImageUsingGd($text, $width, $height, $targetPath, $format, $bgColor, $txtColor)) {
            return;
        }

        $this->persistPixelFallback($targetPath);
    }

    /**
     * Handle addTextToImage functionality with proper error handling.
     *
     * @param mixed $image
     * @param mixed $textColor
     */
    private function addTextToImage($image, string $text, $textColor, int $width, int $height): void
    {
        // Use built-in font for better compatibility
        $font = 5;
        // Largest built-in font
        // Word wrap for long text
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';
        foreach ($words as $word) {
            $testLine = $currentLine . ($currentLine ? ' ' : '') . $word;
            $textWidth = imagefontwidth($font) * strlen($testLine);
            if ($textWidth > $width * 0.8) {
                if ($currentLine) {
                    $lines[] = $currentLine;
                    $currentLine = $word;
                } else {
                    $lines[] = $word;
                }
            } else {
                $currentLine = $testLine;
            }
        }
        if ($currentLine) {
            $lines[] = $currentLine;
        }
        // Calculate total text height
        $lineHeight = imagefontheight($font) + 5;
        $totalHeight = count($lines) * $lineHeight;
        $startY = ($height - $totalHeight) / 2;
        // Draw each line
        foreach ($lines as $i => $line) {
            $textWidth = imagefontwidth($font) * strlen($line);
            $x = ($width - $textWidth) / 2;
            $y = $startY + $i * $lineHeight;
            imagestring($image, $font, (int) $x, (int) $y, $line, $textColor);
        }
    }

    /**
     * Handle parseColor functionality with proper error handling.
     */
    private function parseColor(string $color): array
    {
        // Remove # if present
        $color = ltrim($color, '#');
        // Convert to RGB
        if (strlen($color) === 6) {
            return ['r' => hexdec(substr($color, 0, 2)), 'g' => hexdec(substr($color, 2, 2)), 'b' => hexdec(substr($color, 4, 2))];
        }

        // Default to white if invalid
        return ['r' => 255, 'g' => 255, 'b' => 255];
    }

    /**
     * Handle getCategoryColors functionality with proper error handling.
     */
    private function getCategoryColors(string $categoryName): array
    {
        $categoryColors = ['tools' => ['background' => '#FF6B35', 'text' => '#FFFFFF'], 'hardware' => ['background' => '#004E89', 'text' => '#FFFFFF'], 'safety' => ['background' => '#FFD23F', 'text' => '#333333'], 'electrical' => ['background' => '#7209B7', 'text' => '#FFFFFF'], 'plumbing' => ['background' => '#2E86AB', 'text' => '#FFFFFF'], 'garden' => ['background' => '#A23B72', 'text' => '#FFFFFF'], 'automotive' => ['background' => '#F18F01', 'text' => '#FFFFFF'], 'construction' => ['background' => '#C73E1D', 'text' => '#FFFFFF']];
        $key = strtolower($categoryName);
        foreach ($categoryColors as $category => $colors) {
            if (str_contains($key, $category)) {
                return $colors;
            }
        }

        return ['background' => $this->getRandomColor(), 'text' => '#FFFFFF'];
    }

    /**
     * Handle getRandomColor functionality with proper error handling.
     */
    private function getRandomColor(): string
    {
        $colors = ['#FF6B35', '#004E89', '#FFD23F', '#7209B7', '#2E86AB', '#A23B72', '#F18F01', '#C73E1D', '#3A86FF', '#06FFA5'];

        return $colors[array_rand($colors)];
    }

    /**
     * Handle getRandomPastelColor functionality with proper error handling.
     */
    private function getRandomPastelColor(): string
    {
        $colors = ['#FFB3BA', '#FFDFBA', '#FFFFBA', '#BAFFC9', '#BAE1FF', '#E1BAFF', '#FFBAE1', '#C9FFBA', '#BAFFE1', '#E1FFBA'];

        return $colors[array_rand($colors)];
    }

    /**
     * Handle getRandomGradientColor functionality with proper error handling.
     */
    private function getRandomGradientColor(): string
    {
        $colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#43e97b', '#fa709a', '#fee140', '#a8edea', '#d299c2'];

        return $colors[array_rand($colors)];
    }

    /**
     * Attempt to create an image using ImageMagick if the extension is available.
     *
     * @param array{r:int,g:int,b:int} $backgroundColor
     * @param array{r:int,g:int,b:int} $textColor
     */
    private function createImageUsingImagick(string $text, int $width, int $height, string $targetPath, string $format, array $backgroundColor, array $textColor): bool
    {
        if (! class_exists(\Imagick::class)) {
            return false;
        }

        try {
            $image = new \Imagick();
            $image->newImage($width, $height, new \ImagickPixel($this->toRgbString($backgroundColor)));
            $image->setImageFormat($format === 'jpg' ? 'jpeg' : $format);

            $draw = new \ImagickDraw();
            $draw->setFillColor(new \ImagickPixel($this->toRgbString($textColor)));
            $draw->setTextAlignment(\Imagick::ALIGN_CENTER);
            $draw->setFontSize((float) max(14, min($width, $height) / 6));

            $lines = $this->wrapTextForImagick($draw, $text, (float) ($width * 0.8));
            $lineHeight = $draw->getFontSize() * 1.2;
            $startY = (($height - (count($lines) * $lineHeight)) / 2.0) + $draw->getFontSize();

            foreach ($lines as $index => $line) {
                $image->annotateImage($draw, $width / 2.0, $startY + ($index * $lineHeight), 0, $line);
            }

            $image->writeImage($targetPath);
            $image->clear();
            $image->destroy();
            $draw->destroy();

            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * Attempt to create an image using GD as a fallback when ImageMagick is unavailable.
     *
     * @param array{r:int,g:int,b:int} $backgroundColor
     * @param array{r:int,g:int,b:int} $textColor
     */
    private function createImageUsingGd(string $text, int $width, int $height, string $targetPath, string $format, array $backgroundColor, array $textColor): bool
    {
        if (! function_exists('imagecreatetruecolor')) {
            return false;
        }

        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            return false;
        }

        $background = imagecolorallocate($image, $backgroundColor['r'], $backgroundColor['g'], $backgroundColor['b']);
        if ($background !== false) {
            imagefill($image, 0, 0, $background);
        }

        $textColorResource = imagecolorallocate($image, $textColor['r'], $textColor['g'], $textColor['b']);
        if ($textColorResource !== false) {
            $this->addTextToImage($image, $text, $textColorResource, $width, $height);
        }

        $writeResult = match ($format) {
            'jpg', 'jpeg' => imagejpeg($image, $targetPath, 85),
            'png'         => imagepng($image, $targetPath, 6),
            'webp'        => function_exists('imagewebp') ? imagewebp($image, $targetPath, self::WEBP_QUALITY) : false,
            default       => false,
        };

        imagedestroy($image);

        return $writeResult === true;
    }

    /**
     * Provide a deterministic one-pixel placeholder when all generators fail.
     */
    private function persistPixelFallback(string $targetPath): void
    {
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO2aXhQAAAAASUVORK5CYII=');
        file_put_contents($targetPath, $pixel !== false ? $pixel : '');
    }

    /**
     * Format a colour array into an RGB string understood by Imagick.
     *
     * @param array{r:int,g:int,b:int} $color
     */
    private function toRgbString(array $color): string
    {
        return sprintf('rgb(%d,%d,%d)', $color['r'], $color['g'], $color['b']);
    }

    /**
     * Wrap text into multiple lines based on the configured font size and available width.
     *
     * @return list<string>
     */
    private function wrapTextForImagick(object $draw, string $text, float $maxWidth): array
    {
        $fontSize = max(1.0, $draw->getFontSize());
        $approximateCharWidth = $fontSize * 0.6;
        $wrapLength = max(10, (int) floor($maxWidth / $approximateCharWidth));

        $wrapped = wordwrap(trim($text), $wrapLength, "\n", true);

        return $wrapped === '' ? [''] : explode("\n", $wrapped);
    }

    /**
     * Handle ensureDirectoryExists functionality with proper error handling.
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}
