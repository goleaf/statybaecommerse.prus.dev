<?php

declare(strict_types=1);

use App\Filament\Resources\CustomerGroups\Pages\ViewCustomerGroup;
use App\Filament\Resources\CustomerGroups\RelationManagers\UsersRelationManager;
use App\Filament\Resources\Users\UserResource;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('links customer group users to the user edit page', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $this->actingAs($admin);

    $group = CustomerGroup::factory()->create();
    $member = User::factory()->create([
        'is_admin' => false,
    ]);

    $group->users()->attach($member->getKey(), [
        'assigned_at' => now(),
    ]);

    $expectedUrl = UserResource::getUrl('edit', ['record' => $member]);

    livewire(UsersRelationManager::class, [
        'ownerRecord' => $group,
        'pageClass'   => ViewCustomerGroup::class,
    ])
        ->assertSuccessful()
        ->assertSee($expectedUrl, false);
});
