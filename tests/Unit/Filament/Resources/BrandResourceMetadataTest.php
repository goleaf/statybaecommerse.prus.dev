<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources;

use App\Filament\Resources\BrandResource;
use App\Models\Brand;
use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use PHPUnit\Framework\TestCase;
use UnitEnum;

final class BrandResourceMetadataTest extends TestCase
{
    public function test_model_binding_matches_brand_class(): void
    {
        self::assertSame(Brand::class, BrandResource::getModel());
    }

    public function test_navigation_configuration_is_accessible(): void
    {
        $icon = BrandResource::getNavigationIcon();
        $group = BrandResource::getNavigationGroup();

        self::assertTrue(
            is_string($icon) || $icon instanceof BackedEnum || $icon instanceof Htmlable || $icon === null,
            'Navigation icon should be a string, backed enum, Htmlable instance, or null.'
        );

        self::assertTrue(
            is_string($group) || $group instanceof UnitEnum || $group === null,
            'Navigation group should be a string, enum, or null.'
        );
    }
}
