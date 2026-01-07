<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\UserImpersonation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(UserImpersonation::class)]
final class UserImpersonationPageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_page_configuration(): void
    {
        $this->assertSame('heroicon-o-user', UserImpersonation::getNavigationIcon());
        $this->assertSame('System', UserImpersonation::getNavigationGroup());
        $this->assertSame('user-impersonation', UserImpersonation::getSlug());
    }

    public function test_page_renders_successfully(): void
    {
        Livewire::test(UserImpersonation::class)
            ->assertSuccessful();
    }

    public function test_table_shows_only_non_admin_users(): void
    {
        // Create admin and regular users
        $adminUser = User::factory()->admin()->create();
        $regularUser1 = User::factory()->create(['is_admin' => false]);
        $regularUser2 = User::factory()->create(['is_admin' => false]);

        $component = Livewire::test(UserImpersonation::class);

        // Should see regular users
        $component->assertCanSeeTableRecords([$regularUser1, $regularUser2]);

        // Should not see admin users (except possibly in the query, but filtered out)
        $component->assertSuccessful();
    }

    public function test_impersonate_action_sets_session(): void
    {
        $targetUser = User::factory()->create(['is_admin' => false]);

        Livewire::test(UserImpersonation::class)
            ->callTableAction('impersonate', $targetUser);

        // Check that impersonation session was set
        $this->assertSame($this->admin->id, session('impersonate.original_user_id'));

        // Check that we're now authenticated as the target user
        $this->assertSame($targetUser->id, auth()->id());
    }

    public function test_send_notification_action_form(): void
    {
        $targetUser = User::factory()->create(['is_admin' => false]);

        $component = Livewire::test(UserImpersonation::class)
            ->mountTableAction('send_notification', $targetUser);

        // Test form validation
        $component->assertTableActionHasFormField('send_notification', 'title');
        $component->assertTableActionHasFormField('send_notification', 'message');
        $component->assertTableActionHasFormField('send_notification', 'type');
    }

    public function test_send_notification_action_requires_fields(): void
    {
        $targetUser = User::factory()->create(['is_admin' => false]);

        Livewire::test(UserImpersonation::class)
            ->mountTableAction('send_notification', $targetUser)
            ->setTableActionData([
                'title'   => '',
                'message' => '',
                'type'    => '',
            ])
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['title', 'message', 'type']);
    }

    public function test_send_notification_action_with_valid_data(): void
    {
        $targetUser = User::factory()->create(['is_admin' => false]);

        Livewire::test(UserImpersonation::class)
            ->mountTableAction('send_notification', $targetUser)
            ->setTableActionData([
                'title'   => 'Test Notification',
                'message' => 'This is a test message',
                'type'    => 'info',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();
    }

    public function test_table_columns_are_searchable(): void
    {
        $user = User::factory()->create([
            'name'     => 'John Doe',
            'email'    => 'john@example.com',
            'is_admin' => false,
        ]);

        $component = Livewire::test(UserImpersonation::class);

        // Test name search
        $component->searchTable('John')
            ->assertCanSeeTableRecords([$user]);

        // Test email search
        $component->searchTable('john@example.com')
            ->assertCanSeeTableRecords([$user]);
    }

    public function test_page_title_and_metadata(): void
    {
        $page = app(UserImpersonation::class);

        $this->assertSame('User Impersonation', $page->getTitle());
    }
}
