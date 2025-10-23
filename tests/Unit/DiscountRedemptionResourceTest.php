<?php

declare(strict_types=1);

use App\Filament\Resources\DiscountRedemptionResource;
use Filament\Schemas\Schema;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

afterEach(function (): void {
    // Ensure Mockery expectations are cleaned up between tests for deterministic runs.
    \Mockery::close();
});

it('can create form', function (): void {
    $form = DiscountRedemptionResource::form(Schema::make());
    expect($form)->toBeInstanceOf(Schema::class);
});

it('can create table', function (): void {
    $table = DiscountRedemptionResource::table(Table::make(
        \Mockery::mock(HasTable::class)->shouldIgnoreMissing(),
    ));
    expect($table)->toBeInstanceOf(Table::class);
});

it('has correct model', function (): void {
    expect(DiscountRedemptionResource::getModel())->toBe(\App\Models\DiscountRedemption::class);
});

it('has correct navigation group', function (): void {
    // The resource now lives under the Discounts cluster to mirror Filament navigation.
    expect(DiscountRedemptionResource::getNavigationGroup())->toBe('Discounts');
});

it('has correct navigation icon', function (): void {
    expect(DiscountRedemptionResource::getNavigationIcon())->toBe('heroicon-o-receipt-percent');
});

it('has correct navigation sort', function (): void {
    expect(DiscountRedemptionResource::getNavigationSort())->toBeNull();
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
