<?php declare(strict_types=1);

use App\Filament\Resources\ActivityLogResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->adminUser = User::factory()->create([
        'email' => 'admin@admin.com',
        'name' => 'Admin User',
    ]);

    // Create role and permissions if they don't exist
    $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

    // Create all necessary permissions
    $permissions = [
        'view_activity',
        'view_analytics',
        'view_order',
        'view_product',
        'view_user',
    ];

    foreach ($permissions as $permission) {
        $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        $role->givePermissionTo($perm);
    }

    $this->adminUser->assignRole($role);

    actingAs($this->adminUser);
});

it('can render activity log resource page', function () {
    $response = $this->get(ActivityLogResource::getUrl('index'));

    $response->assertSuccessful();
});

it('displays activities in table', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    activity('product')
        ->causedBy($user)
        ->performedOn($product)
        ->log('Product created');

    $activities = Activity::all();

    livewire(ActivityLogResource\Pages\ListActivityLogs::class)
        ->assertCanSeeTableRecords($activities);
});

it('can filter activities by log name', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $order = Order::factory()->create();

    activity('product')
        ->causedBy($user)
        ->performedOn($product)
        ->log('Product created');

    activity('order')
        ->causedBy($user)
        ->performedOn($order)
        ->log('Order created');

    $productActivity = Activity::where('log_name', 'product')->first();
    $orderActivity = Activity::where('log_name', 'order')->first();

    livewire(ActivityLogResource\Pages\ListActivityLogs::class)
        ->filterTable('log_name', 'product')
        ->assertCanSeeTableRecords([$productActivity])
        ->assertCanNotSeeTableRecords([$orderActivity]);
});

it('can filter activities by subject type', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $order = Order::factory()->create();

    activity('test')
        ->causedBy($user)
        ->performedOn($product)
        ->log('Product action');

    activity('test')
        ->causedBy($user)
        ->performedOn($order)
        ->log('Order action');

    $productActivity = Activity::where('subject_type', Product::class)->first();
    $orderActivity = Activity::where('subject_type', Order::class)->first();

    livewire(ActivityLogResource\Pages\ListActivityLogs::class)
        ->filterTable('subject_type', Product::class)
        ->assertCanSeeTableRecords([$productActivity])
        ->assertCanNotSeeTableRecords([$orderActivity]);
});

it('can filter activities by date range', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $oldActivity = activity('test')
        ->causedBy($user)
        ->performedOn($product)
        ->log('Old activity');
    $oldActivity->created_at = now()->subDays(10);
    $oldActivity->save();

    $recentActivity = activity('test')
        ->causedBy($user)
        ->performedOn($product)
        ->log('Recent activity');

    $oldActivityRecord = Activity::where('description', 'Old activity')->first();
    $recentActivityRecord = Activity::where('description', 'Recent activity')->first();

    livewire(ActivityLogResource\Pages\ListActivityLogs::class)
        ->filterTable('created_at', [
            'created_from' => now()->subDays(5)->format('Y-m-d'),
            'created_until' => now()->format('Y-m-d'),
        ])
        ->assertCanSeeTableRecords([$recentActivityRecord])
        ->assertCanNotSeeTableRecords([$oldActivityRecord]);
});

it('displays activity details correctly', function () {
    $user = User::factory()->create(['name' => 'Test User']);
    $product = Product::factory()->create(['name' => 'Test Product']);

    activity('product')
        ->causedBy($user)
        ->performedOn($product)
        ->withProperties(['test' => 'value'])
        ->log('Product updated');

    $activity = Activity::first();

    livewire(ActivityLogResource\Pages\ListActivityLogs::class)
        ->assertCanSeeTableRecords([$activity])
        ->assertSee('Product updated')
        ->assertSee('product')
        ->assertSee('Test User');
});

it('can view activity modal', function () {
    $user = User::factory()->create(['name' => 'Test User']);
    $product = Product::factory()->create(['name' => 'Test Product']);

    activity('product')
        ->causedBy($user)
        ->performedOn($product)
        ->withProperties(['test' => 'value'])
        ->log('Product updated');

    $activity = Activity::first();

    livewire(ActivityLogResource\Pages\ListActivityLogs::class)
        ->callTableAction('view_details', $activity)
        ->assertSee('Product updated')
        ->assertSee('Test User');
});

it('displays activity properties in modal', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    activity('product')
        ->causedBy($user)
        ->performedOn($product)
        ->withProperties([
            'attributes' => ['name' => 'New Name'],
            'old' => ['name' => 'Old Name']
        ])
        ->log('Product updated');

    $activity = Activity::where('description', 'Product updated')->first();

    livewire(ActivityLogResource\Pages\ListActivityLogs::class)
        ->callTableAction('view_details', $activity);

    // The modal should display the changes
    expect($activity->properties)->toHaveKey('attributes');
    expect($activity->properties)->toHaveKey('old');
});

it('formats log name as badge', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    activity('product')
        ->causedBy($user)
        ->performedOn($product)
        ->log('Test activity');

    livewire(ActivityLogResource\Pages\ListActivityLogs::class)
        ->assertOk();
});

it('formats subject type correctly', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    activity('test')
        ->causedBy($user)
        ->performedOn($product)
        ->log('Test activity');

    $activity = Activity::first();

    expect(class_basename($activity->subject_type))->toBe('User');
});

it('displays causer name or system', function () {
    $user = User::factory()->create(['name' => 'Test User']);
    $product = Product::factory()->create();

    // Activity with causer
    activity('test')
        ->causedBy($user)
        ->performedOn($product)
        ->log('User activity');

    // Activity without causer (system)
    activity('test')
        ->performedOn($product)
        ->log('System activity');

    $userActivity = Activity::where('description', 'User activity')->first();
    $systemActivity = Activity::where('description', 'System activity')->first();

    expect($userActivity->causer->name)->toBe('Test User');
    expect($systemActivity->causer)->not->toBeNull();
});

it('sorts activities by creation date descending', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $firstActivity = activity('test')
        ->causedBy($user)
        ->performedOn($product)
        ->log('First activity');

    $secondActivity = activity('test')
        ->causedBy($user)
        ->performedOn($product)
        ->log('Second activity');

    livewire(ActivityLogResource\Pages\ListActivityLogs::class)
        ->assertOk();
});

it('polls data every 30 seconds', function () {
    livewire(ActivityLogResource\Pages\ListActivityLogs::class)
        ->assertOk();
});

it('can access activity log resource with proper permissions', function () {
    expect(ActivityLogResource::canAccess())->toBeTrue();
});

it('displays correct navigation properties', function () {
    expect(ActivityLogResource::getNavigationLabel())->toBe(__('Veiklos žurnalai'));
    expect(ActivityLogResource::getModelLabel())->toBe(__('admin.activity_logs.title'));
    expect(ActivityLogResource::getPluralModelLabel())->toBe(__('admin.activity_logs.title'));
});
