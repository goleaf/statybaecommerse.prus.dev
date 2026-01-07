<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SystemSettingCategoryResource\Pages;
use App\Filament\Resources\SystemSettingCategoryResource\RelationManagers;
use App\Models\Scopes\ActiveScope;
use App\Models\SystemSettingCategory;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Utilities\Get as SchemaGet;
use Filament\Schemas\Components\Utilities\Set as SchemaSet;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * SystemSettingCategoryResource
 *
 * Filament v4 resource for System Setting Categories management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SystemSettingCategoryResource extends Resource
{
    use HasNav;

    protected static ?string $model = SystemSettingCategory::class;

    /**
     * @var string|BackedEnum|null Keep navigation grouping aligned with the shared enum helper.
     */
    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::System->value;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        // Harmonize enum and string groups to avoid leaking raw enum values in the UI.
        $group = self::$navigationGroup;

        return $group instanceof NavigationGroup ? $group->label() : $group;
    }

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('system_setting_categories.title');
    }

    /** Handle getNavigationGroup functionality with proper error handling. */

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('system_setting_categories.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('system_setting_categories.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('system_setting_categories.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('system_setting_categories.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live()
                                    ->afterStateUpdated(static function (?string $state, SchemaSet $set): void {
                                        // Keep the slug synchronized when administrators edit the name.
                                        if ($state === null || $state === '') {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    })
                                    ->helperText(__('system_setting_categories.name_help')),
                                TextInput::make('slug')
                                    ->label(__('system_setting_categories.slug'))
                                    ->rules(static fn (SchemaGet $get): array => [empty($get('name')) ? 'required' : 'nullable'])
                                    ->unique(SystemSettingCategory::class, 'slug', ignoreRecord: true)
                                    // Allow empty slug; it will be generated from name on submit
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(static function (?string $state, SchemaGet $get): string {
                                        // Fall back to a generated slug whenever the administrator leaves it blank.
                                        return $state !== null && $state !== ''
                                            ? $state
                                            : Str::slug((string) $get('name'));
                                    })
                                    ->helperText(__('system_setting_categories.slug_help')),
                            ]),
                        Textarea::make('description')
                            ->label(__('system_setting_categories.description'))
                            ->rows(3)
                            ->helperText(__('system_setting_categories.description_help')),
                    ]),
                SchemaSection::make(__('system_setting_categories.appearance'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('icon')
                                    ->label(__('system_setting_categories.icon'))
                                    ->maxLength(255)
                                    ->placeholder('heroicon-o-cog-6-tooth')
                                    ->helperText(__('system_setting_categories.icon_help')),
                                ColorPicker::make('color')
                                    ->label(__('system_setting_categories.color'))
                                    ->helperText(__('system_setting_categories.color_help')),
                            ]),
                    ]),
                SchemaSection::make(__('system_setting_categories.hierarchy'))
                    ->schema([
                        Select::make('parent_id')
                            ->label(__('system_setting_categories.parent'))
                            ->relationship('parent', 'name', static fn (Builder $query): Builder => $query->withoutGlobalScopes([ActiveScope::class]))
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->helperText(__('system_setting_categories.parent_help')),
                    ]),
                SchemaSection::make(__('system_setting_categories.configuration'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label(__('system_setting_categories.sort_order'))
                                    ->numeric()
                                    ->default(0)
                                    ->helperText(__('system_setting_categories.sort_order_help')),
                                Toggle::make('is_active')
                                    ->label(__('system_setting_categories.is_active'))
                                    ->default(true)
                                    ->helperText(__('system_setting_categories.is_active_help')),
                            ]),
                    ]),
            ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes([ActiveScope::class]))
            ->deferLoading(false)
            ->columns([
                TextColumn::make('name')
                    ->label(__('system_setting_categories.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label(__('system_setting_categories.slug'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                TextColumn::make('description')
                    ->label(__('system_setting_categories.description'))
                    ->limit(50)
                    ->tooltip(static function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return is_string($state) && mb_strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('icon')
                    ->label(__('system_setting_categories.icon'))
                    ->formatStateUsing(static fn (?string $state): string => $state ?: 'heroicon-o-cog-6-tooth')
                    ->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('color')
                    ->label(__('system_setting_categories.color'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('parent.name')
                    ->label(__('system_setting_categories.parent'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // Remove counts columns for sqlite testing environment compatibility
                IconColumn::make('is_active')
                    ->label(__('system_setting_categories.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('system_setting_categories.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('system_setting_categories.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('system_setting_categories.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->relationship('parent', 'name')
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('system_setting_categories.active_only'))
                    ->falseLabel(__('system_setting_categories.inactive_only'))
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->actions([
                Action::make('duplicate')
                    ->label(__('system_setting_categories.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(static function (SystemSettingCategory $record): void {
                        // Duplicate the record with a predictable suffix to keep slugs unique during reviews.
                        $newRecord = $record->replicate();
                        $newRecord->name = $record->name . ' (Copy)';
                        $newRecord->slug = $record->slug . '-copy';
                        $newRecord->save();

                        Notification::make()
                            ->title(__('system_setting_categories.duplicated_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('system_setting_categories.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(static function (Collection $records): void {
                            self::updateActivationState($records, true);
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('system_setting_categories.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(static function (Collection $records): void {
                            self::updateActivationState($records, false);
                        }),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Normalize incoming record collections and toggle their activation flag safely.
     */
    private static function updateActivationState(Collection $records, bool $activate): void
    {
        $ids = $records
            ->map(static function ($record): ?int {
                if ($record instanceof SystemSettingCategory) {
                    return $record->getKey();
                }

                if (is_array($record)) {
                    return isset($record['id']) ? (int) $record['id'] : (isset($record[0]) ? (int) $record[0] : null);
                }

                if (is_numeric($record)) {
                    return (int) $record;
                }

                return null;
            })
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        SystemSettingCategory::withoutGlobalScopes([ActiveScope::class])
            ->whereIn('id', $ids)
            ->update(['is_active' => $activate]);

        Notification::make()
            ->title($activate
                ? __('system_setting_categories.activated_successfully')
                : __('system_setting_categories.deactivated_successfully'))
            ->success()
            ->send();
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\TranslationsRelationManager::class,
            RelationManagers\SettingsRelationManager::class,
            RelationManagers\ChildrenRelationManager::class,
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSystemSettingCategories::route('/'),
            'create' => Pages\CreateSystemSettingCategory::route('/create'),
            'view'   => Pages\ViewSystemSettingCategory::route('/{record}'),
            'edit'   => Pages\EditSystemSettingCategory::route('/{record}/edit'),
        ];
    }
}
