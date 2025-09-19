<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
/**
 * MenuResource
 * 
 * Filament v4 resource for Menu management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;
    
    /** @var UnitEnum|string|null */
    protected static string | UnitEnum | null $navigationGroup = "Products";
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('menus.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return "Content";
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('menus.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('menus.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('menus.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('menus.name'))
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('slug')
                                ->label(__('menus.slug'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    
                    Textarea::make('description')
                        ->label(__('menus.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('menus.menu_items'))
                ->components([
                    Repeater::make('items')
                        ->label(__('menus.menu_items'))
                        ->components([
                            TextInput::make('label')
                                ->label(__('menus.item_label'))
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('url')
                                ->label(__('menus.item_url'))
                                ->required()
                                ->maxLength(255)
                                ->url(),
                            
                            Select::make('target')
                                ->label(__('menus.item_target'))
                                ->options([
                                    '_self' => __('menus.targets.self'),
                                    '_blank' => __('menus.targets.blank'),
                                    '_parent' => __('menus.targets.parent'),
                                    '_top' => __('menus.targets.top'),
                                ])
                                ->default('_self'),
                            
                            TextInput::make('icon')
                                ->label(__('menus.item_icon'))
                                ->maxLength(100)
                                ->helperText(__('menus.item_icon_help')),
                            
                            TextInput::make('css_class')
                                ->label(__('menus.item_css_class'))
                                ->maxLength(255)
                                ->helperText(__('menus.item_css_class_help')),
                            
                            Toggle::make('is_active')
                                ->label(__('menus.item_is_active'))
                                ->default(true),
                            
                            TextInput::make('sort_order')
                                ->label(__('menus.item_sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ])
                        ->columns(3)
                        ->addActionLabel(__('menus.add_menu_item'))
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('menus.settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('menus.is_active'))
                                ->default(true),
                            
                            Toggle::make('is_mobile')
                                ->label(__('menus.is_mobile'))
                                ->default(false),
                        ]),
                    
                    Grid::make(2)
                        ->components([
                            TextInput::make('css_class')
                                ->label(__('menus.css_class'))
                                ->maxLength(255)
                                ->helperText(__('menus.css_class_help')),
                            
                            TextInput::make('sort_order')
                                ->label(__('menus.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('menus.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('slug')
                    ->label(__('menus.slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                
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
                
                IconColumn::make('is_mobile')
                    ->label(__('menus.is_mobile'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('css_class')
                    ->label(__('menus.css_class'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('sort_order')
                    ->label(__('menus.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
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
                    ->label(__('menus.is_active'))
                    ->boolean()
                    ->trueLabel(__('menus.active_only'))
                    ->falseLabel(__('menus.inactive_only'))
                    ->native(false),
                
                TernaryFilter::make('is_mobile')
                    ->label(__('menus.is_mobile'))
                    ->boolean()
                    ->trueLabel(__('menus.mobile_only'))
                    ->falseLabel(__('menus.desktop_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                
                Action::make('toggle_active')
                    ->label(fn (Menu $record): string => $record->is_active ? __('menus.deactivate') : __('menus.activate'))
                    ->icon(fn (Menu $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Menu $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Menu $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        
                        Notification::make()
                            ->title($record->is_active ? __('menus.activated_successfully') : __('menus.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Action::make('preview')
                    ->label(__('menus.preview'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Menu $record): string => route('menu.preview', $record))
                    ->openUrlInNewTab(),
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
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'view' => Pages\ViewMenu::route('/{record}'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
