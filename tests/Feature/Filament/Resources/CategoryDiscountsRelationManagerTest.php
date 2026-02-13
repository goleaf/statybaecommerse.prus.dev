<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\RelationManagers\DiscountsRelationManager;
use App\Models\Category;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CategoryDiscountsRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private Discount $discount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->actingAs($admin);

        $this->category = Category::withoutGlobalScopes()->create([
            'name'       => 'Discount Category',
            'slug'       => 'discount-category',
            'is_visible' => true,
            'is_enabled' => true,
            'is_active'  => true,
        ]);

        $this->discount = Discount::withoutGlobalScopes()->create([
            'name'       => 'Category Discount',
            'slug'       => 'category-discount',
            'type'       => 'fixed',
            'value'      => 10,
            'is_active'  => true,
            'is_enabled' => true,
        ]);

        $this->category->discounts()->attach($this->discount->getKey());
    }

    public function test_edit_action_updates_existing_discount_code(): void
    {
        DiscountCode::withoutGlobalScopes()->create([
            'discount_id' => $this->discount->getKey(),
            'code'        => 'OLD-CODE',
            'name'        => $this->discount->name,
            'status'      => 'active',
            'is_active'   => true,
            'type'        => 'fixed',
            'value'       => 10,
        ]);

        Livewire::test(DiscountsRelationManager::class, [
            'ownerRecord' => $this->category,
            'pageClass'   => EditCategory::class,
        ])
            ->mountTableAction('edit', $this->discount->getKey())
            ->set('mountedActions.0.data.code', 'NEW-CODE')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('discount_codes', [
            'discount_id' => $this->discount->getKey(),
            'code'        => 'NEW-CODE',
        ]);
    }

    public function test_edit_action_creates_discount_code_when_missing(): void
    {
        Livewire::test(DiscountsRelationManager::class, [
            'ownerRecord' => $this->category,
            'pageClass'   => EditCategory::class,
        ])
            ->mountTableAction('edit', $this->discount->getKey())
            ->set('mountedActions.0.data.code', 'FIRST-CODE')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('discount_codes', [
            'discount_id' => $this->discount->getKey(),
            'code'        => 'FIRST-CODE',
        ]);

        Livewire::test(DiscountsRelationManager::class, [
            'ownerRecord' => $this->category,
            'pageClass'   => EditCategory::class,
        ])
            ->searchTable('FIRST-CODE')
            ->assertCanSeeTableRecords([$this->discount]);
    }
}
