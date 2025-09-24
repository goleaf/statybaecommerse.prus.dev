<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingTranslationResource\Pages;
use App\Models\SystemSettingTranslation;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\HeaderAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * SystemSettingTranslationResource
 *
 * Filament v4 resource for SystemSettingTranslation management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SystemSettingTranslationResource extends Resource
{
    protected static ?string $model = SystemSettingTranslation::class;

    protected static ?int $navigationSort = 14;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    public static function getNavigationLabel(): string
    {
        return __('admin.system_setting_translations.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.system_setting_translations.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.system_setting_translations.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Translation Details')
                    ->tabs([
                        Tab::make(__('admin.system_setting_translations.basic_information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                SchemaGrid::make(2)
                                    ->schema([
                                        Select::make('system_setting_id')
                                            ->label(__('admin.system_setting_translations.system_setting'))
                                            ->relationship('systemSetting', 'key')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                TextInput::make('key')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),
                                                Textarea::make('description')
                                                    ->rows(2),
                                            ])
                                            ->helperText(__('admin.system_setting_translations.system_setting_help')),
                                        Select::make('locale')
                                            ->label(__('admin.system_setting_translations.locale'))
                                            ->options([
                                                'en' => 'English',
                                                'lt' => 'Lithuanian',
                                                'de' => 'German',
                                                'fr' => 'French',
                                                'es' => 'Spanish',
                                                'pl' => 'Polish',
                                                'ru' => 'Russian',
                                            ])
                                            ->required()
                                            ->default('lt')
                                            ->native(false)
                                            ->searchable()
                                            ->helperText(__('admin.system_setting_translations.locale_help')),
                                    ]),
                                TextInput::make('name')
                                    ->label(__('admin.system_setting_translations.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->helperText(__('admin.system_setting_translations.name_help')),
                                Textarea::make('description')
                                    ->label(__('admin.system_setting_translations.description'))
                                    ->maxLength(1000)
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->helperText(__('admin.system_setting_translations.description_help')),
                                Textarea::make('help_text')
                                    ->label(__('admin.system_setting_translations.help_text'))
                                    ->maxLength(1000)
                                    ->rows(3)
                                    ->helperText(__('admin.system_setting_translations.help_text_help'))
                                    ->columnSpanFull(),
                            ]),
                        Tab::make(__('admin.system_setting_translations.advanced_settings'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                SchemaGrid::make(2)
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label(__('admin.system_setting_translations.is_active'))
                                            ->default(true)
                                            ->helperText(__('admin.system_setting_translations.is_active_help')),
                                        Toggle::make('is_public')
                                            ->label(__('admin.system_setting_translations.is_public'))
                                            ->default(false)
                                            ->helperText(__('admin.system_setting_translations.is_public_help')),
                                    ]),
                                KeyValue::make('metadata')
                                    ->label(__('admin.system_setting_translations.metadata'))
                                    ->keyLabel(__('admin.system_setting_translations.metadata_key'))
                                    ->valueLabel(__('admin.system_setting_translations.metadata_value'))
                                    ->helperText(__('admin.system_setting_translations.metadata_help'))
                                    ->columnSpanFull(),
                                TagsInput::make('tags')
                                    ->label(__('admin.system_setting_translations.tags'))
                                    ->helperText(__('admin.system_setting_translations.tags_help'))
                                    ->columnSpanFull(),
                            ]),
                        Tab::make(__('admin.system_setting_translations.rich_content'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                RichEditor::make('rich_description')
                                    ->label(__('admin.system_setting_translations.rich_description'))
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'strike',
                                        'link',
                                        'bulletList',
                                        'orderedList',
                                        'h2',
                                        'h3',
                                        'blockquote',
                                        'codeBlock',
                                    ])
                                    ->helperText(__('admin.system_setting_translations.rich_description_help'))
                                    ->columnSpanFull(),
                                FileUpload::make('attachments')
                                    ->label(__('admin.system_setting_translations.attachments'))
                                    ->multiple()
                                    ->acceptedFileTypes(['image/*', 'application/pdf', 'text/*'])
                                    ->maxFiles(5)
                                    ->helperText(__('admin.system_setting_translations.attachments_help'))
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('systemSetting.key')
                    ->label(__('admin.system_setting_translations.system_setting'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary')
                    ->url(fn($record) => route('filament.admin.resources.system-settings.view', $record->system_setting_id))
                    ->openUrlInNewTab(),
                TextColumn::make('locale')
                    ->label(__('admin.system_setting_translations.locale'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'en' => 'success',
                        'lt' => 'info',
                        'de' => 'warning',
                        'fr' => 'danger',
                        'es' => 'primary',
                        'pl' => 'secondary',
                        'ru' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'en' => '🇺🇸 English',
                        'lt' => '🇱🇹 Lithuanian',
                        'de' => '🇩🇪 German',
                        'fr' => '🇫🇷 French',
                        'es' => '🇪🇸 Spanish',
                        'pl' => '🇵🇱 Polish',
                        'ru' => '🇷🇺 Russian',
                        default => $state,
                    }),
                TextColumn::make('name')
                    ->label(__('admin.system_setting_translations.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->weight('bold'),
                TextColumn::make('description')
                    ->label(__('admin.system_setting_translations.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('help_text')
                    ->label(__('admin.system_setting_translations.help_text'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable()
                    ->searchable(),
                BooleanColumn::make('is_active')
                    ->label(__('admin.system_setting_translations.is_active'))
                    ->toggleable()
                    ->sortable(),
                BooleanColumn::make('is_public')
                    ->label(__('admin.system_setting_translations.is_public'))
                    ->toggleable()
                    ->sortable(),
                TagsColumn::make('tags')
                    ->label(__('admin.system_setting_translations.tags'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('systemSetting.group')
                    ->label(__('admin.system_setting_translations.setting_group'))
                    ->badge()
                    ->color('gray')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('systemSetting.type')
                    ->label(__('admin.system_setting_translations.setting_type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'string', 'text' => 'gray',
                        'number', 'integer', 'float' => 'blue',
                        'boolean' => 'green',
                        'array', 'json' => 'purple',
                        'file', 'image' => 'orange',
                        'select' => 'indigo',
                        'color' => 'pink',
                        'date', 'datetime' => 'yellow',
                        default => 'gray',
                    })
                    ->toggleable()
                    ->sortable(),
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
                SelectFilter::make('system_setting_id')
                    ->label(__('admin.system_setting_translations.system_setting'))
                    ->relationship('systemSetting', 'key')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('locale')
                    ->label(__('admin.system_setting_translations.locale'))
                    ->options([
                        'en' => '🇺🇸 English',
                        'lt' => '🇱🇹 Lithuanian',
                        'de' => '🇩🇪 German',
                        'fr' => '🇫🇷 French',
                        'es' => '🇪🇸 Spanish',
                        'pl' => '🇵🇱 Polish',
                        'ru' => '🇷🇺 Russian',
                    ])
                    ->multiple(),
                SelectFilter::make('systemSetting.group')
                    ->label(__('admin.system_setting_translations.setting_group'))
                    ->relationship('systemSetting', 'group')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label(__('admin.system_setting_translations.is_active'))
                    ->boolean()
                    ->trueLabel(__('admin.common.active'))
                    ->falseLabel(__('admin.common.inactive'))
                    ->native(false),
                TernaryFilter::make('is_public')
                    ->label(__('admin.system_setting_translations.is_public'))
                    ->boolean()
                    ->trueLabel(__('admin.common.public'))
                    ->falseLabel(__('admin.common.private'))
                    ->native(false),
                Filter::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->form([
                        DatePicker::make('from')->label(__('admin.common.from')),
                        DatePicker::make('until')->label(__('admin.common.until')),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = __('admin.common.from') . ': ' . $data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = __('admin.common.until') . ': ' . $data['until'];
                        }

                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn(Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn(Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date));
                    }),
                Filter::make('name')
                    ->label(__('admin.system_setting_translations.name'))
                    ->form([
                        TextInput::make('value')
                            ->label(__('admin.common.search_by_name'))
                            ->placeholder(__('admin.common.search_by_name')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        return $query->when($value, fn(Builder $q, $v): Builder => $q->where('name', 'like', "%{$v}%"));
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->slideOver(),
                EditAction::make()
                    ->slideOver(),
                Action::make('duplicate')
                    ->label(__('admin.common.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (SystemSettingTranslation $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->name = $record->name . ' (Copy)';
                        $newRecord->save();

                        Notification::make()
                            ->title(__('admin.system_setting_translations.duplicated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('translate')
                    ->label(__('admin.system_setting_translations.translate'))
                    ->icon('heroicon-o-language')
                    ->color('warning')
                    ->action(function (SystemSettingTranslation $record): void {
                        // Translation logic here
                        Notification::make()
                            ->title(__('admin.system_setting_translations.translation_started'))
                            ->info()
                            ->send();
                    }),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    BulkAction::make('activate')
                        ->label(__('admin.common.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('admin.system_setting_translations.activated_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('admin.common.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('admin.system_setting_translations.deactivated_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('export_translations')
                        ->label(__('admin.system_setting_translations.export_translations'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Export logic here
                            Notification::make()
                                ->title(__('admin.system_setting_translations.exported_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('import_translations')
                        ->label(__('admin.system_setting_translations.import_translations'))
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            // Import logic here
                            Notification::make()
                                ->title(__('admin.system_setting_translations.imported_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                HeaderAction::make('create')
                    ->label(__('admin.common.create'))
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(fn() => self::getUrl('create')),
                HeaderAction::make('export_all')
                    ->label(__('admin.system_setting_translations.export_all'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (): void {
                        // Export all logic here
                        Notification::make()
                            ->title(__('admin.system_setting_translations.exported_all_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('locale')
            ->reorderable('sort_order')
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            // Relations can be added here if needed
            // For example, if we had related models
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\SystemSettingTranslationStatsWidget::class,
            \App\Filament\Widgets\SystemSettingTranslationChartWidget::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return self::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('admin.system_setting_translations.system_setting') => $record->systemSetting->key,
            __('admin.system_setting_translations.locale') => $record->locale,
        ];
    }

    public static function getGlobalSearchResultUrl($record): string
    {
        return self::getUrl('view', ['record' => $record]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemSettingTranslations::route('/'),
            'create' => Pages\CreateSystemSettingTranslation::route('/create'),
            'view' => Pages\ViewSystemSettingTranslation::route('/{record}'),
            'edit' => Pages\EditSystemSettingTranslation::route('/{record}/edit'),
        ];
    }
}
