<?php declare(strict_types=1);

use App\Filament\Resources\CouponResource;
use Illuminate\Support\Str;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('renders coupon resource index page', function () {
    $this->get(CouponResource::getUrl('index'))->assertSuccessful();
});

it('lists coupons in table', function () {
    $coupons = collect(range(1, 3))->map(function () {
        return \App\Models\Coupon::create([
            'code' => Str::upper(Str::random(8)),
            'name' => 'Test ' . Str::random(5),
            'description' => 'Desc',
            'type' => 'fixed',
            'value' => 10,
            'minimum_amount' => 0,
            'usage_limit' => null,
            'used_count' => 0,
            'is_active' => true,
        ]);
    });

    Livewire::test(CouponResource\Pages\ListCoupons::class)
        ->assertCanSeeTableRecords($coupons);
});

it('renders coupon create page', function () {
    $this->get(CouponResource::getUrl('create'))->assertSuccessful();
});

it('renders coupon view and edit pages', function () {
    $coupon = \App\Models\Coupon::create([
        'code' => Str::upper(Str::random(8)),
        'name' => 'Test',
        'description' => 'Desc',
        'type' => 'fixed',
        'value' => 5,
        'minimum_amount' => 0,
        'usage_limit' => null,
        'used_count' => 0,
        'is_active' => true,
    ]);

    $this->get(CouponResource::getUrl('view', ['record' => $coupon]))->assertSuccessful();
    $this->get(CouponResource::getUrl('edit', ['record' => $coupon]))->assertSuccessful();
});


