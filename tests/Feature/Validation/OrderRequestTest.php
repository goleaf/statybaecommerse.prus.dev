<?php

declare(strict_types=1);

namespace Tests\Feature\Validation;

use App\Models\Order;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class OrderRequestTest extends TestCase
{
    private function actingAsAdminUser(): User
    {
        $user = User::factory()->create();

        // Ensure a matching role exists and assign it so AuthorizationMatrix grants permissions.
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole('admin');

        $this->actingAs($user);

        return $user;
    }

    public function test_store_missing_items_returns_422(): void
    {
        $this->actingAsAdminUser();

        $this->postJson('/orders', [])
            ->assertStatus(422);
    }

    public function test_update_invalid_payload_returns_422(): void
    {
        $user = $this->actingAsAdminUser();

        $order = Order::factory()->create(['user_id' => $user->getKey()]);

        $this->putJson("/orders/{$order->getKey()}", [
            'items' => [],
        ])->assertStatus(422);
    }
}

