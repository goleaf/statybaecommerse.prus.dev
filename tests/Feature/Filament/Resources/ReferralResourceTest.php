<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralResource;
use App\Filament\Resources\ReferralResource\Pages\CreateReferral;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('loads index page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this
        ->get(ReferralResource::getUrl('index'))
        ->assertOk();
});

it('loads create page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this
        ->get(ReferralResource::getUrl('create'))
        ->assertOk();
});

it('loads view and edit pages', function () {
    $user = User::factory()->create();
    $referrer = User::factory()->create();
    $referred = User::factory()->create();
    $this->actingAs($user);

    $referral = Referral::factory()->create([
        'referrer_id' => $referrer->id,
        'referred_id' => $referred->id,
        'referral_code' => 'CODE-12345',
        'status' => 'pending',
        'title' => 'Test Referral',
    ]);

    $this
        ->get(ReferralResource::getUrl('view', ['record' => $referral]))
        ->assertOk();

    $this
        ->get(ReferralResource::getUrl('edit', ['record' => $referral]))
        ->assertOk();
});

it('creates a referral via form action', function () {
    $user = User::factory()->create();
    $referrer = User::factory()->create();
    $referred = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(CreateReferral::class)
        ->fillForm([
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'referral_code' => 'CODE-ABC',
            'status' => 'pending',
            'title' => 'Nauja rekomendacija',
        ])
        ->call('create')
        ->assertHasNoErrors();

    expect(Referral::query()->where('referral_code', 'CODE-ABC')->exists())->toBeTrue();
});
