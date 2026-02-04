<?php

declare(strict_types=1);

use App\Filament\Resources\Partners\Pages\EditPartner;
use App\Filament\Resources\Partners\RelationManagers\UsersRelationManager;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('creates partner users with a password from the relation manager', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $partner = Partner::factory()->create();

    $password = 'Admin123!';
    $email = 'partner.user@example.test';

    livewire(UsersRelationManager::class, [
        'ownerRecord' => $partner,
        'pageClass'   => EditPartner::class,
    ])
        ->callTableAction('create', null, [
            'name'     => 'Partner User',
            'email'    => $email,
            'password' => $password,
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'email' => $email,
        'name'  => 'Partner User',
    ]);

    $user = User::where('email', $email)->first();

    expect($user)->not->toBeNull();
    expect(Hash::check($password, $user->password))->toBeTrue();

    $this->assertDatabaseHas('partner_users', [
        'partner_id' => $partner->id,
        'user_id'    => $user->id,
    ]);
});
