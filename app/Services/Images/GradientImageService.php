<?php

declare (strict_types=1);
namespace App\Services\Images;

/**
 * GradientImageService
 * 
 * Service class containing GradientImageService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class GradientImageService
{
    /**
     * Handle generateGradientPng functionality with proper error handling.
     * @param int $width
     * @param int $height
     * @param string|null $startHex
     * @param string|null $endHex
     * @return string
     */
    public function generateGradientPng(int $width = 800, int $height = 800, ?string $startHex = null, ?string $endHex = null): string
    {
        if (!\function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('GD extension is required to generate images.');
        }
        // Reduce image size in testing environment to prevent memory issues
        if (app()->environment('testing')) {
            $width = min($width, 100);
            $height = min($height, 100);
        }
        $startHex = $startHex ?: $this->randomColor();
        $endHex = $endHex ?: $this->randomColor();
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            throw new \RuntimeException('Failed to create image canvas.');
        }
        [$sr, $sg, $sb] = $this->hexToRgb($startHex);
        [$er, $eg, $eb] = $this->hexToRgb($endHex);
        // Vertical gradient for performance and consistency with square thumbs
        for ($y = 0; $y < $height; $y++) {
            $t = $height > 1 ? $y / ($height - 1) : 0.0;
            // 0..1
            $r = (int) round($sr + ($er - $sr) * $t);
            $g = (int) round($sg + ($eg - $sg) * $t);
            $b = (int) round($sb + ($eb - $sb) * $t);
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }
        $tmpDir = sys_get_temp_dir();
        $filename = 'gradient_' . uniqid('', true) . '.png';
        $path = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        if (!imagepng($image, $path, 6)) {
            imagedestroy($image);
            throw new \RuntimeException('Failed to save PNG image.');
        }
        imagedestroy($image);
        return $path;
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
     * Handle randomColor functionality with proper error handling.
     * @return string
     */
    private function randomColor(): string
    {
        // Pleasant pastel-ish range
        $r = random_int(64, 224);
        $g = random_int(64, 224);
        $b = random_int(64, 224);
        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}