<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SystemSettingHistoryResource\Pages;
use App\Models\SystemSettingHistory;
use App\Models\SystemSetting;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * SystemSettingHistoryResource
 *
 * Filament v4 resource for SystemSettingHistory management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SystemSettingHistoryResource extends Resource
{
    protected static ?string $model = SystemSettingHistory::class;
    protected static ?int $navigationSort = 13;
    protected static ?string $recordTitleAttribute = 'change_reason';
    protected static ?string $navigationGroup = NavigationGroup::Settings;

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Settings;

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

    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.system_setting_histories.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('system_setting_id')
                                    ->label(__('admin.system_setting_histories.system_setting'))
                                    ->options(SystemSetting::pluck('key', 'id'))
                                    ->required()
                                    ->searchable(),

                                Select::make('changed_by')
                                    ->label(__('admin.system_setting_histories.changed_by'))
                                    ->options(User::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),

                                TextInput::make('change_reason')
                                    ->label(__('admin.system_setting_histories.change_reason'))
                                    ->maxLength(255),

                                TextInput::make('ip_address')
                                    ->label(__('admin.system_setting_histories.ip_address'))
                                    ->ip()
                                    ->maxLength(45),

                                TextInput::make('user_agent')
                                    ->label(__('admin.system_setting_histories.user_agent'))
                                    ->maxLength(500),
                            ]),

                        Textarea::make('old_value')
                            ->label(__('admin.system_setting_histories.old_value'))
                            ->rows(3)
                            ->helperText(__('admin.system_setting_histories.old_value_help')),

                        Textarea::make('new_value')
                            ->label(__('admin.system_setting_histories.new_value'))
                            ->rows(3)
                            ->helperText(__('admin.system_setting_histories.new_value_help')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('systemSetting.key')
                    ->label(__('admin.system_setting_histories.system_setting'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('user.name')
                    ->label(__('admin.system_setting_histories.changed_by'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('change_reason')
                    ->label(__('admin.system_setting_histories.change_reason'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('old_value')
                    ->label(__('admin.system_setting_histories.old_value'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                TextColumn::make('new_value')
                    ->label(__('admin.system_setting_histories.new_value'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                TextColumn::make('ip_address')
                    ->label(__('admin.system_setting_histories.ip_address'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('system_setting_id')
                    ->label(__('admin.system_setting_histories.system_setting'))
                    ->options(SystemSetting::pluck('key', 'id'))
                    ->searchable(),

                SelectFilter::make('changed_by')
                    ->label(__('admin.system_setting_histories.changed_by'))
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSystemSettingHistories::route('/'),
            'create' => Pages\CreateSystemSettingHistory::route('/create'),
            'view' => Pages\ViewSystemSettingHistory::route('/{record}'),
            'edit' => Pages\EditSystemSettingHistory::route('/{record}/edit'),
        ];
    }
}
