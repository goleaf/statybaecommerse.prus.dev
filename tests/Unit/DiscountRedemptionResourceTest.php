<?php declare(strict_types=1);

use App\Filament\Resources\DiscountRedemptionResource;
use Filament\Forms\Form;
use Filament\Tables\Table;

it('can create form', function (): void {
    $form = DiscountRedemptionResource::form(Form::make());
    expect($form)->toBeInstanceOf(Form::class);
});

it('can create table', function (): void {
    $table = DiscountRedemptionResource::table(Table::make());
    expect($table)->toBeInstanceOf(Table::class);
});

it('has correct model', function (): void {
    expect(DiscountRedemptionResource::getModel())->toBe(\App\Models\DiscountRedemption::class);
});

it('has correct navigation group', function (): void {
    expect(DiscountRedemptionResource::getNavigationGroup())->toBe('Marketing');
});

it('has correct navigation icon', function (): void {
    expect(DiscountRedemptionResource::getNavigationIcon())->toBe('heroicon-o-ticket');
});

it('has correct navigation sort', function (): void {
    expect(DiscountRedemptionResource::getNavigationSort())->toBe(2);
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
    expect(DiscountRedemptionResource::getNavigationBadgeColor())->toBe('warning');
});

