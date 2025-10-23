<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingHistories;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\SystemSettingHistories\Pages\CreateSystemSettingHistory;
use App\Filament\Resources\SystemSettingHistories\Pages\EditSystemSettingHistory;
use App\Filament\Resources\SystemSettingHistories\Pages\ListSystemSettingHistories;
use App\Filament\Resources\SystemSettingHistories\Pages\ViewSystemSettingHistory;
use App\Filament\Resources\SystemSettingHistories\Schemas\SystemSettingHistoryForm;
use App\Filament\Resources\SystemSettingHistories\Tables\SystemSettingHistoriesTable;
use App\Models\SystemSettingHistory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class SystemSettingHistoryResource extends Resource
{
    use HasNav;

    protected static ?string $model = SystemSettingHistory::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return SystemSettingHistoryForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return SystemSettingHistoriesTable::configure($table);
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
            'index'  => ListSystemSettingHistories::route('/'),
            'create' => CreateSystemSettingHistory::route('/create'),
            'view' => ViewSystemSettingHistory::route('/{record}'),
            'edit' => EditSystemSettingHistory::route('/{record}/edit'),
        ];
    }
}
