<?php

declare(strict_types=1);

use App\Filament\Resources\DiscountRedemptionResource;
use Filament\Schemas\Schema;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

function makeTestTable(): Table
{
    // Provide a minimal HasTable implementation so Filament can construct the table during the unit test.
    $component = new class implements HasTable
    {
        use InteractsWithTable;

        public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
        {
            // Return null because translation handling is irrelevant for these simple unit assertions.
            return null;
        }
    };

    return Table::make($component);
}

it('unit: can create form', function (): void {
    $form = DiscountRedemptionResource::form(Schema::make());
    expect($form)->toBeInstanceOf(Schema::class);
});

it('unit: can create table', function (): void {
    $table = DiscountRedemptionResource::table(makeTestTable());
    expect($table)->toBeInstanceOf(Table::class);
});

it('unit: has correct model', function (): void {
    expect(DiscountRedemptionResource::getModel())->toBe(\App\Models\DiscountRedemption::class);
});

it('unit: has correct navigation group', function (): void {
    // The resource now lives under the Discounts cluster to mirror Filament navigation.
    expect(DiscountRedemptionResource::getNavigationGroup())->toBe('Discounts');
});

it('unit: has correct navigation icon', function (): void {
    expect(DiscountRedemptionResource::getNavigationIcon())->toBe('heroicon-o-receipt-percent');
});

it('unit: has correct navigation sort', function (): void {
    expect(DiscountRedemptionResource::getNavigationSort())->toBeNull();
});

it('unit: has correct pages', function (): void {
    $pages = DiscountRedemptionResource::getPages();
    expect($pages)->toHaveKey('index');
    expect($pages)->toHaveKey('create');
    expect($pages)->toHaveKey('view');
    expect($pages)->toHaveKey('edit');
});

it('unit: has correct relations', function (): void {
    $relations = DiscountRedemptionResource::getRelations();
    expect($relations)->toBeArray();
});

it('unit: has navigation badge', function (): void {
    expect(DiscountRedemptionResource::getNavigationBadge())->toBeNull();
});

it('unit: has navigation badge color', function (): void {
    expect(DiscountRedemptionResource::getNavigationBadgeColor())->toBeNull();
});
