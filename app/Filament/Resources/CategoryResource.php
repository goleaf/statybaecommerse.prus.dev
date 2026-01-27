<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

final class CategoryResource extends BaseResource
{
    use HasNav;

    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 3;

    public static function getRecordTitleAttribute(): ?string
    {
        return 'name';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.categories.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.categories.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.categories.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('admin.categories.basic_information'))
                ->description(__('admin.categories.basic_information_description'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('messages.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('slug')
                                ->label(__('messages.slug'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                        ]),
                    RichEditor::make('description')
                        ->label(__('messages.description'))
                        ->columnSpanFull(),
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('parent_id')
                                ->label(__('messages.category'))
                                ->relationship('parent', 'name')
                                ->searchable()
                                ->preload(),
                            Toggle::make('is_active')
                                ->label(__('admin.categories.is_active'))
                                ->default(true),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label(__('messages.category'))
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.categories.is_active')),
                TextColumn::make('products_count')
                    ->label(__('admin.categories.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.categories.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view'   => Pages\ViewCategory::route('/{record}'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
