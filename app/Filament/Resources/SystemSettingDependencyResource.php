<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use Filament\Schemas\Schema;
use App\Filament\Resources\SystemSettingDependencyResource\Pages;
use App\Models\SystemSetting;
use App\Models\SystemSettingDependency;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

final class SystemSettingDependencyResource extends Resource
{
    protected static ?string $model = SystemSettingDependency::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-link';
    }

    

    

    public static function getNavigationLabel(): string
    {
        return __('admin.system_setting_dependencies.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.system_setting_dependencies.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.system_setting_dependencies.model_label');
    }

    public static function form(Schema $schema): Schema   
    {
        return $schema->schema([
            SchemaSection::make(__('admin.system_setting_dependencies.basic_information'))
                ->description(__('admin.system_setting_dependencies.basic_information_description'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('setting_id')
                                ->label(__('admin.system_setting_dependencies.setting'))
                                ->options(fn () => SystemSetting::withoutGlobalScopes()->pluck('key', 'id')->all())
                                ->rules([Rule::exists('system_settings', 'id')])
                                ->required()
                                ->searchable()
                                ->preload()
                                ->helperText(__('admin.system_setting_dependencies.setting_help')),
                            Select::make('depends_on_setting_id')
                                ->label(__('admin.system_setting_dependencies.depends_on_setting'))
                                ->options(fn () => SystemSetting::withoutGlobalScopes()->pluck('key', 'id')->all())
                                ->rules([Rule::exists('system_settings', 'id')])
                                ->required()
                                ->searchable()
                                ->preload()
                                ->helperText(__('admin.system_setting_dependencies.depends_on_setting_help')),
                        ]),
                    // Allow operators and value-based conditions to be configured separately for clarity.
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('condition')
                                ->label(__('admin.system_setting_dependencies.condition'))
                                ->options([
                                    'equals' => __('admin.system_settings.equals'),
                                    'not_equals' => __('admin.system_settings.not_equals'),
                                    'greater_than' => __('admin.system_settings.greater_than'),
                                    'less_than' => __('admin.system_settings.less_than'),
                                    'contains' => __('admin.system_settings.contains'),
                                    'not_contains' => __('admin.system_settings.not_contains'),
                                    'is_empty' => __('admin.system_settings.is_empty'),
                                    'is_not_empty' => __('admin.system_settings.is_not_empty'),
                                    'is_true' => __('admin.system_settings.is_true'),
                                    'is_false' => __('admin.system_settings.is_false'),
                                ])
                                ->required()
                                ->native(false)
                                ->helperText(__('admin.system_setting_dependencies.condition_help')),
                            TextInput::make('condition_value')
                                ->label(__('admin.system_settings.condition_value'))
                                ->maxLength(255)
                                ->nullable()
                                ->required(fn (Get $get): bool => $get('condition') !== null && ! in_array($get('condition'), [
                                    'is_empty',
                                    'is_not_empty',
                                    'is_true',
                                    'is_false',
                                ], true))
                                ->hidden(fn (Get $get): bool => in_array($get('condition'), [
                                    'is_empty',
                                    'is_not_empty',
                                    'is_true',
                                    'is_false',
                                ], true)),
                        ])
                        ->columnSpanFull(),
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('admin.system_setting_dependencies.is_active'))
                                ->default(true)
                                ->helperText(__('admin.system_setting_dependencies.is_active_help')),
                            SupportFlatpickr::makeDateTime('created_at')
                                ->label(__('admin.common.created_at'))
                                ->disabled()
                                ->dehydrated(false)
                                ->visible(fn ($record) => $record?->created_at),
                        ]),
                    Placeholder::make('updated_at')
                        ->label(__('admin.common.updated_at'))
                        ->content(fn ($record) => $record?->updated_at?->format('Y-m-d H:i:s'))
                        ->visible(fn ($record) => $record?->updated_at),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.common.id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('setting.key')
                    ->label(__('admin.system_setting_dependencies.setting'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->copyMessage(__('admin.common.copied')),
                TextColumn::make('setting.name')
                    ->label(__('admin.system_setting_dependencies.setting_name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->limit(30),
                TextColumn::make('dependsOnSetting.key')
                    ->label(__('admin.system_setting_dependencies.depends_on_setting'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary')
                    ->copyable()
                    ->copyMessage(__('admin.common.copied')),
                TextColumn::make('dependsOnSetting.name')
                    ->label(__('admin.system_setting_dependencies.depends_on_setting_name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->limit(30),
                TextColumn::make('condition')
                    ->label(__('admin.system_setting_dependencies.condition'))
                    ->badge()
                    ->formatStateUsing(function (?string $state): string {
                        if ($state === null) {
                            return __('admin.common.not_set');
                        }

                        $translationKey = "admin.system_settings.{$state}";

                        return __($translationKey) !== $translationKey
                            ? __($translationKey)
                            : ucfirst(str_replace('_', ' ', $state));
                    })
                    ->sortable(),
                TextColumn::make('condition_value')
                    ->label(__('admin.system_settings.condition_value'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = (string) ($column->getState() ?? '');

                        if ($state === '') {
                            return null;
                        }

                        return is_string($state) && strlen($state) > 50 ? $state : null;
                    })
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('admin.system_setting_dependencies.is_active'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('setting_id')
                    ->label(__('admin.system_setting_dependencies.setting'))
                    ->options(fn () => SystemSetting::withoutGlobalScopes()->pluck('key', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        return isset($data['value']) && $data['value'] !== ''
                            ? $query->where('setting_id', $data['value'])
                            : $query;
                    }),
                SelectFilter::make('depends_on_setting_id')
                    ->label(__('admin.system_setting_dependencies.depends_on_setting'))
                    ->options(fn () => SystemSetting::withoutGlobalScopes()->pluck('key', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        return isset($data['value']) && $data['value'] !== ''
                            ? $query->where('depends_on_setting_id', $data['value'])
                            : $query;
                    }),
                TernaryFilter::make('is_active')
                    ->label(__('admin.system_setting_dependencies.is_active')),
                Filter::make('created_at')
                    ->form([
                        SupportFlatpickr::makeDateTime('created_from')
                            ->label(__('admin.common.created_from')),
                        SupportFlatpickr::makeDateTime('created_until')
                            ->label(__('admin.common.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('updated_at')
                    ->form([
                        SupportFlatpickr::makeDateTime('updated_from')
                            ->label(__('admin.common.updated_from')),
                        SupportFlatpickr::makeDateTime('updated_until')
                            ->label(__('admin.common.updated_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['updated_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '>=', $date),
                            )
                            ->when(
                                $data['updated_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('updated_at', '<=', $date),
                            );
                    }),
            ])
            ->searchable(['condition', 'condition_value', 'setting.key', 'dependsOnSetting.key'])
            ->actions([
                ViewAction::make()
                    ->label(__('admin.common.view'))
                    ->icon('heroicon-o-eye'),
                EditAction::make()
                    ->label(__('admin.common.edit'))
                    ->icon('heroicon-o-pencil'),
                Action::make('toggle_active')
                    ->label(fn (SystemSettingDependency $record): string => $record->is_active ? __('admin.system_setting_dependencies.deactivate') : __('admin.system_setting_dependencies.activate'))
                    ->icon(fn (SystemSettingDependency $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (SystemSettingDependency $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (SystemSettingDependency $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('admin.system_setting_dependencies.activated_successfully') : __('admin.system_setting_dependencies.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (SystemSettingDependency $record): string => $record->is_active ? __('admin.system_setting_dependencies.deactivate_confirm') : __('admin.system_setting_dependencies.activate_confirm'))
                    ->modalDescription(fn (SystemSettingDependency $record): string => $record->is_active ? __('admin.system_setting_dependencies.deactivate_description') : __('admin.system_setting_dependencies.activate_description')),
                Action::make('duplicate')
                    ->label(__('admin.common.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (SystemSettingDependency $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->is_active = false;
                        $newRecord->save();

                        Notification::make()
                            ->title(__('admin.system_setting_dependencies.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.system_setting_dependencies.duplicate_confirm'))
                    ->modalDescription(__('admin.system_setting_dependencies.duplicate_description')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('admin.common.delete_selected'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.common.delete_confirm'))
                        ->modalDescription(__('admin.common.delete_description')),
                    BulkAction::make('activate')
                        ->label(__('admin.system_setting_dependencies.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('admin.system_setting_dependencies.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.system_setting_dependencies.bulk_activate_confirm'))
                        ->modalDescription(__('admin.system_setting_dependencies.bulk_activate_description')),
                    BulkAction::make('deactivate')
                        ->label(__('admin.system_setting_dependencies.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('admin.system_setting_dependencies.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.system_setting_dependencies.bulk_deactivate_confirm'))
                        ->modalDescription(__('admin.system_setting_dependencies.bulk_deactivate_description')),
                    BulkAction::make('duplicate')
                        ->label(__('admin.common.duplicate_selected'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each(function (SystemSettingDependency $record): void {
                                $newRecord = $record->replicate();
                                $newRecord->is_active = false;
                                $newRecord->save();
                            });

                            Notification::make()
                                ->title(__('admin.system_setting_dependencies.bulk_duplicated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.system_setting_dependencies.bulk_duplicate_confirm'))
                        ->modalDescription(__('admin.system_setting_dependencies.bulk_duplicate_description')),
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
            'index'  => Pages\ListSystemSettingDependencies::route('/'),
            'create' => Pages\CreateSystemSettingDependency::route('/create'),
            'view'   => Pages\ViewSystemSettingDependency::route('/{record}'),
            'edit'   => Pages\EditSystemSettingDependency::route('/{record}/edit'),
        ];
    }
}
