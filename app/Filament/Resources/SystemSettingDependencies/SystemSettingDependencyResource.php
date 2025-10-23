<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingDependencies;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\SystemSettingDependencies\Pages\CreateSystemSettingDependency;
use App\Filament\Resources\SystemSettingDependencies\Pages\EditSystemSettingDependency;
use App\Filament\Resources\SystemSettingDependencies\Pages\ListSystemSettingDependencies;
use App\Filament\Resources\SystemSettingDependencies\Schemas\SystemSettingDependencyForm;
use App\Filament\Resources\SystemSettingDependencies\Tables\SystemSettingDependenciesTable;
use App\Models\SystemSettingDependency;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class SystemSettingDependencyResource extends Resource
{
    use HasNav;

    protected static ?string $model = SystemSettingDependency::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return SystemSettingDependencyForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
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