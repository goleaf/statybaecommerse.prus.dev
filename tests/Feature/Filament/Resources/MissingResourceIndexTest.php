<?php

declare(strict_types=1);

use App\Filament\Resources\AuditTrailResource;
use App\Filament\Resources\BrandResource;
use App\Filament\Resources\CampaignResource;
use App\Filament\Resources\CityResource;
use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\CollectionRuleResource;
use App\Filament\Resources\EnumManagementResource;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\ProductVariantResource;
use App\Filament\Resources\RecommendationAnalyticsResource;
use App\Filament\Resources\ReferralCampaignResource;
use App\Filament\Resources\Settings\SettingResource;
use App\Filament\Resources\SliderTranslationResource;
use App\Filament\Resources\SystemSettingResource;
use App\Filament\Resources\UserManagementResource;
use App\Filament\Resources\UserPreferenceResource;
use App\Filament\Resources\VariantStockResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * @return array<int, class-string>
 */
dataset('filament-resource-indices', [
    // Smoke test each Filament resource that previously lacked coverage to ensure their index routes stay registered.
    AuditTrailResource::class,
    BrandResource::class,
    CampaignResource::class,
    CityResource::class,
    CollectionResource::class,
    CollectionRuleResource::class,
    EnumManagementResource::class,
    PostResource::class,
    ProductVariantResource::class,
    RecommendationAnalyticsResource::class,
    ReferralCampaignResource::class,
    SettingResource::class,
    SliderTranslationResource::class,
    SystemSettingResource::class,
    UserManagementResource::class,
    UserPreferenceResource::class,
    VariantStockResource::class,
]);

it('feature: mounts each Filament resource index page', function (string $resourceClass): void {
    // Arrange: create an administrator-capable user so Filament authorisation passes in the test environment.
    $user = User::factory()->admin()->create();
    actingAs($user);

    // Act & Assert: hitting the resource index route should return an OK response when the resource is registered correctly.
    $this
        ->get($resourceClass::getUrl('index'))
        ->assertOk();
})->with('filament-resource-indices');
