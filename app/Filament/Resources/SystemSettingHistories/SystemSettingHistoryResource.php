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
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class SystemSettingHistoryResource extends Resource
{
    use HasNav;

    protected static ?string $model = SystemSettingHistory::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        // Ensure compatibility with the Schema-based form builder introduced in Filament v4.
        return SystemSettingHistoryForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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
            'edit'   => EditSystemSettingHistory::route('/{record}/edit'),
        ];
    }
}