<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\Order;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
        
        // Test that the name is stored as JSON for translations
        $this->assertIsString($user->name);
        $this->assertStringContainsString('Test User', $user->name);
    }

    public function test_user_has_many_orders(): void
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->orders);
        $this->assertInstanceOf(Order::class, $user->orders->first());
    }

    public function test_user_has_many_addresses(): void
    {
        $user = User::factory()->create();
        $addresses = Address::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->addresses);
        $this->assertInstanceOf(Address::class, $user->addresses->first());
    }

    public function test_user_casts_work_correctly(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }

    public function test_user_fillable_attributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    public function test_user_hidden_attributes(): void
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }


    public function test_user_has_translations_relationship(): void
    {
        $user = User::factory()->create();

        // Test that user has translations relationship
        $this->assertTrue(method_exists($user, 'translations'));
        $this->assertTrue(method_exists($user, 'getTranslation'));
    }
}