<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\Users;

use App\Filament\Resources\CouponUsageResource;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\RelationManagers\CouponUsagesRelationManager;
use App\Models\AdminUser;
use App\Models\Scopes\UserOwnedScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CouponUsagesRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_usages_relation_manager_uses_full_page_create_action(): void
    {
        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();

        Livewire::test(CouponUsagesRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->assertTableActionExists('create')
            ->assertSee(CouponUsageResource::getUrl('create', ['user_id' => $user->getKey()]), false);
    }

    public function test_coupon_usages_relation_manager_table_query_removes_user_owned_scope(): void
    {
        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();

        $component = Livewire::test(CouponUsagesRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])->assertSuccessful();

        $query = $component->instance()->getTable()->getQuery();

        $this->assertContains(UserOwnedScope::class, $query->removedScopes());
    }
}
