<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\BrandResource;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BrandResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole($adminRole);
        $this->actingAs($this->adminUser);
    }

    public function test_can_render_brand_index_page(): void
    {
        $this->get(BrandResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_list_brands(): void
    {
        $brands = Brand::factory()->count(10)->create();

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->assertCanSeeTableRecords($brands);
    }

    public function test_can_create_brand(): void
    {
        $newData = [
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'description' => 'Test brand description',
            'website' => 'https://testbrand.lt',
            'is_enabled' => true,
        ];

        Livewire::test(BrandResource\Pages\CreateBrand::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', [
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'website' => 'https://testbrand.lt',
        ]);
    }

    public function test_can_validate_brand_creation(): void
    {
        Livewire::test(BrandResource\Pages\CreateBrand::class)
            ->fillForm([
                'name' => '',
                'slug' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);
    }

    public function test_can_update_brand(): void
    {
        $brand = Brand::factory()->create();

        $newData = [
            'name' => 'Updated Brand Name',
            'description' => 'Updated description',
        ];

        Livewire::test(BrandResource\Pages\EditBrand::class, [
            'record' => $brand->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'Updated Brand Name',
            'description' => 'Updated description',
        ]);
    }

    public function test_can_delete_brand(): void
    {
        $brand = Brand::factory()->create();

        Livewire::test(BrandResource\Pages\EditBrand::class, [
            'record' => $brand->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    public function test_can_filter_brands_by_enabled_status(): void
    {
        $enabledBrand = Brand::factory()->create(['is_enabled' => true]);
        $disabledBrand = Brand::factory()->create(['is_enabled' => false]);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->filterTable('enabled')
            ->assertCanSeeTableRecords([$enabledBrand])
            ->assertCanNotSeeTableRecords([$disabledBrand]);
    }

    public function test_can_search_brands(): void
    {
        $brandA = Brand::factory()->create(['name' => 'Makita Lietuva']);
        $brandB = Brand::factory()->create(['name' => 'Bosch Professional']);

        Livewire::test(BrandResource\Pages\ListBrands::class)
            ->searchTable('Makita')
            ->assertCanSeeTableRecords([$brandA])
            ->assertCanNotSeeTableRecords([$brandB]);
    }
}
