<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\SubscriberResource\Pages\CreateSubscriber;
use App\Filament\Resources\SubscriberResource\Pages\EditSubscriber;
use App\Filament\Resources\SubscriberResource\Pages\ListSubscribers;
use App\Filament\Resources\SubscriberResource\Pages\ViewSubscriber;
use App\Models\Subscriber;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class SubscriberResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('view notifications', 'web');
        Gate::before(fn ($user = null, ?string $ability = null) => true);
        Filament::setCurrentPanel('admin');
    }

    public function test_can_list_subscribers(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $subscribers = Subscriber::factory()->count(5)->create();
        $first = $subscribers->first();

        $this->actingAs($adminUser);

        Livewire::test(ListSubscribers::class)
            ->assertTableColumnExists('email')
            ->searchTable($first->email)
            ->assertSee($first->email);
    }

    public function test_can_create_subscriber(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $user = User::factory()->create();

        $this->actingAs($adminUser);

        Livewire::test(CreateSubscriber::class)
            ->fillForm([
                'email' => 'test@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+37060000000',
                'company' => 'Test Company',
                'job_title' => 'Developer',
                'status' => 'active',
                'source' => 'website',
                'user_id' => $user->id,
                'interests' => ['products', 'news'],
                'metadata' => ['utm_source' => 'google'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('subscribers', [
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'status' => 'active',
        ]);
    }

    public function test_can_edit_subscriber(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $subscriber = Subscriber::factory()->create();

        $this->actingAs($adminUser);

        Livewire::test(EditSubscriber::class, ['record' => $subscriber->id])
            ->fillForm([
                'first_name' => 'Updated Name',
                'status' => 'inactive',
                'source' => 'website',
                'phone' => '+37060000000',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('subscribers', [
            'id' => $subscriber->id,
            'first_name' => 'Updated Name',
            'status' => 'inactive',
        ]);
    }

    public function test_can_view_subscriber(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $subscriber = Subscriber::factory()->create();

        $this->actingAs($adminUser);

        Livewire::test(ViewSubscriber::class, ['record' => $subscriber->id])
            ->assertSee($subscriber->email);
    }

    public function test_can_delete_subscriber(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $subscriber = Subscriber::factory()->create();

        $this->actingAs($adminUser);

        Livewire::test(EditSubscriber::class, ['record' => $subscriber->id])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertSoftDeleted('subscribers', ['id' => $subscriber->id]);
    }

    public function test_can_filter_subscribers_by_status(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $activeSubscriber = Subscriber::factory()->active()->create();
        $inactiveSubscriber = Subscriber::factory()->inactive()->create();

        $this->actingAs($adminUser);

        Livewire::test(ListSubscribers::class)
            ->filterTable('status', 'active')
            ->assertCanSeeTableRecords([$activeSubscriber])
            ->assertCanNotSeeTableRecords([$inactiveSubscriber]);
    }

    public function test_can_filter_subscribers_by_source(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $websiteSubscriber = Subscriber::factory()->fromSource('website')->create();
        $adminSubscriber = Subscriber::factory()->fromSource('admin')->create();

        $this->actingAs($adminUser);

        Livewire::test(ListSubscribers::class)
            ->filterTable('source', 'website')
            ->assertCanSeeTableRecords([$websiteSubscriber])
            ->assertCanNotSeeTableRecords([$adminSubscriber]);
    }

    public function test_can_search_subscribers(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $subscriber1 = Subscriber::factory()->create(['email' => 'john@example.com']);
        $subscriber2 = Subscriber::factory()->create(['email' => 'jane@example.com']);

        $this->actingAs($adminUser);

        Livewire::test(ListSubscribers::class)
            ->searchTable('john')
            ->assertCanSeeTableRecords([$subscriber1])
            ->assertCanNotSeeTableRecords([$subscriber2]);
    }

    public function test_can_bulk_verify_subscribers(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $subscribers = Subscriber::factory()->count(3)->create(['is_verified' => false]);

        $this->actingAs($adminUser);

        Livewire::test(ListSubscribers::class)
            ->callTableBulkAction('verify', $subscribers->pluck('id')->all());

        foreach ($subscribers as $subscriber) {
            $this->assertDatabaseHas('subscribers', [
                'id' => $subscriber->id,
                'is_verified' => true,
            ]);
        }
    }

    public function test_can_bulk_unsubscribe_subscribers(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $subscribers = Subscriber::factory()->active()->count(3)->create();

        $this->actingAs($adminUser);

        Livewire::test(ListSubscribers::class)
            ->callTableBulkAction('unsubscribe', $subscribers->pluck('id')->all());

        foreach ($subscribers as $subscriber) {
            $this->assertDatabaseHas('subscribers', [
                'id' => $subscriber->id,
                'status' => 'unsubscribed',
            ]);
        }
    }

    public function test_can_verify_individual_subscriber(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $subscriber = Subscriber::factory()->create(['is_verified' => false]);

        $this->actingAs($adminUser);

        Livewire::test(ListSubscribers::class)
            ->callTableAction('verify', $subscriber)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('subscribers', [
            'id' => $subscriber->id,
            'is_verified' => true,
        ]);
    }

    public function test_can_unsubscribe_individual_subscriber(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $subscriber = Subscriber::factory()->active()->create();

        $this->actingAs($adminUser);

        Livewire::test(ListSubscribers::class)
            ->callTableAction('unsubscribe', $subscriber)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('subscribers', [
            'id' => $subscriber->id,
            'status' => 'unsubscribed',
        ]);
    }

    public function test_can_resubscribe_individual_subscriber(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $subscriber = Subscriber::factory()->unsubscribed()->create();

        $this->actingAs($adminUser);

        Livewire::test(ListSubscribers::class)
            ->callTableAction('resubscribe', $subscriber)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('subscribers', [
            'id' => $subscriber->id,
            'status' => 'active',
        ]);
    }

    public function test_subscriber_validation_rules(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);

        $this->actingAs($adminUser);

        Livewire::test(CreateSubscriber::class)
            ->fillForm([
                'email' => 'invalid-email',
                'phone' => 'invalid-phone',
            ])
            ->call('create')
            ->assertHasFormErrors(['email', 'phone']);
    }

    public function test_subscriber_unique_email_validation(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $existingSubscriber = Subscriber::factory()->create(['email' => 'existing@example.com']);

        $this->actingAs($adminUser);

        Livewire::test(CreateSubscriber::class)
            ->fillForm([
                'email' => 'existing@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'status' => 'active',
                'source' => 'website',
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    public function test_subscriber_relationship_with_user(): void
    {
        $adminUser = User::factory()->create(['email' => 'admin@example.com']);
        $user = User::factory()->create();
        $subscriber = Subscriber::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $subscriber->user);
        $this->assertEquals($user->id, $subscriber->user->id);
    }

    public function test_subscriber_full_name_accessor(): void
    {
        $subscriber = Subscriber::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $subscriber->full_name);
    }

    public function test_subscriber_scopes(): void
    {
        $activeSubscriber = Subscriber::factory()->active()->create();
        $inactiveSubscriber = Subscriber::factory()->inactive()->create();
        $unsubscribedSubscriber = Subscriber::factory()->unsubscribed()->create();

        $this->assertCount(1, Subscriber::withoutGlobalScopes()->active()->get());
        $this->assertCount(1, Subscriber::withoutGlobalScopes()->inactive()->get());
        $this->assertCount(1, Subscriber::withoutGlobalScopes()->unsubscribed()->get());
    }

    public function test_subscriber_business_methods(): void
    {
        $subscriber = Subscriber::factory()->active()->create();

        $this->assertTrue($subscriber->unsubscribe());
        $this->assertEquals('unsubscribed', $subscriber->fresh()->status);

        $this->assertTrue($subscriber->resubscribe());
        $this->assertEquals('active', $subscriber->fresh()->status);

        $this->assertTrue($subscriber->addInterest('new_interest'));
        $this->assertTrue($subscriber->hasInterest('new_interest'));

        $this->assertTrue($subscriber->removeInterest('new_interest'));
        $this->assertFalse($subscriber->hasInterest('new_interest'));
    }
}
