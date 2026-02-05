<?php

declare(strict_types=1);

use App\Filament\Resources\ProductResource\Tables\ProductsTable;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

function makeProductsTable(): Table
{
    $component = new class implements HasTable
    {
        use InteractsWithTable;

        public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
        {
            return null;
        }
    };

    return Table::make($component);
}

it('unit: configures expected pagination options for the products table', function (): void {
    $table = ProductsTable::configure(makeProductsTable());

    expect($table->getPaginationPageOptions())->toBe([
        10,
        20,
        50,
        100,
        150,
        200,
        300,
        400,
        500,
        600,
        700,
        800,
        900,
        1000,
        1500,
        2000,
        3000,
        4000,
        5000,
        6000,
        7000,
        8000,
        9000,
        10000,
    ]);
});
