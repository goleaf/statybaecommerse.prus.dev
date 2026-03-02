<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\RelationManagers\OrdersRelationManager;
use App\Models\AdminUser;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class OrdersRelationManagerVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_relation_manager_shows_cancelled_orders_for_user(): void
    {
        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $order = Order::factory()
            ->cancelled()
            ->create([
                'user_id' => $user->getKey(),
            ]);

        Livewire::test(OrdersRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$order]);
    }
}
