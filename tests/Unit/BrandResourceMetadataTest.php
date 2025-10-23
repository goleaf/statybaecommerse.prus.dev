<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Filament\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the brand resource metadata that previously required a manual artisan probe.
 */
final class BrandResourceMetadataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The resource should remain bound to the Brand model class.
     */
    public function testResourceReferencesBrandModel(): void
    {
        $this->assertSame(Brand::class, BrandResource::getModel());
    }

    /**
     * The customized query must skip the enabled and active global scopes so diagnostics can see every brand.
     */
    public function testEloquentQueryIgnoresVisibilityScopes(): void
    {
        $disabledBrand = Brand::factory()->createQuietly([
            'is_enabled' => false,
            'is_active' => false,
        ]);

        $this->assertTrue(
            BrandResource::getEloquentQuery()
                ->whereKey($disabledBrand)
                ->exists(),
            'Brands hidden by the default scopes should still appear in the Filament resource query.',
        );
    }
}
