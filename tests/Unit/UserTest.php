<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    public function test_user_has_many_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->orders);
        $this->assertInstanceOf(Order::class, $user->orders->first());
    }

    public function test_user_preferred_locale(): void
    {
        $user = User::factory()->create(['preferred_locale' => 'lt']);

        $this->assertEquals('lt', $user->preferredLocale());
    }

    public function test_user_roles_label_accessor(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole($role);

        $this->assertEquals('Admin', $user->rolesLabel);
    }

    public function test_user_roles_label_when_no_roles(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('N/A', $user->rolesLabel);
    }

    public function test_user_implements_locale_preference(): void
    {
        $user = new User();
        
        $this->assertInstanceOf(
            \Illuminate\Contracts\Translation\HasLocalePreference::class,
            $user
        );
    }

    public function test_user_uses_spatie_roles(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'manager', 'guard_name' => 'web']);
        
        $user->assignRole($role);
        
        $this->assertTrue($user->hasRole('manager'));
        $this->assertCount(1, $user->roles);
    }

    public function test_user_fillable_attributes(): void
    {
        $user = new User();
        
        $expectedFillable = [
            'email',
            'password',
            'preferred_locale',
            'email_verified_at',
            'first_name',
            'last_name',
            'gender',
            'phone_number',
            'birth_date',
            'timezone',
            'opt_in',
        ];

        $this->assertEquals($expectedFillable, $user->getFillable());
    }

    public function test_user_hidden_attributes(): void
    {
        $user = new User();
        
        $expectedHidden = [
            'password',
            'remember_token',
        ];

        $this->assertEquals($expectedHidden, $user->getHidden());
    }

    public function test_user_casts(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => '2025-01-01 12:00:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }
}
