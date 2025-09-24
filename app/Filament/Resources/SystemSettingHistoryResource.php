<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingHistoryResource\Pages;
use App\Models\SystemSettingHistory;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

final class SystemSettingHistoryResource extends Resource
{
    protected static ?string $model = SystemSettingHistory::class;
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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('admin.system_setting_histories.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('system_setting_id')
                                    ->label(__('admin.system_setting_histories.system_setting'))
                                    ->relationship('systemSetting', 'key')
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->searchable()
                                    ->preload(),
                                Select::make('changed_by')
                                    ->label(__('admin.system_setting_histories.changed_by'))
                                    ->relationship('user', 'name')
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('change_reason')
                                    ->label(__('admin.system_setting_histories.change_reason'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('ip_address')
                                    ->label(__('admin.system_setting_histories.ip_address'))
                                    ->ip()
                                    ->maxLength(45),
                                TextInput::make('user_agent')
                                    ->label(__('admin.system_setting_histories.user_agent'))
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ]),
                        Textarea::make('old_value')
                            ->label(__('admin.system_setting_histories.old_value'))
                            ->rows(3)
                            ->helperText(__('admin.system_setting_histories.old_value_help'))
                            ->columnSpanFull(),
                        Textarea::make('new_value')
                            ->label(__('admin.system_setting_histories.new_value'))
                            ->rows(3)
                            ->helperText(__('admin.system_setting_histories.new_value_help'))
                            ->columnSpanFull(),
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
                    ->copyable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('user.name')
                    ->label(__('admin.system_setting_histories.changed_by'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
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
                    })
                    ->toggleable(),
                TextColumn::make('new_value')
                    ->label(__('admin.system_setting_histories.new_value'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->label(__('admin.system_setting_histories.ip_address'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('system_setting_id')
                    ->label(__('admin.system_setting_histories.system_setting'))
                    ->relationship('systemSetting', 'key')
                    ->searchable(),
                SelectFilter::make('changed_by')
                    ->label(__('admin.system_setting_histories.changed_by'))
                    ->relationship('user', 'name')
                    ->searchable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('restore_value')
                    ->label(__('admin.system_setting_histories.restore_value'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->action(function (SystemSettingHistory $record): void {
                        $record->systemSetting()->update([
                            'type' => 'string',
                            'value' => $record->old_value,
                        ]);
                        Notification::make()
                            ->title(__('admin.system_setting_histories.value_restored_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn(SystemSettingHistory $record): bool => !empty($record->old_value)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('export_history')
                        ->label(__('admin.system_setting_histories.export_history'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Export logic here
                            Notification::make()
                                ->title(__('admin.system_setting_histories.exported_successfully'))
                                ->success()
                                ->send();
                        }),
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
