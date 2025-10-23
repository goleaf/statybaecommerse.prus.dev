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
use Filament\Forms\Form;
use Filament\Resources\Resource;
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

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return SystemSettingDependencyForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
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
