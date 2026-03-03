<?php

declare(strict_types=1);

namespace App\Support\Pdf;

final class LoremPdfGenerator
{
    private function __construct() {}

    public static function brochureBinary(string $title, string $subtitle, string $body): string
    {
        $lines = [
            trim($title) !== '' ? $title : 'Brochure',
            trim($subtitle) !== '' ? $subtitle : 'Download',
        ];

        foreach (self::wrapLines($body) as $line) {
            $lines[] = $line;
        }

        return self::buildPdf($lines);
    }

    public static function testBinary(string $context = 'Test PDF'): string
    {
        $safeContext = trim($context) !== '' ? $context : 'Test PDF';

        return self::brochureBinary(
            $safeContext,
            'Lorem ipsum fixture',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
        );
    }

    /**
     * @param array<int, string> $lines
     */
    private static function buildPdf(array $lines): string
    {
        $contentStream = self::buildContentStream($lines);

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            5 => sprintf("<< /Length %d >>\nstream\n%s\nendstream", strlen($contentStream), $contentStream),
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n%s\nendobj\n", $number, $body);
        }

        $xrefOffset = strlen($pdf);
        $size = count($objects) + 1;

        $pdf .= sprintf("xref\n0 %d\n", $size);
        $pdf .= "0000000000 65535 f \n";

        for ($index = 1; $index < $size; $index++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$index] ?? 0);
        }

        $pdf .= "trailer\n";
        $pdf .= sprintf("<< /Size %d /Root 1 0 R >>\n", $size);
        $pdf .= "startxref\n";
        $pdf .= $xrefOffset . "\n";
        $pdf .= '%%EOF';

        return $pdf;
    }

    /**
     * @param array<int, string> $lines
     */
    private static function buildContentStream(array $lines): string
    {
        $stream = "BT\n/F1 12 Tf\n50 760 Td\n";

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $stream .= "0 -18 Td\n";
            }

            $stream .= sprintf("(%s) Tj\n", self::escapeText($line));
        }

        $stream .= 'ET';

        return $stream;
    }

    private static function escapeText(string $text): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\(', '\)'],
            trim($text)
        );
    }

    /**
     * @return array<int, string>
     */
    private static function wrapLines(string $text, int $maxLength = 88): array
    {
        $source = trim($text);
        if ($source === '') {
            return [
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            ];
        }

        $words = preg_split('/\s+/', $source) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $word = trim((string) $word);
            if ($word === '') {
                continue;
            }

            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if (strlen($candidate) <= $maxLength) {
                $current = $candidate;

                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }

            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }
}
