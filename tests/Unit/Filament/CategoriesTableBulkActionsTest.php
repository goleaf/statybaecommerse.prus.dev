<?php

declare(strict_types=1);

use App\Filament\Resources\CategoryResource\Tables\CategoriesTable;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

function makeCategoriesTable(): Table
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

it('enables bulk selection for categories', function (): void {
    $table = CategoriesTable::configure(makeCategoriesTable());

    expect($table->hasBulkAction('delete'))->toBeTrue();
});
