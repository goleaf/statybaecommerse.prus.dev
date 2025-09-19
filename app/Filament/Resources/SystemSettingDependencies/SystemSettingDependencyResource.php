<?php

namespace App\Filament\Resources\SystemSettingDependencies;
use App\Filament\Resources\SystemSettingDependencies\Pages\CreateSystemSettingDependency;
use App\Filament\Resources\SystemSettingDependencies\Pages\EditSystemSettingDependency;
use App\Filament\Resources\SystemSettingDependencies\Pages\ListSystemSettingDependencies;
use App\Filament\Resources\SystemSettingDependencies\Schemas\SystemSettingDependencyForm;
use App\Filament\Resources\SystemSettingDependencies\Tables\SystemSettingDependenciesTable;
use App\Models\SystemSettingDependency;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
class SystemSettingDependencyResource extends Resource
{
    protected static ?string $model = SystemSettingDependency::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function form(Schema $schema): Schema
    {
        return SystemSettingDependencyForm::configure($schema);
    }
    public static function table(Table $table): Table
        return SystemSettingDependenciesTable::configure($table);
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => ListSystemSettingDependencies::route('/'),
            'create' => CreateSystemSettingDependency::route('/create'),
            'edit' => EditSystemSettingDependency::route('/{record}/edit'),
}
