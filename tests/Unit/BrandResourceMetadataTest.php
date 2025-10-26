<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Filament\Resources\BrandResource;
use App\Models\Brand;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
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
    public function test_resource_references_brand_model(): void
    {
        $this->assertSame(Brand::class, BrandResource::getModel());
    }

    /**
     * The customized query must skip the enabled and active global scopes so diagnostics can see every brand.
     */
    public function test_eloquent_query_ignores_visibility_scopes(): void
    {
        $disabledBrand = Brand::factory()->createQuietly([
            'is_enabled' => false,
            'is_active'  => false,
        ]);

        $this->assertTrue(
            BrandResource::getEloquentQuery()
                ->whereKey($disabledBrand)
                ->exists(),
            'Brands hidden by the default scopes should still appear in the Filament resource query.',
        );
    }

    /**
     * Ensure the Filament form exposes the JSON-backed social links repeater.
     */
    public function test_form_registers_social_links_repeater(): void
    {
        $schema = BrandResource::form(Schema::make());

        $hasRepeater = collect($schema->getComponents())
            ->flatMap(fn ($component) => method_exists($component, 'getComponents') ? $component->getComponents() : [])
            ->contains(fn ($component) => $component instanceof Repeater && $component->getName() === 'social_links');

        $this->assertTrue($hasRepeater, 'The brand form should surface a social_links repeater component.');
    }

    /**
     * Confirm the premium toggle stays part of the settings grid for featured placements.
     */
    public function test_form_includes_premium_toggle(): void
    {
        $schema = BrandResource::form(Schema::make());

        $hasPremiumToggle = collect($schema->getComponents())
            ->flatMap(fn ($component) => method_exists($component, 'getComponents') ? $component->getComponents() : [])
            ->flatMap(fn ($component) => method_exists($component, 'getComponents') ? $component->getComponents() : [$component])
            ->contains(fn ($component) => $component instanceof Toggle && $component->getName() === 'is_premium');

        $this->assertTrue($hasPremiumToggle, 'The settings grid must include an is_premium toggle.');
    }
}
