<?php declare(strict_types=1);

use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('User Impersonation Access Control', function () {
    it('allows administrator to access user impersonation page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        
        $this->actingAs($admin)
            ->get('/admin/user-impersonation')
            ->assertOk();
    });

    it('allows admin role to access user impersonation page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $this->actingAs($admin)
            ->get('/admin/user-impersonation')
            ->assertOk();
    });

    it('denies access to regular users', function () {
        $user = User::factory()->create();
        $user->assignRole('user');
        
        $this->actingAs($user)
            ->get('/admin/user-impersonation')
            ->assertForbidden();
    });

    it('denies access to unauthenticated users', function () {
        $this->get('/admin/user-impersonation')
            ->assertRedirect('/admin/login');
    });
});

describe('User Impersonation Page Functionality', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('displays user list with correct columns', function () {
        $users = User::factory()->count(3)->create(['is_admin' => false]);
        
        $this->get('/admin/user-impersonation')
            ->assertSee('Name')
            ->assertSee('Email')
            ->assertSee('Orders')
            ->assertSee('Created At')
            ->assertSee('Last Login')
            ->assertSee('Status');
    });

    it('filters out admin users from impersonation list', function () {
        $adminUser = User::factory()->create(['is_admin' => true]);
        $regularUser = User::factory()->create(['is_admin' => false]);
        
        $this->get('/admin/user-impersonation')
            ->assertDontSee($adminUser->email)
            ->assertSee($regularUser->email);
    });

    it('shows impersonate action for regular users', function () {
        $user = User::factory()->create(['is_admin' => false]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->assertTableActionExists('impersonate')
            ->assertTableActionVisible('impersonate', $user);
    });

    it('hides impersonate action for admin users', function () {
        $adminUser = User::factory()->create(['is_admin' => true]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->assertTableActionNotVisible('impersonate', $adminUser);
    });
});

describe('User Impersonation Actions', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('can start impersonation session', function () {
        $targetUser = User::factory()->create(['is_admin' => false]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableAction('impersonate', $targetUser)
            ->assertRedirect('/');
        
        expect(session('impersonate'))->not()->toBeNull();
        expect(session('impersonate.original_user_id'))->toBe($this->admin->id);
        expect(session('impersonate.impersonated_user_id'))->toBe($targetUser->id);
        expect(auth()->id())->toBe($targetUser->id);
    });

    it('prevents impersonating admin users', function () {
        $adminUser = User::factory()->create(['is_admin' => true]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableAction('impersonate', $adminUser)
            ->assertNotified(__('admin.notifications.cannot_impersonate_admin'));
        
        expect(session('impersonate'))->toBeNull();
        expect(auth()->id())->toBe($this->admin->id);
    });

    it('can stop impersonation session', function () {
        $targetUser = User::factory()->create(['is_admin' => false]);
        
        // Start impersonation
        session([
            'impersonate' => [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $targetUser->id,
                'started_at' => now()->toISOString(),
            ]
        ]);
        auth()->login($targetUser);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callHeaderAction('stop_impersonation')
            ->assertRedirect('/admin');
        
        expect(session('impersonate'))->toBeNull();
        expect(auth()->id())->toBe($this->admin->id);
    });

    it('shows stop impersonation button when impersonating', function () {
        session([
            'impersonate' => [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => 999,
                'started_at' => now()->toISOString(),
            ]
        ]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->assertHeaderActionExists('stop_impersonation')
            ->assertHeaderActionVisible('stop_impersonation');
    });

    it('hides stop impersonation button when not impersonating', function () {
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->assertHeaderActionNotVisible('stop_impersonation');
    });
});

describe('User Impersonation Notifications', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('can send notification to user', function () {
        Notification::fake();
        
        $targetUser = User::factory()->create(['is_admin' => false]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableAction('send_notification', $targetUser, [
                'title' => 'Test Notification',
                'message' => 'This is a test message',
                'type' => 'info',
            ])
            ->assertNotified(__('admin.notifications.notification_sent'));
        
        Notification::assertSentTo($targetUser, AdminNotification::class);
    });

    it('validates notification form data', function () {
        $targetUser = User::factory()->create(['is_admin' => false]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableAction('send_notification', $targetUser, [
                'title' => '',
                'message' => '',
                'type' => '',
            ])
            ->assertHasTableActionErrors(['title', 'message', 'type']);
    });
});

describe('User Impersonation Bulk Actions', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('can bulk activate users', function () {
        $users = User::factory()->count(3)->create(['is_admin' => false, 'is_active' => false]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableBulkAction('activate', $users)
            ->assertNotified();
        
        foreach ($users as $user) {
            expect($user->fresh()->is_active)->toBeTrue();
        }
    });

    it('can bulk deactivate users', function () {
        $users = User::factory()->count(3)->create(['is_admin' => false, 'is_active' => true]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableBulkAction('deactivate', $users)
            ->assertNotified();
        
        foreach ($users as $user) {
            expect($user->fresh()->is_active)->toBeFalse();
        }
    });
});

describe('User Impersonation Filters', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('can filter by active status', function () {
        $activeUser = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $inactiveUser = User::factory()->create(['is_admin' => false, 'is_active' => false]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->filterTable('is_active', 'true')
            ->assertCanSeeTableRecords([$activeUser])
            ->assertCanNotSeeTableRecords([$inactiveUser]);
    });

    it('can filter by users with orders', function () {
        $userWithOrders = User::factory()->create(['is_admin' => false]);
        $userWithoutOrders = User::factory()->create(['is_admin' => false]);
        
        // Create orders for one user
        \App\Models\Order::factory()->count(2)->create(['user_id' => $userWithOrders->id]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->filterTable('has_orders')
            ->assertCanSeeTableRecords([$userWithOrders])
            ->assertCanNotSeeTableRecords([$userWithoutOrders]);
    });

    it('can filter by recent activity', function () {
        $recentUser = User::factory()->create([
            'is_admin' => false,
            'last_login_at' => now()->subDays(5)
        ]);
        $oldUser = User::factory()->create([
            'is_admin' => false,
            'last_login_at' => now()->subDays(60)
        ]);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->filterTable('recent_activity')
            ->assertCanSeeTableRecords([$recentUser])
            ->assertCanNotSeeTableRecords([$oldUser]);
    });
});

describe('User Impersonation Search', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('can search users by name', function () {
        $john = User::factory()->create(['is_admin' => false, 'name' => 'John Doe']);
        $jane = User::factory()->create(['is_admin' => false, 'name' => 'Jane Smith']);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$john])
            ->assertCanNotSeeTableRecords([$jane]);
    });

    it('can search users by email', function () {
        $user1 = User::factory()->create(['is_admin' => false, 'email' => 'john@example.com']);
        $user2 = User::factory()->create(['is_admin' => false, 'email' => 'jane@example.com']);
        
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->searchTable('john@example.com')
            ->assertCanSeeTableRecords([$user1])
            ->assertCanNotSeeTableRecords([$user2]);
    });
});

describe('User Impersonation Middleware', function () {
    it('handles impersonation session correctly', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        $targetUser = User::factory()->create(['is_admin' => false]);
        
        // Start impersonation
        session([
            'impersonate' => [
                'original_user_id' => $admin->id,
                'impersonated_user_id' => $targetUser->id,
                'started_at' => now()->toISOString(),
            ]
        ]);
        
        $this->actingAs($admin)
            ->get('/')
            ->assertOk();
        
        // The middleware should handle the impersonation
        expect(auth()->id())->toBe($targetUser->id);
    });
});
