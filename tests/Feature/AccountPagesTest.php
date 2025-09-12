<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('loads account dashboard', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    actingAs($user);

    $this->get(route('account.index'))
        ->assertOk()
        ->assertSee(__('Overview'));
});

it('loads account subpages', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    actingAs($user);

    $this->get(route('account.profile'))->assertOk();
    $this->get(route('account.addresses'))->assertOk();
    $this->get(route('account.orders'))->assertOk();
    $this->get(route('account.reviews'))->assertOk();
    $this->get(route('account.wishlist'))->assertOk();
    $this->get(route('account.documents'))->assertOk();
    $this->get(route('account.notifications'))->assertOk();
});


