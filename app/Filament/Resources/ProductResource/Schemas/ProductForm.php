<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('admin.products.basic_information'))
                    ->description(__('admin.products.basic_information_description'))
                    ->schema([
                        Grid::make(3)
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
                                TextInput::make('sku')
                                    ->label(__('messages.sku'))
                                    ->required(),
                                TextInput::make('barcode')
                                    ->label(__('messages.barcode'))
                                    ->maxLength(100),
                                Select::make('brand_id')
                                    ->label(__('messages.brand'))
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('suppliers')
                                    ->label(__('admin.suppliers.plural_model_label'))
                                    ->relationship(
                                        name: 'suppliers',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: static function (Builder $query, ?Model $record): Builder {
                                            return $query
                                                ->where(static function (Builder $where) use ($record): void {
                                                    $where->where('is_enabled', true);

                                                    if ($record instanceof Product && $record->exists) {
                                                        $where->orWhereIn(
                                                            'suppliers.id',
                                                            $record->suppliers()->select('suppliers.id')
                                                        );
                                                    }
                                                })
                                                ->orderBy('name');
                                        },
                                    )
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                                Select::make('status')
                                    ->label(__('admin.products.status'))
                                    ->options([
                                        'draft'     => __('admin.products.status_draft'),
                                        'pending'   => __('admin.products.status_pending'),
                                        'published' => __('admin.products.status_published'),
                                        'archived'  => __('admin.products.status_archived'),
                                    ])
                                    ->default('draft')
                                    ->required(),
                                Toggle::make('is_featured')
                                    ->label(__('admin.products.is_featured'))
                                    ->default(false),
                                DateTimePicker::make('published_at')
                                    ->label(__('admin.products.published_at')),
                            ]),
                        RichEditor::make('description')
                            ->label(__('messages.description'))
                            ->columnSpanFull(),
                        RichEditor::make('detailed_description')
                            ->label(__('admin.products.detailed_description'))
                            ->columnSpanFull(),
                        Textarea::make('short_description')->label(__('admin.products.short_description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                Select::make('collections')
                                    ->label(__('messages.collections'))
                                    ->relationship('collections', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('admin.products.pricing'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->label(__('messages.price'))
                                    ->numeric()
                                    ->required()
                                    ->prefix('€'),
                                TextInput::make('cost_price')
                                    ->label(__('admin.products.cost_price'))
                                    ->numeric()
                                    ->prefix('€'),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Product Images')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('thumbnail')
                                    ->label('Thumbnail / Featured Image')
                                    ->collection('thumbnail')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        null,
                                        '1:1',
                                        '4:3',
                                        '16:9',
                                    ])
                                    ->conversion('thumb')
                                    ->maxSize(2048)
                                    ->helperText('Main image shown in product listings (max 2MB)'),
                                SpatieMediaLibraryFileUpload::make('product_images')
                                    ->label('Product Gallery')
                                    ->collection('product_images')
                                    ->multiple()
                                    ->image()
                                    ->reorderable()
                                    ->appendFiles()
                                    ->downloadable()
                                    ->openable()
                                    ->maxFiles(10)
                                    ->maxSize(5120)
                                    ->acceptedFileTypes([
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                        'image/gif',
                                    ])
                                    ->helperText('Upload up to 10 images. Drag to reorder.')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
                Section::make(__('admin.products.inventory'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Toggle::make('manage_stock')
                                    ->label(__('admin.products.manage_stock'))
                                    ->default(true),
                                Toggle::make('allow_backorder')
                                    ->label(__('admin.products.allow_backorder'))
                                    ->default(false),
                                TextInput::make('stock_quantity')
                                    ->label(__('admin.products.stock_quantity'))
                                    ->numeric()
                                    ->integer()
                                    ->default(0),
                                TextInput::make('low_stock_threshold')
                                    ->label(__('admin.products.low_stock_threshold'))
                                    ->numeric()
                                    ->integer()
                                    ->default(0),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('admin.products.physical'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('weight')
                                    ->label(__('admin.products.weight'))
                                    ->numeric()
                                    ->suffix(__('messages.unit_kg')),
                                TextInput::make('length')
                                    ->label(__('admin.products.length'))
                                    ->numeric()
                                    ->suffix(__('messages.unit_cm')),
                                TextInput::make('width')
                                    ->label(__('admin.products.width'))
                                    ->numeric()
                                    ->suffix(__('messages.unit_cm')),
                                TextInput::make('height')
                                    ->label(__('admin.products.height'))
                                    ->numeric()
                                    ->suffix(__('messages.unit_cm')),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('size')
                                    ->label(__('messages.size'))
                                    ->maxLength(255),
                                TextInput::make('size_type')
                                    ->label(__('admin.labels.size_type'))
                                    ->maxLength(255),
                                TextInput::make('color')
                                    ->label(__('translations.color'))
                                    ->maxLength(255),
                                TextInput::make('pack_size')
                                    ->label(__('attribute.pack_size'))
                                    ->maxLength(255),
                                TextInput::make('pack_size_type')
                                    ->label(__('admin.labels.pack_size_type'))
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('admin.products.shipping_exclusions'))
                    ->description(__('admin.products.shipping_exclusions_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_venipak_locker_excluded')
                                    ->label(__('admin.products.exclude_from_venipak_lockers'))
                                    ->default(false),
                                Toggle::make('is_venipak_courier_excluded')
                                    ->label(__('admin.products.exclude_from_venipak_courier'))
                                    ->default(false),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
