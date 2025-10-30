<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralResource;
use App\Filament\Resources\ReferralResource\Pages\ListReferrals;
use App\Filament\Resources\ReferralResource\Pages\CreateReferral;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::query()->firstOrCreate([
        'name'       => 'view notifications',
        'guard_name' => 'web',
    ]);
});

it('feature: loads index page', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);

    $this
        ->get(ReferralResource::getUrl('index'))
        ->assertOk();
});

it('feature: loads create page', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);

    $this
        ->get(ReferralResource::getUrl('create'))
        ->assertOk();
});

it('feature: loads view and edit pages', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $referrer = User::factory()->create();
    $referred = User::factory()->create();
    $this->actingAs($user);

    $referral = Referral::factory()->create([
        'referrer_id'   => $referrer->id,
        'referred_id'   => $referred->id,
        'referral_code' => 'CODE-12345',
        'status'        => 'pending',
        'title'         => 'Test Referral',
    ]);

    $this
        ->get(ReferralResource::getUrl('view', ['record' => $referral->getRouteKey()]))
        ->assertOk();

    $this
        ->get(ReferralResource::getUrl('edit', ['record' => $referral->getRouteKey()]))
        ->assertOk();
});

it('feature: creates a referral via form action', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $referrer = User::factory()->create();
    $referred = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(CreateReferral::class)
        ->fillForm([
            'referrer_id'   => $referrer->id,
            'referred_id'   => $referred->id,
            'referral_code' => 'CODE-ABC',
            'status'        => 'pending',
            'title'         => 'Nauja rekomendacija',
        ])
        ->call('create')
        ->assertHasNoErrors();

    expect(Referral::query()->where('referral_code', 'CODE-ABC')->exists())->toBeTrue();
});

it('feature: lists referrals via the Filament table component', function (): void {
    // Ensure Filament loads the admin panel so Livewire table helpers resolve correctly.
    test()->resolveAdminPanel();

    // Authenticate as an administrator capable of accessing the referral management pages.
    $admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $this->actingAs($admin);

    // Normalise existing fixtures so relationship filter options never receive null labels.
    Referral::query()->whereNull('source')->update(['source' => 'unspecified']);
    Referral::query()->whereNull('campaign')->update(['campaign' => 'general']);

    // Seed a referral with deterministic titles so the table has a predictable record to display.
    $referral = Referral::factory()->create([
        'source'   => 'direct',
        'campaign' => 'coverage-program',
        'title' => [
            'en' => 'Coverage Referral Program',
            'lt' => 'Padengimo rekomendacijų programa',
        ],
    ]);

    // Hydrate the table data prior to asserting the seeded referral is visible.
    Livewire::actingAs($admin)
        ->test(ListReferrals::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$referral]);
});
