<?php

declare(strict_types=1);

use App\Filament\Resources\BrandResource;
use App\Filament\Resources\BrandResource\RelationManagers\DiscountsRelationManager;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('hides the brand discounts relation manager when the pivot table is missing', function (): void {
    Schema::dropIfExists('discount_brands');

    $relations = BrandResource::getRelations();

    expect($relations)->not->toContain(DiscountsRelationManager::class);
});

it('does not crash the brand edit page when relation query points to a removed tab', function (): void {
    $this->resolveAdminPanel();

    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $brand = Brand::factory()->create([
        'slug' => 'brand-discount-relation-fallback',
    ]);

    Schema::dropIfExists('discount_brands');

    $this->actingAs($admin);

    $response = $this->get("/admin/brands/{$brand->getRouteKey()}/edit?relation=5");

    expect($response->status())->toBeLessThan(500);
});

it('renders the brand edit discounts relation tab when discount pivot exists', function (): void {
    $this->resolveAdminPanel();

    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $brand = Brand::factory()->create([
        'slug' => 'brand-discount-relation-enabled',
    ]);

    $this->actingAs($admin);

    $response = $this->get("/admin/brands/{$brand->getRouteKey()}/edit?relation=5");

    expect($response->status())->toBeLessThan(500);
});
