<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingDependencies;
use App\Support\Concerns\HasNav;


use Filament\Schemas\Schema;
use App\Filament\Resources\SystemSettingDependencies\Pages\CreateSystemSettingDependency;
use App\Filament\Resources\SystemSettingDependencies\Pages\EditSystemSettingDependency;
use App\Filament\Resources\SystemSettingDependencies\Pages\ListSystemSettingDependencies;
use App\Filament\Resources\SystemSettingDependencies\Schemas\SystemSettingDependencyForm;
use App\Filament\Resources\SystemSettingDependencies\Tables\SystemSettingDependenciesTable;
use App\Models\SystemSettingDependency;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SystemSettingDependencyResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema   
    {
        return SystemSettingDependencyForm::configure($schema);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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
            'index'  => ListSystemSettingDependencies::route('/'),
            'create' => CreateSystemSettingDependency::route('/create'),
            'edit'   => EditSystemSettingDependency::route('/{record}/edit'),
        ];
    }
}