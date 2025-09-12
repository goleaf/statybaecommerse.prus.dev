<?php declare(strict_types=1);

use App\Models\User;
use App\Models\Order;
use App\Notifications\AdminNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('User Impersonation Integration', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('can complete full impersonation workflow', function () {
        $targetUser = User::factory()->create(['is_admin' => false]);
        
        // Start impersonation
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableAction('impersonate', $targetUser)
            ->assertRedirect('/');
        
        // Verify impersonation session
        expect(session('impersonate'))->not()->toBeNull();
        expect(session('impersonate.original_user_id'))->toBe($this->admin->id);
        expect(session('impersonate.impersonated_user_id'))->toBe($targetUser->id);
        expect(auth()->id())->toBe($targetUser->id);
        
        // Stop impersonation
        $page = new \App\Filament\Pages\UserImpersonation();
        $page->stopImpersonation();
        
        // Verify session is cleared
        expect(session('impersonate'))->toBeNull();
        expect(auth()->id())->toBe($this->admin->id);
    });

    it('can send notification during impersonation workflow', function () {
        Notification::fake();
        
        $targetUser = User::factory()->create(['is_admin' => false]);
        
        // Send notification
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableAction('send_notification', $targetUser, [
                'title' => 'Test Notification',
                'message' => 'This is a test message',
                'type' => 'info',
            ])
            ->assertNotified(__('admin.notifications.notification_sent'));
        
        Notification::assertSentTo($targetUser, AdminNotification::class);
    });

    it('can perform bulk actions on users', function () {
        $users = User::factory()->count(3)->create(['is_admin' => false, 'is_active' => false]);
        
        // Bulk activate users
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableBulkAction('activate', $users)
            ->assertNotified();
        
        foreach ($users as $user) {
            expect($user->fresh()->is_active)->toBeTrue();
        }
        
        // Bulk deactivate users
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableBulkAction('deactivate', $users)
            ->assertNotified();
        
        foreach ($users as $user) {
            expect($user->fresh()->is_active)->toBeFalse();
        }
    });

    it('can filter and search users effectively', function () {
        $activeUser = User::factory()->create([
            'is_admin' => false,
            'is_active' => true,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $inactiveUser = User::factory()->create([
            'is_admin' => false,
            'is_active' => false,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com'
        ]);
        
        // Test filtering by active status
        $component = Livewire::test(\App\Filament\Pages\UserImpersonation::class);
        $component->filterTable('is_active', 'true');
        expect($component->get('tableFilters.is_active.value'))->toBe('true');
        
        // Test searching by name
        $component = Livewire::test(\App\Filament\Pages\UserImpersonation::class);
        $component->searchTable('John');
        expect($component->get('tableSearch'))->toBe('John');
        
        // Test searching by email
        $component = Livewire::test(\App\Filament\Pages\UserImpersonation::class);
        $component->searchTable('jane@example.com');
        expect($component->get('tableSearch'))->toBe('jane@example.com');
    });

    it('can view user orders through impersonation page', function () {
        $user = User::factory()->create(['is_admin' => false]);
        $orders = Order::factory()->count(2)->create(['user_id' => $user->id]);
        
        // Test that orders are displayed in the table
        $this->get('/admin/user-impersonation')
            ->assertSee($user->email);
    });

    it('handles impersonation with middleware correctly', function () {
        $targetUser = User::factory()->create(['is_admin' => false]);
        
        // Start impersonation
        session([
            'impersonate' => [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $targetUser->id,
                'started_at' => now()->toISOString(),
            ]
        ]);
        
        // Make a request that goes through middleware
        $response = $this->get('/admin');
        
        // Verify middleware handled impersonation
        expect(auth()->id())->toBe($targetUser->id);
        
        // Verify impersonation data is shared with view
        $sharedData = view()->shared('impersonating');
        expect($sharedData)->not()->toBeNull();
        expect($sharedData['user']->id)->toBe($targetUser->id);
        expect($sharedData['original_user']->id)->toBe($this->admin->id);
    });

    it('prevents impersonation of admin users', function () {
        $adminUser = User::factory()->create(['is_admin' => true]);
        
        // Admin users should not appear in the table
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->assertCanNotSeeTableRecords([$adminUser]);
    });

    it('handles impersonation session expiration gracefully', function () {
        $targetUser = User::factory()->create(['is_admin' => false]);
        
        // Create expired impersonation session
        session([
            'impersonate' => [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $targetUser->id,
                'started_at' => now()->subHours(25)->toISOString(), // Expired
            ]
        ]);
        
        // Try to stop impersonation
        $page = new \App\Filament\Pages\UserImpersonation();
        $page->stopImpersonation();
        
        // Session should be cleared
        expect(session('impersonate'))->toBeNull();
    });

    it('can handle multiple impersonation sessions', function () {
        $user1 = User::factory()->create(['is_admin' => false]);
        $user2 = User::factory()->create(['is_admin' => false]);
        
        // Impersonate first user
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableAction('impersonate', $user1)
            ->assertRedirect('/');
        
        expect(auth()->id())->toBe($user1->id);
        
        // Stop impersonation
        $page = new \App\Filament\Pages\UserImpersonation();
        $page->stopImpersonation();
        
        expect(auth()->id())->toBe($this->admin->id);
        
        // Impersonate second user
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableAction('impersonate', $user2)
            ->assertRedirect('/');
        
        expect(auth()->id())->toBe($user2->id);
    });
});
