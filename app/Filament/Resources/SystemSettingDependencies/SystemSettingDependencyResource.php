<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingDependencies;

use App\Filament\Resources\SystemSettingDependencies\Pages\CreateSystemSettingDependency;
use UnitEnum;
use BackedEnum;
use App\Filament\Resources\SystemSettingDependencies\Pages\EditSystemSettingDependency;
use App\Filament\Resources\SystemSettingDependencies\Pages\ListSystemSettingDependencies;
use App\Filament\Resources\SystemSettingDependencies\Schemas\SystemSettingDependencyForm;
use App\Filament\Resources\SystemSettingDependencies\Tables\SystemSettingDependenciesTable;
use App\Models\SystemSettingDependency;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Form;

class SystemSettingDependencyResource extends Resource
{
    protected static ?string $model = SystemSettingDependency::class;

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        return SystemSettingDependencyForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return SystemSettingDependenciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSystemSettingDependencies::route('/'),
            'create' => CreateSystemSettingDependency::route('/create'),
            'edit' => EditSystemSettingDependency::route('/{record}/edit'),
        ];
    }
}
