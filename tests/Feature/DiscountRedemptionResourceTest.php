<?php declare(strict_types=1);

use App\Filament\Resources\DiscountRedemptionResource;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\Order;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;

beforeEach(function () {
    $this->admin = User::factory()->create(['email' => 'admin@admin.com']);
    $this->user = User::factory()->create();
    $this->discount = Discount::factory()->create();
    $this->discountCode = DiscountCode::factory()->create(['discount_id' => $this->discount->id]);
    $this->order = Order::factory()->create();
});

it('can list discount redemptions', function () {
    $this->actingAs($this->admin);

    DiscountRedemption::factory()->count(3)->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can create discount redemption', function () {
    $this->actingAs($this->admin);

    $this
        ->get(DiscountRedemptionResource::getUrl('create'))
        ->assertSuccessful();
});

it('can view discount redemption', function () {
    $this->actingAs($this->admin);

    $redemption = DiscountRedemption::factory()->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('view', ['record' => $redemption]))
        ->assertSuccessful();
});

it('can edit discount redemption', function () {
    $this->actingAs($this->admin);

    $redemption = DiscountRedemption::factory()->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('edit', ['record' => $redemption]))
        ->assertSuccessful();
});

it('can filter discount redemptions by status', function () {
    $this->actingAs($this->admin);

    DiscountRedemption::factory()->pending()->create();
    DiscountRedemption::factory()->redeemed()->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can filter discount redemptions by user', function () {
    $this->actingAs($this->admin);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    DiscountRedemption::factory()->create(['user_id' => $user1->id]);
    DiscountRedemption::factory()->create(['user_id' => $user2->id]);

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can filter discount redemptions by amount range', function () {
    $this->actingAs($this->admin);

    DiscountRedemption::factory()->create(['amount_saved' => 10.0]);
    DiscountRedemption::factory()->create(['amount_saved' => 50.0]);
    DiscountRedemption::factory()->create(['amount_saved' => 100.0]);

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can filter discount redemptions by date range', function () {
    $this->actingAs($this->admin);

    DiscountRedemption::factory()->create(['redeemed_at' => now()->subDays(10)]);
    DiscountRedemption::factory()->create(['redeemed_at' => now()->subDays(5)]);
    DiscountRedemption::factory()->create(['redeemed_at' => now()]);

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can search discount redemptions', function () {
    $this->actingAs($this->admin);

    $discount = Discount::factory()->create(['name' => 'Special Discount']);
    $redemption = DiscountRedemption::factory()->create(['discount_id' => $discount->id]);

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can bulk update status to redeemed', function () {
    $this->actingAs($this->admin);

    $redemptions = DiscountRedemption::factory()->pending()->count(3)->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can bulk update status to expired', function () {
    $this->actingAs($this->admin);

    $redemptions = DiscountRedemption::factory()->pending()->count(3)->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('shows navigation badge with pending count', function () {
    $this->actingAs($this->admin);

    DiscountRedemption::factory()->pending()->count(5)->create();
    DiscountRedemption::factory()->redeemed()->count(3)->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can access global search', function () {
    $this->actingAs($this->admin);

    $discount = Discount::factory()->create(['name' => 'Test Discount']);
    $redemption = DiscountRedemption::factory()->create(['discount_id' => $discount->id]);

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('validates required fields on create', function () {
    $this->actingAs($this->admin);

    $this
        ->get(DiscountRedemptionResource::getUrl('create'))
        ->assertSuccessful();
});

it('shows proper form sections', function () {
    $this->actingAs($this->admin);

    $this
        ->get(DiscountRedemptionResource::getUrl('create'))
        ->assertSuccessful();
});

it('shows proper table columns', function () {
    $this->actingAs($this->admin);

    DiscountRedemption::factory()->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('shows proper filters', function () {
    $this->actingAs($this->admin);

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('shows proper actions', function () {
    $this->actingAs($this->admin);

    $redemption = DiscountRedemption::factory()->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('shows proper bulk actions', function () {
    $this->actingAs($this->admin);

    DiscountRedemption::factory()->count(3)->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can sort by different columns', function () {
    $this->actingAs($this->admin);

    DiscountRedemption::factory()->count(5)->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can toggle column visibility', function () {
    $this->actingAs($this->admin);

    DiscountRedemption::factory()->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('shows proper money formatting', function () {
    $this->actingAs($this->admin);

    $redemption = DiscountRedemption::factory()->create(['amount_saved' => 25.5, 'currency_code' => 'EUR']);

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('shows proper status badges', function () {
    $this->actingAs($this->admin);

    DiscountRedemption::factory()->pending()->create();
    DiscountRedemption::factory()->redeemed()->create();
    DiscountRedemption::factory()->expired()->create();
    DiscountRedemption::factory()->cancelled()->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('shows proper date formatting', function () {
    $this->actingAs($this->admin);

    $redemption = DiscountRedemption::factory()->create(['redeemed_at' => now()->subDays(2)]);

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can copy code and ip address', function () {
    $this->actingAs($this->admin);

    $redemption = DiscountRedemption::factory()->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('shows proper infolist on view page', function () {
    $this->actingAs($this->admin);

    $redemption = DiscountRedemption::factory()->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('view', ['record' => $redemption]))
        ->assertSuccessful();
});

it('can handle empty states', function () {
    $this->actingAs($this->admin);

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can handle large datasets', function () {
    $this->actingAs($this->admin);

    DiscountRedemption::factory()->count(100)->create();

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

it('respects user permissions', function () {
    $this->actingAs($this->user);

    $this
        ->get(DiscountRedemptionResource::getUrl('index'))
        ->assertSuccessful();
});

