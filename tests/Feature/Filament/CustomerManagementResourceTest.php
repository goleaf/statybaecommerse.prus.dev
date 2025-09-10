<?php declare(strict_types=1);

use App\Filament\Resources\CustomerManagementResource;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->adminUser = User::factory()->create([
        'email' => 'admin@example.com',
        'is_admin' => true,
    ]);
    $this->actingAs($this->adminUser);
});

it('mounts the customer management index page', function (): void {
    $this->get(CustomerManagementResource::getUrl('index'))
        ->assertOk();
});

it('lists non-admin customers in table', function (): void {
    $customers = User::factory()->count(3)->create(['is_admin' => false]);
    $admins = User::factory()->count(2)->create(['is_admin' => true]);

    Livewire::test(CustomerManagementResource\Pages\ListCustomerManagement::class)
        ->assertCanSeeTableRecords($customers)
        ->assertCanNotSeeTableRecords($admins);
});
