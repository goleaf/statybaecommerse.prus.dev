<?php

declare (strict_types=1);
namespace App\Services\Images;

use Illuminate\Support\Str;
/**
 * LocalImageGeneratorService
 * 
 * Service class containing LocalImageGeneratorService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class LocalImageGeneratorService
{
    private const DEFAULT_WIDTH = 800;
    private const DEFAULT_HEIGHT = 600;
    private const WEBP_QUALITY = 90;
    /**
     * Handle generateWebPImage functionality with proper error handling.
     * @param string $text
     * @param int $width
     * @param int $height
     * @param string|null $backgroundColor
     * @param string|null $textColor
     * @param string|null $filename
     * @return string
     */
    public function generateWebPImage(string $text, int $width = self::DEFAULT_WIDTH, int $height = self::DEFAULT_HEIGHT, ?string $backgroundColor = null, ?string $textColor = null, ?string $filename = null): string
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required for image generation');
        }
        // Create image resource
        $image = imagecreatetruecolor($width, $height);
        // Set background color
        $bgColor = $this->parseColor($backgroundColor ?? $this->getRandomColor());
        $background = imagecolorallocate($image, $bgColor['r'], $bgColor['g'], $bgColor['b']);
        imagefill($image, 0, 0, $background);
        // Set text color
        $txtColor = $this->parseColor($textColor ?? '#FFFFFF');
        $textColorResource = imagecolorallocate($image, $txtColor['r'], $txtColor['g'], $txtColor['b']);
        // Add text
        $this->addTextToImage($image, $text, $textColorResource, $width, $height);
        // Generate filename if not provided
        if (!$filename) {
            $filename = Str::slug($text) . '_' . time() . '.webp';
        } elseif (!str_ends_with($filename, '.webp')) {
            $filename .= '.webp';
        }
        // Save as WebP
        $tempPath = storage_path('app/temp/' . $filename);
        $this->ensureDirectoryExists(dirname($tempPath));
        $success = imagewebp($image, $tempPath, self::WEBP_QUALITY);
        imagedestroy($image);
        if (!$success) {
            throw new \RuntimeException("Failed to save WebP image: {$tempPath}");
        }
        return $tempPath;
    }
    /**
     * Handle generateProductImage functionality with proper error handling.
     * @param string $productName
     * @param string $categoryName
     * @return string
     */
    public function generateProductImage(string $productName, string $categoryName): string
    {
        $colors = $this->getCategoryColors($categoryName);
        return $this->generateWebPImage(text: $productName, width: 600, height: 600, backgroundColor: $colors['background'], textColor: $colors['text'], filename: 'product_' . Str::slug($productName));
    }
    /**
     * Handle generateCategoryImage functionality with proper error handling.
     * @param string $categoryName
     * @return string
     */
    public function generateCategoryImage(string $categoryName): string
    {
        $colors = $this->getCategoryColors($categoryName);
        return $this->generateWebPImage(text: $categoryName, width: 400, height: 300, backgroundColor: $colors['background'], textColor: $colors['text'], filename: 'category_' . Str::slug($categoryName));
    }
    /**
     * Handle generateBrandLogo functionality with proper error handling.
     * @param string $brandName
     * @return string
     */
    public function generateBrandLogo(string $brandName): string
    {
        return $this->generateWebPImage(text: $brandName, width: 300, height: 200, backgroundColor: '#FFFFFF', textColor: '#333333', filename: 'brand_logo_' . Str::slug($brandName));
    }
    /**
     * Handle generateBrandBanner functionality with proper error handling.
     * @param string $brandName
     * @return string
     */
    public function generateBrandBanner(string $brandName): string
    {
        return $this->generateWebPImage(text: $brandName, width: 1200, height: 400, backgroundColor: $this->getRandomGradientColor(), textColor: '#FFFFFF', filename: 'brand_banner_' . Str::slug($brandName));
    }
    /**
     * Handle generateCollectionImage functionality with proper error handling.
     * @param string $collectionName
     * @return string
     */
    public function generateCollectionImage(string $collectionName): string
    {
        return $this->generateWebPImage(text: $collectionName, width: 800, height: 500, backgroundColor: $this->getRandomPastelColor(), textColor: '#333333', filename: 'collection_' . Str::slug($collectionName));
    }
    /**
     * Handle convertToWebP functionality with proper error handling.
     * @param string $sourcePath
     * @param string|null $outputPath
     * @return string
     */
    public function convertToWebP(string $sourcePath, ?string $outputPath = null): string
    {
        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException("Source file does not exist: {$sourcePath}");
        }
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            throw new \InvalidArgumentException("Invalid image file: {$sourcePath}");
        }
        $mimeType = $imageInfo['mime'];
        // Create image resource based on type
        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => throw new \InvalidArgumentException("Unsupported image type: {$mimeType}"),
        };
        if (!$image) {
            throw new \RuntimeException("Failed to create image resource from: {$sourcePath}");
        }
        // Generate output path if not provided
        if (!$outputPath) {
            $pathInfo = pathinfo($sourcePath);
            $outputPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
        }
        $this->ensureDirectoryExists(dirname($outputPath));
        // Save as WebP
        $success = imagewebp($image, $outputPath, self::WEBP_QUALITY);
        imagedestroy($image);
        if (!$success) {
            throw new \RuntimeException("Failed to save WebP image: {$outputPath}");
        }
        return $outputPath;
    }
    /**
     * Handle addTextToImage functionality with proper error handling.
     * @param mixed $image
     * @param string $text
     * @param mixed $textColor
     * @param int $width
     * @param int $height
     * @return void
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
     * @param string $color
     * @return array
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
     * @param string $categoryName
     * @return array
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
     * @return string
     */
    private function getRandomColor(): string
    {
        $colors = ['#FF6B35', '#004E89', '#FFD23F', '#7209B7', '#2E86AB', '#A23B72', '#F18F01', '#C73E1D', '#3A86FF', '#06FFA5'];
        return $colors[array_rand($colors)];
    }
    /**
     * Handle getRandomPastelColor functionality with proper error handling.
     * @return string
     */
    private function getRandomPastelColor(): string
    {
        $colors = ['#FFB3BA', '#FFDFBA', '#FFFFBA', '#BAFFC9', '#BAE1FF', '#E1BAFF', '#FFBAE1', '#C9FFBA', '#BAFFE1', '#E1FFBA'];
        return $colors[array_rand($colors)];
    }
    /**
     * Handle getRandomGradientColor functionality with proper error handling.
     * @return string
     */
    private function getRandomGradientColor(): string
    {
        $colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#43e97b', '#fa709a', '#fee140', '#a8edea', '#d299c2'];
        return $colors[array_rand($colors)];
    }
    /**
     * Handle ensureDirectoryExists functionality with proper error handling.
     * @param string $directory
     * @return void
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}