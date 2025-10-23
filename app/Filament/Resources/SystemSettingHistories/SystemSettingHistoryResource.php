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
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class SystemSettingHistoryResource extends Resource
{
    use HasNav;

    protected static ?string $model = SystemSettingHistory::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 13;

    protected static ?string $recordTitleAttribute = 'change_reason';

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Settings';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.system_setting_histories.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.system_setting_histories.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.system_setting_histories.model_label');
    }

    public static function form(Form $form): Form|array
    {
        return SystemSettingHistoryForm::configure($form);
    }

    public static function table(Table $table): Table|array
    {
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
