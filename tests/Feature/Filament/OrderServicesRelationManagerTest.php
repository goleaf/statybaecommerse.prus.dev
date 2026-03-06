<?php

declare(strict_types=1);

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\RelationManagers\ServicesRelationManager;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $admin = User::factory()->create([
        'email'    => 'info@egisstatyba.lt',
        'is_admin' => true,
    ]);

    $this->actingAs($admin);
});

it('registers the services relation manager on the order resource', function (): void {
    expect(OrderResource::getRelations())->toContain(ServicesRelationManager::class);
});

it('adds all active services to an order from the relation manager action', function (): void {
    $order = Order::factory()->create();

    $activeServices = Service::factory()->count(2)->create([
        'is_active' => true,
    ]);
    $inactiveService = Service::factory()->create([
        'is_active' => false,
    ]);

    $order->services()->attach($activeServices->first()->id, [
        'price'    => $activeServices->first()->price,
        'quantity' => 1,
    ]);

    livewire(ServicesRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->callTableAction('add_all_services');

    $serviceIds = $order->refresh()->services()->pluck('services.id')->all();

    expect($serviceIds)
        ->toHaveCount(2)
        ->toEqualCanonicalizing($activeServices->pluck('id')->all());

    expect($serviceIds)->not->toContain($inactiveService->id);
});
