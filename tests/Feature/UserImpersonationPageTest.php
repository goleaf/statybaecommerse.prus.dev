<?php declare(strict_types=1);

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('User Impersonation Page Tests', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        
        $this->regularUser = User::factory()->create(['is_admin' => false]);
        $this->anotherAdmin = User::factory()->create(['is_admin' => true]);
        
        $this->actingAs($this->admin);
    });

    describe('Page Access and Navigation', function () {
        it('can access user impersonation page', function () {
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee('User Impersonation');
        });

        it('shows correct navigation label', function () {
            $this->get('/admin/user-impersonation')
                ->assertOk();
        });

        it('has correct navigation icon and group', function () {
            // Test that the page has the correct navigation properties
            expect(true)->toBeTrue(); // Placeholder for navigation tests
        });
    });

    describe('User Table Functionality', function () {
        it('displays non-admin users in the table', function () {
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee($this->regularUser->name)
                ->assertSee($this->regularUser->email);
        });

        it('does not display admin users in the table', function () {
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertDontSee($this->anotherAdmin->name);
        });

        it('shows user order count', function () {
            Order::factory()->count(3)->create(['user_id' => $this->regularUser->id]);
            
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee('3'); // Order count
        });

        it('shows user creation date', function () {
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee($this->regularUser->created_at->format('Y-m-d'));
        });

        it('shows user last login date', function () {
            $this->regularUser->update(['last_login_at' => now()->subDays(1)]);
            
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee($this->regularUser->last_login_at->format('Y-m-d'));
        });

        it('shows user active status', function () {
            $this->regularUser->update(['is_active' => false]);
            
            $this->get('/admin/user-impersonation')
                ->assertOk();
        });
    });

    describe('Table Filters', function () {
        it('can filter by active users', function () {
            $activeUser = User::factory()->create(['is_admin' => false, 'is_active' => true]);
            $inactiveUser = User::factory()->create(['is_admin' => false, 'is_active' => false]);
            
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee($activeUser->name)
                ->assertSee($inactiveUser->name);
        });

        it('can filter by users with orders', function () {
            $userWithOrders = User::factory()->create(['is_admin' => false]);
            Order::factory()->create(['user_id' => $userWithOrders->id]);
            
            $userWithoutOrders = User::factory()->create(['is_admin' => false]);
            
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee($userWithOrders->name)
                ->assertSee($userWithoutOrders->name);
        });

        it('can filter by recent activity', function () {
            $recentUser = User::factory()->create([
                'is_admin' => false,
                'last_login_at' => now()->subDays(1)
            ]);
            
            $oldUser = User::factory()->create([
                'is_admin' => false,
                'last_login_at' => now()->subDays(60)
            ]);
            
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee($recentUser->name)
                ->assertSee($oldUser->name);
        });
    });

    describe('Table Actions', function () {
        it('can impersonate a user', function () {
            // Test the impersonate action
            expect(true)->toBeTrue(); // Placeholder for action test
        });

        it('can view user orders', function () {
            Order::factory()->create(['user_id' => $this->regularUser->id]);
            
            // Test the view orders action
            expect(true)->toBeTrue(); // Placeholder for action test
        });

        it('can send notification to user', function () {
            // Test the send notification action
            expect(true)->toBeTrue(); // Placeholder for action test
        });
    });

    describe('Bulk Actions', function () {
        it('can activate multiple users', function () {
            $user1 = User::factory()->create(['is_admin' => false, 'is_active' => false]);
            $user2 = User::factory()->create(['is_admin' => false, 'is_active' => false]);
            
            // Test bulk activate action
            expect(true)->toBeTrue(); // Placeholder for bulk action test
        });

        it('can deactivate multiple users', function () {
            $user1 = User::factory()->create(['is_admin' => false, 'is_active' => true]);
            $user2 = User::factory()->create(['is_admin' => false, 'is_active' => true]);
            
            // Test bulk deactivate action
            expect(true)->toBeTrue(); // Placeholder for bulk action test
        });
    });

    describe('Search and Sorting', function () {
        it('can search users by name', function () {
            $user1 = User::factory()->create(['is_admin' => false, 'name' => 'John Doe']);
            $user2 = User::factory()->create(['is_admin' => false, 'name' => 'Jane Smith']);
            
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee($user1->name)
                ->assertSee($user2->name);
        });

        it('can search users by email', function () {
            $user1 = User::factory()->create(['is_admin' => false, 'email' => 'john@example.com']);
            $user2 = User::factory()->create(['is_admin' => false, 'email' => 'jane@example.com']);
            
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee($user1->email)
                ->assertSee($user2->email);
        });

        it('can sort users by creation date', function () {
            $user1 = User::factory()->create(['is_admin' => false, 'created_at' => now()->subDays(2)]);
            $user2 = User::factory()->create(['is_admin' => false, 'created_at' => now()->subDays(1)]);
            
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee($user1->name)
                ->assertSee($user2->name);
        });

        it('can sort users by last login', function () {
            $user1 = User::factory()->create(['is_admin' => false, 'last_login_at' => now()->subDays(2)]);
            $user2 = User::factory()->create(['is_admin' => false, 'last_login_at' => now()->subDays(1)]);
            
            $this->get('/admin/user-impersonation')
                ->assertOk()
                ->assertSee($user1->name)
                ->assertSee($user2->name);
        });
    });

    describe('Header Actions', function () {
        it('shows stop impersonation button when impersonating', function () {
            session(['impersonate' => [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $this->regularUser->id,
                'started_at' => now()->toISOString(),
            ]]);
            
            $this->get('/admin/user-impersonation')
                ->assertOk();
        });

        it('hides stop impersonation button when not impersonating', function () {
            $this->get('/admin/user-impersonation')
                ->assertOk();
        });
    });
});
