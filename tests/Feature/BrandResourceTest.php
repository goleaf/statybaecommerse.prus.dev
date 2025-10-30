<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\BrandResource\Pages\CreateBrand;
use App\Filament\Resources\BrandResource\Pages\EditBrand;
use App\Filament\Resources\BrandResource\Pages\ListBrands;
use App\Models\Brand;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class BrandResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Register the Filament admin panel to guarantee widget discovery mirrors production.
        $this->resolveAdminPanel();

        // Seed the permissions scaffold so the super admin role inherits every capability.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Provision a deterministic administrator and grant full access for brand management scenarios.
        $this->adminUser = User::factory()->create([
            'email'    => 'brand-admin@example.test',
            'is_admin' => true,
        ]);
        $this->adminUser->assignRole('super_admin');
    }

    public function test_list_page_surfaces_brands_without_global_scopes(): void
    {
        // Create a disabled brand to confirm the resource removes visibility scopes for administrative review.
        $hiddenBrand = Brand::factory()->create([
            'name'       => 'Hidden Brand',
            'slug'       => 'hidden-brand',
            'is_enabled' => false,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListBrands::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$hiddenBrand]);
    }

    public function test_admin_can_create_brand_with_translated_social_links(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(CreateBrand::class)
            ->fillForm([
                'name' => [
                    'lt' => 'Statyba Hero LT',
                    'en' => 'Statyba Hero EN',
                ],
                'slug'        => 'statyba-hero',
                'description' => [
                    'lt' => 'Lietuviškas aprašymas',
                    'en' => 'English description',
                ],
                'website'      => 'https://hero.example',
                'is_premium'   => true,
                'social_links' => [
                    ['platform' => 'facebook', 'url' => 'https://facebook.com/hero'],
                    ['platform' => 'instagram', 'url' => 'https://instagram.com/hero'],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', [
            'slug'       => 'statyba-hero',
            'is_premium' => true,
        ]);
    }

    public function test_admin_can_update_brand_feature_flags(): void
    {
        // Seed a baseline premium brand to exercise the edit workflow.
        $brand = Brand::factory()->create([
            'name'       => 'Legacy Premium',
            'slug'       => 'legacy-premium',
            'is_premium' => true,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(EditBrand::class, ['record' => $brand->getKey()])
            ->fillForm([
                'name' => [
                    'lt' => 'Atnaujintas LT',
                    'en' => 'Updated EN',
                ],
                'slug'        => 'legacy-premium',
                'description' => [
                    'lt' => 'Atnaujintas aprašymas',
                    'en' => 'Updated description',
                ],
                'website'    => 'https://updated.example',
                'is_premium' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', [
            'id'         => $brand->id,
            'is_premium' => false,
        ]);
    }
}
