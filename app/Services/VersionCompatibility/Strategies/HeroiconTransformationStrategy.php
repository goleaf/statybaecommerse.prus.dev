<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\Strategies;

use App\Services\VersionCompatibility\Contracts\TransformationStrategyInterface;
use App\Services\VersionCompatibility\TransformationResult;

/**
 * Transforms Filament v4 Heroicon usage to v3.3 compatible string format
 */
final class HeroiconTransformationStrategy implements TransformationStrategyInterface
{
    private const HEROICON_MAPPINGS = [
        'Heroicon::OutlinedRectangleStack' => "'heroicon-o-rectangle-stack'",
        'Heroicon::OutlineHome'            => "'heroicon-o-home'",
        'Heroicon::OutlineUser'            => "'heroicon-o-user'",
        'Heroicon::OutlineCog'             => "'heroicon-o-cog'",
        'Heroicon::OutlineDocument'        => "'heroicon-o-document'",
        'Heroicon::OutlineShoppingCart'    => "'heroicon-o-shopping-cart'",
        'Heroicon::OutlineTag'             => "'heroicon-o-tag'",
        'Heroicon::OutlineCollection'      => "'heroicon-o-collection'",
        'Heroicon::OutlineChart'           => "'heroicon-o-chart-bar'",
        'Heroicon::OutlineUsers'           => "'heroicon-o-users'",
    ];

    public function transform(string $content): TransformationResult
    {
        $originalContent = $content;
        $transformations = [];

        // Remove Heroicon import
        if (str_contains($content, 'use Filament\Support\Icons\Heroicon;')) {
            $content = str_replace('use Filament\Support\Icons\Heroicon;', '', $content);
            $transformations[] = 'Removed Heroicon import';
        }

        // Replace Heroicon constants with string equivalents
        foreach (self::HEROICON_MAPPINGS as $oldUsage => $newUsage) {
            if (str_contains($content, $oldUsage)) {
                $content = str_replace($oldUsage, $newUsage, $content);
                $transformations[] = "Replaced {$oldUsage} with {$newUsage}";
            }
        }

        // Handle return statements in getNavigationIcon methods
        $pattern = '/return\s+Heroicon::\w+;/';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "return 'heroicon-o-rectangle-stack';", $content);
            $transformations[] = 'Fixed getNavigationIcon return statements';
        }

        // Clean up extra whitespace from removed imports
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

        return new TransformationResult(
            $content,
            $content !== $originalContent,
            $transformations
        );
    }

    public function getName(): string
    {
        return 'Heroicon Transformation';
    }

    public function canHandle(string $content): bool
    {
        return str_contains($content, 'Heroicon::');
    }
}
