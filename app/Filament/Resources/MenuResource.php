<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use App\Support\Concerns\HasNav;
use Filament\Schemas\Schema;
use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\MenuResource\RelationManagers\MenuItemsRelationManager;
use App\Models\Menu;
use App\Models\Scopes\ActiveScope;
use BackedEnum;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class MenuResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static \UnitEnum|string|null $navigationGroup = 'Content';

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('menus.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('menus.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema   
    {
        // Ensure compatibility with the Schema-based form builder introduced in Filament v4.
        $locationOptions = [
            'header'  => __('menus.locations.header'),
            'footer'  => __('menus.locations.footer'),
            'sidebar' => __('menus.locations.sidebar'),
            'mobile'  => __('menus.locations.mobile'),
        ];

        return $schema->schema([
            SchemaSection::make(__('menus.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('menus.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('key')
                                ->label(__('menus.key'))
                                ->helperText(__('menus.key_help'))
                                ->required()
                                ->maxLength(255)
                                ->rule('alpha_dash')
                                ->unique(ignoreRecord: true),
                        ]),
                    Select::make('location')
                        ->label(__('menus.location'))
                        ->required()
                        ->options($locationOptions)
                        ->searchable(),
                    Textarea::make('description')
                        ->label(__('menus.description'))
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('menus.settings'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('menus.is_active'))
                        ->default(true),
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
            ->columns([
                TextColumn::make('name')
                    ->label(__('menus.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('key')
                    ->label(__('menus.key'))
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location')
                    ->label(__('menus.location'))
                    ->badge()
                    ->formatStateUsing(static fn (?string $state): ?string => match ($state) {
                        'header'  => __('menus.locations.header'),
                        'footer'  => __('menus.locations.footer'),
                        'sidebar' => __('menus.locations.sidebar'),
                        'mobile'  => __('menus.locations.mobile'),
                        default   => $state,
                    })
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('menus.description'))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('items_count')
                    ->label(__('menus.items_count'))
                    ->counts('items')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('menus.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('menus.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('menus.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->trueLabel(__('menus.active_only'))
                    ->falseLabel(__('menus.inactive_only')),
                SelectFilter::make('location')
                    ->label(__('menus.location'))
                    ->multiple()
                    ->options([
                        'header'  => __('menus.locations.header'),
                        'footer'  => __('menus.locations.footer'),
                        'sidebar' => __('menus.locations.sidebar'),
                        'mobile'  => __('menus.locations.mobile'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('toggle_active')
                    ->label(fn (Menu $record): string => $record->is_active ? __('menus.deactivate') : __('menus.activate'))
                    ->icon(fn (Menu $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Menu $record): string => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Menu $record): void {
                        // Flip the active flag before Filament resolves the success notification payload.
                        $record->update(['is_active' => ! $record->is_active]);
                    })
                    ->successNotificationTitle(static fn (Menu $record): string => $record->is_active
                        ? __('menus.activated_successfully')
                        : __('menus.deactivated_successfully')
                    ),
                Action::make('duplicate')
                    ->label(__('menus.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('secondary')
                    ->requiresConfirmation()
                    ->action(function (Menu $record): void {
                        $timestamp = now()->timestamp;

                        $duplicate = $record->replicate([
                            'created_at',
                            'updated_at',
                        ]);

                        $duplicate->name = sprintf('%s (Copy)', $record->name);
                        $duplicate->key = sprintf('%s_copy_%s', $record->key, $timestamp);

                        $duplicate->save();

                        Notification::make()
                            ->title(__('menus.duplicated_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('menus.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('menus.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('menus.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('menus.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name');
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            MenuItemsRelationManager::class,
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'view'   => Pages\ViewMenu::route('/{record}'),
            'edit'   => Pages\EditMenu::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
        ]);
    }
}
