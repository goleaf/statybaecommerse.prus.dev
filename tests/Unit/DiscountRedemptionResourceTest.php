<?php

declare(strict_types=1);

use App\Filament\Resources\DiscountRedemptionResource;
use App\Support\Nav;
use Filament\Forms\Form;
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

it('can create form', function (): void {
    $form = DiscountRedemptionResource::form(Form::make());
    expect($form)->toBeInstanceOf(Form::class);
});

it('can create table', function (): void {
    $table = DiscountRedemptionResource::table(makeTestTable());
    expect($table)->toBeInstanceOf(Table::class);
});

it('has correct model', function (): void {
    expect(DiscountRedemptionResource::getModel())->toBe(\App\Models\DiscountRedemption::class);
});

it('has correct navigation group', function (): void {
    expect(DiscountRedemptionResource::getNavigationGroup())->toBe(
        Nav::groupForResource(DiscountRedemptionResource::class)
    );
});

it('has correct navigation icon', function (): void {
    expect(DiscountRedemptionResource::getNavigationIcon())->toBe('heroicon-o-receipt-percent');
});

it('has correct navigation sort', function (): void {
    expect(DiscountRedemptionResource::getNavigationSort())->toBe(
        Nav::sortForResource(DiscountRedemptionResource::class)
    );
});

it('has correct pages', function (): void {
    $pages = DiscountRedemptionResource::getPages();
    expect($pages)->toHaveKey('index');
    expect($pages)->toHaveKey('create');
    expect($pages)->toHaveKey('view');
    expect($pages)->toHaveKey('edit');
});

it('has correct relations', function (): void {
    $relations = DiscountRedemptionResource::getRelations();
    expect($relations)->toBeArray();
});

it('has navigation badge', function (): void {
    expect(DiscountRedemptionResource::getNavigationBadge())->toBeNull();
});

it('has navigation badge color', function (): void {
    expect(DiscountRedemptionResource::getNavigationBadgeColor())->toBeNull();
});
