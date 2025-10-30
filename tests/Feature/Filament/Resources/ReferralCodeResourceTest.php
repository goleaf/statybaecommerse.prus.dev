<?php

declare(strict_types=1);

use App\Filament\Resources\ReferralCodeResource;
use App\Filament\Resources\ReferralCodeResource\Pages\CreateReferralCode;
use App\Filament\Resources\ReferralCodeResource\Pages\ListReferralCodes;
use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Authenticate each scenario as the super admin expected by Filament policies.
    $this->admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $this->actingAs($this->admin);
});

it('feature: loads referral code index page', function (): void {
    // Confirm the list view mounts without authorization or boot issues.
    $this
        ->get(ReferralCodeResource::getUrl('index'))
        ->assertOk();
});

it('feature: loads referral code creation page', function (): void {
    // Ensure administrators can reach the creation form for new referral codes.
    $this
        ->get(ReferralCodeResource::getUrl('create'))
        ->assertOk();
});

it('feature: creates a referral code through the Livewire form', function (): void {
    // Provision a referrer so the user relationship select resolves an option.
    $referrer = User::factory()->create(['name' => 'Referral Owner']);

    // Submit the creation payload including translatable copy and structured metadata.
    Livewire::test(CreateReferralCode::class)
        ->fillForm([
            'user_id'       => $referrer->getKey(),
            'code'          => 'REF-CODE-2025',
            'title'         => ['en' => 'Invite bonus'],
            'description'   => ['en' => 'Share the code and earn rewards.'],
            'is_active'     => true,
            'expires_at'    => now()->addMonth()->format('Y-m-d'),
            'usage_limit'   => 5,
            'usage_count'   => 0,
            'reward_amount' => 10,
            'reward_type'   => 'fixed',
            'campaign_id'   => 'spring-campaign',
            'source'        => 'newsletter',
            'conditions'    => ['minimum_order' => '50'],
            'tags'          => ['segment' => 'vip'],
            'metadata'      => ['note' => 'created via test'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Assert the persisted referral code reflects the expected attributes and localization payload.
    $this->assertDatabaseHas('referral_codes', [
        'code'        => 'REF-CODE-2025',
        'user_id'     => $referrer->getKey(),
        'is_active'   => true,
        'reward_type' => 'fixed',
    ]);
});

it('feature: lists stored referral codes inside the table component', function (): void {
    // Seed a couple of records so the table renders realistic data points.
    $records = ReferralCode::factory()->count(2)->create();

    // Load the table and ensure both generated codes are visible to the administrator.
    Livewire::test(ListReferralCodes::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords($records);
});
