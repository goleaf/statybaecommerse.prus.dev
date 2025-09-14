<?php

declare(strict_types=1);

namespace App\Services\Images;

final /**
 * GradientImageService
 * 
 * Service class containing business logic and external integrations.
 */
class GradientImageService
{
    public function generateGradientPng(int $width = 800, int $height = 800, ?string $startHex = null, ?string $endHex = null): string
    {
        if (! \function_exists('imagecreatetruecolor')) {
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
            $t = $height > 1 ? $y / ($height - 1) : 0.0;  // 0..1
            $r = (int) round($sr + ($er - $sr) * $t);
            $g = (int) round($sg + ($eg - $sg) * $t);
            $b = (int) round($sb + ($eb - $sb) * $t);
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }

        $tmpDir = sys_get_temp_dir();
        $filename = 'gradient_'.uniqid('', true).'.png';
        $path = rtrim($tmpDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename;

        if (! imagepng($image, $path, 6)) {
            imagedestroy($image);
            throw new \RuntimeException('Failed to save PNG image.');
        }

        imagedestroy($image);

        return $path;
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $int = hexdec($hex);

        return [($int >> 16) & 255, ($int >> 8) & 255, $int & 255];
    }

    private function randomColor(): string
    {
        // Pleasant pastel-ish range
        $r = random_int(64, 224);
        $g = random_int(64, 224);
        $b = random_int(64, 224);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
