<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class BrandResource extends BaseResource
{
    protected static ?string $model = Brand::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('messages.admin_brands');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.brands.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.brands.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('admin.brands.basic_information'))
                ->description(__('admin.brands.basic_information_description'))
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
                            Toggle::make('is_active')
                                ->label(__('admin.brands.is_active'))
                                ->default(true),
                            Toggle::make('is_premium')
                                ->label(__('admin.brands.is_premium'))
                                ->default(false),
                        ]),
                ]),
            SchemaSection::make(__('messages.media'))
                ->description(__('admin.brands.media_description'))
                ->schema([
                    FileUpload::make('logo')
                        ->label(__('messages.image'))
                        ->image()
                        ->columnSpanFull(),
                ])
                ->collapsible(),
            SchemaSection::make(__('admin.brands.social_links'))
                ->schema([
                    Repeater::make('social_links')
                        ->schema([
                            Select::make('platform')
                                ->options(array_combine(Brand::SOCIAL_LINK_PLATFORMS, array_map('ucfirst', Brand::SOCIAL_LINK_PLATFORMS)))
                                ->required(),
                            TextInput::make('url')
                                ->url()
                                ->required(),
                        ])
                        ->columns(2),
                ])
                ->collapsible(),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \App\Models\Scopes\ActiveScope::class,
                \App\Models\Scopes\EnabledScope::class,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label(__('messages.image'))
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.brands.is_active')),
                TextColumn::make('products_count')
                    ->label(__('admin.brands.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.brands.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'view'   => Pages\ViewBrand::route('/{record}'),
            'edit'   => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
