<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\UserController;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the wishlist pivot includes a product column so profile hydration queries never fail.
        if (Schema::hasTable('user_wishlists') && ! Schema::hasColumn('user_wishlists', 'product_id')) {
            Schema::table('user_wishlists', static function (Blueprint $table): void {
                $table->unsignedBigInteger('product_id')->nullable();
            });
        }
    }

    public function test_profile_returns_authenticated_user_resource(): void
    {
        $controller = new UserController();
        $user = User::factory()->create([
            'first_name' => 'Ada',
            'last_name'  => 'Lovelace',
            'email'      => 'ada@example.com',
        ]);
        $request = Request::create('/api/users/profile', 'GET');
        $request->setUserResolver(static fn () => $user);

        $resource = $controller->profile($request);

        // Assert the controller returns a UserResource so downstream transforms remain consistent.
        $this->assertInstanceOf(UserResource::class, $resource);
        $payload = $resource->resolve();
        // Assert the contract envelope exposes the user identifier so consumers can reconcile updates.
        $this->assertSame($user->getKey(), $payload['data']['id']);
        // Assert the resource includes the hydrated first name for profile headers.
        $this->assertSame('Ada', $payload['data']['first_name']);
    }

    public function test_update_profile_persists_mutable_attributes(): void
    {
        $controller = new UserController();
        $user = User::factory()->create([
            'first_name' => 'Grace',
            'last_name'  => 'Hopper',
            'phone_number' => '123456',
        ]);
        $request = Request::create('/api/users/profile', 'POST', [
            'first_name' => 'Grace',
            'last_name'  => 'Updated',
            'phone_number' => '987654321',
        ]);
        $request->setUserResolver(static fn () => $user);

        $resource = $controller->updateProfile($request);

        $user->refresh();
        // Assert the profile update wrote the new phone number so contact data stays fresh.
        $this->assertSame('987654321', $user->phone_number);
        $resourcePayload = $resource->resolve();
        // Assert the returned resource mirrors the persisted last name for confirmation UIs.
        $this->assertSame('Updated', $resourcePayload['data']['last_name']);
    }

    public function test_statistics_endpoint_summarises_account_totals(): void
    {
        $controller = new UserController();
        $admin = User::factory()->create(['is_admin' => true]);
        $request = Request::create('/api/users/statistics', 'GET');
        $request->setUserResolver(static fn () => $admin);
        $this->actingAs($admin);

        User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
        User::factory()->create(['is_active' => false]);

        $response = $controller->statistics($request);

        // Assert the statistics method returns a JsonResponse for contract compatibility.
        $this->assertInstanceOf(JsonResponse::class, $response);
        $payload = $response->getData(true);
        // Assert the payload flags success so dashboards can render counts confidently.
        $this->assertTrue($payload['success']);
        // Assert total_users reflects the active cohort governed by the global scope.
        $this->assertSame(2, $payload['data']['total_users']);
        // Assert active_users matches the same scoped total for clarity in dashboards.
        $this->assertSame(2, $payload['data']['active_users']);
        // Assert inactive_users returns zero because globally scoped queries exclude disabled accounts.
        $this->assertSame(0, $payload['data']['inactive_users']);
        // Assert verified_users tallies the scoped verified user count.
        $this->assertSame(2, $payload['data']['verified_users']);
    }
}
