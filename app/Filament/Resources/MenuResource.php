<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\RelationManagers\MenuItemsRelationManager;
use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use \BackedEnum;
final class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bars-3';


    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.menus');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('translations.menu_information'))
                    ->components([
                        Forms\Components\TextInput::make('key')
                            ->label(__('translations.key'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),
                        Forms\Components\TextInput::make('name')
                            ->label(__('translations.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location')
                            ->label(__('translations.location'))
                            ->maxLength(100),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('translations.active'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->label(__('translations.key'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('translations.name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('location')->label(__('translations.location'))->toggleable(),
                Tables\Columns\ToggleColumn::make('is_active')->label(__('translations.active'))->sortable(),
                Tables\Columns\TextColumn::make('created_at')->date('Y-m-d')->sortable(),
            ])
            ->filters([])
            ->recordUrl(fn($record) => self::getUrl('edit', ['record' => $record]))
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MenuItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
