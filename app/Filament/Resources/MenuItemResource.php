<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\Menu;
use App\Models\MenuItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

/**
 * MenuItemResource
 *
 * Filament v4 resource for MenuItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static UnitEnum|string|null $navigationGroup = 'Content';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'label';

    public static function getNavigationLabel(): string
    {
        return __('admin.menu_items.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.menu_items.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.menu_items.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FormSection::make(__('admin.menu_items.basic_information'))
                    ->schema([
                        FormGrid::make(2)
                            ->schema([
                                Select::make('menu_id')
                                    ->label(__('admin.menu_items.menu'))
                                    ->options(Menu::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Select::make('parent_id')
                                    ->label(__('admin.menu_items.parent'))
                                    ->options(MenuItem::whereNull('parent_id')->pluck('label', 'id'))
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('label')
                                    ->label(__('admin.menu_items.label'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('url')
                                    ->label(__('admin.menu_items.url'))
                                    ->maxLength(255)
                                    ->url(),
                                TextInput::make('route_name')
                                    ->label(__('admin.menu_items.route_name'))
                                    ->maxLength(255)
                                    ->helperText(__('admin.menu_items.route_name_help')),
                                TextInput::make('icon')
                                    ->label(__('admin.menu_items.icon'))
                                    ->maxLength(100)
                                    ->helperText(__('admin.menu_items.icon_help')),
                                TextInput::make('sort_order')
                                    ->label(__('admin.menu_items.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),
                FormSection::make(__('admin.menu_items.status'))
                    ->schema([
                        FormGrid::make(1)
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label(__('admin.menu_items.is_visible'))
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('menu.name')
                    ->label(__('admin.menu_items.menu'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('label')
                    ->label(__('admin.menu_items.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url')
                    ->label(__('admin.menu_items.url'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('route_name')
                    ->label(__('admin.menu_items.route_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('icon')
                    ->label(__('admin.menu_items.icon'))
                    ->icon()
                    ->sortable(),
                TextColumn::make('parent.label')
                    ->label(__('admin.menu_items.parent'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('admin.menu_items.sort_order'))
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label(__('admin.menu_items.is_visible'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('menu_id')
                    ->label(__('admin.menu_items.menu'))
                    ->options(Menu::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('parent_id')
                    ->label(__('admin.menu_items.parent'))
                    ->options(MenuItem::whereNull('parent_id')->pluck('label', 'id'))
                    ->searchable(),
                TernaryFilter::make('is_visible')
                    ->label(__('admin.menu_items.is_visible')),
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
            ->defaultSort('sort_order');
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
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'view' => Pages\ViewMenuItem::route('/{record}'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
