<?php

declare(strict_types=1);

use App\Filament\Resources\BrandResource\Pages\ListBrands;
use App\Filament\Resources\BrandResource\Tables\BrandsTable;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $admin = User::factory()->create([
        'email'    => 'info@egisstatyba.lt',
        'is_admin' => true,
    ]);

    $this->actingAs($admin);
});

it('allows bulk deleting brands from the list table', function (): void {
    $listPage = app(ListBrands::class);

    $table = BrandsTable::configure(Table::make($listPage));

    $bulkActions = $table->getBulkActions();

    expect($bulkActions)->not->toBeEmpty();
    expect($bulkActions[0])->toBeInstanceOf(BulkActionGroup::class);

    $actionNames = collect($bulkActions[0]->getActions())
        ->map(fn ($action) => $action->getName())
        ->all();

    expect($actionNames)->toContain('delete');
    expect($actionNames)->toContain('forceDelete');
    expect($actionNames)->toContain('restore');
});
