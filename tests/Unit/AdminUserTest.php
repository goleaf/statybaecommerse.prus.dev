<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_can_be_created(): void
    {
        $adminUser = AdminUser::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $this->assertInstanceOf(AdminUser::class, $adminUser);
        $this->assertEquals('Admin User', $adminUser->name);
        $this->assertEquals('admin@example.com', $adminUser->email);
        $this->assertTrue(Hash::check('password123', $adminUser->password));
    }

    public function test_admin_user_fillable_attributes(): void
    {
        $adminUser = new AdminUser();
        $fillable = $adminUser->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    public function test_admin_user_hidden_attributes(): void
    {
        $adminUser = new AdminUser();
        $hidden = $adminUser->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    public function test_admin_user_casts(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email_verified_at' => now(),
            'password' => 'password123',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $adminUser->email_verified_at);
        $this->assertTrue(Hash::check('password123', $adminUser->password));
    }

    public function test_admin_user_can_access_panel(): void
    {
        $adminUser = AdminUser::factory()->create();
        $panel = \Filament\Facades\Filament::getDefaultPanel();

        $this->assertTrue($adminUser->canAccessPanel($panel));
    }

    public function test_admin_user_implements_filament_user(): void
    {
        $adminUser = new AdminUser();
        
        $this->assertInstanceOf(\Filament\Models\Contracts\FilamentUser::class, $adminUser);
    }

    public function test_admin_user_uses_notifiable_trait(): void
    {
        $adminUser = new AdminUser();
        
        $this->assertTrue(method_exists($adminUser, 'notify'));
        $this->assertTrue(method_exists($adminUser, 'notifications'));
    }

    public function test_admin_user_password_is_hashed(): void
    {
        $adminUser = AdminUser::factory()->create([
            'password' => 'plaintext_password',
        ]);

        $this->assertNotEquals('plaintext_password', $adminUser->password);
        $this->assertTrue(Hash::check('plaintext_password', $adminUser->password));
    }

    public function test_admin_user_email_verification(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->assertNull($adminUser->email_verified_at);

        $adminUser->email_verified_at = now();
        $adminUser->save();

        $this->assertNotNull($adminUser->email_verified_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $adminUser->email_verified_at);
    }

    public function test_admin_user_remember_token(): void
    {
        $adminUser = AdminUser::factory()->create();
        
        // Factory creates a remember token, so we check it's not null
        $this->assertNotNull($adminUser->remember_token);

        $adminUser->remember_token = 'test_token';
        $adminUser->save();

        $this->assertEquals('test_token', $adminUser->remember_token);
    }

    public function test_admin_user_table_name(): void
    {
        $adminUser = new AdminUser();
        
        $this->assertEquals('admin_users', $adminUser->getTable());
    }

    public function test_admin_user_primary_key(): void
    {
        $adminUser = new AdminUser();
        
        $this->assertEquals('id', $adminUser->getKeyName());
    }

    public function test_admin_user_authentication(): void
    {
        $adminUser = AdminUser::factory()->create([
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        // Test password verification
        $this->assertTrue(\Hash::check('password123', $adminUser->password));
        
        // Test that the user can be found by email
        $foundUser = AdminUser::where('email', 'admin@test.com')->first();
        $this->assertNotNull($foundUser);
        $this->assertEquals($adminUser->id, $foundUser->id);
    }

    public function test_admin_user_factory(): void
    {
        $adminUser = AdminUser::factory()->create();

        $this->assertInstanceOf(AdminUser::class, $adminUser);
        $this->assertNotEmpty($adminUser->name);
        $this->assertNotEmpty($adminUser->email);
        $this->assertNotEmpty($adminUser->password);
    }
}
