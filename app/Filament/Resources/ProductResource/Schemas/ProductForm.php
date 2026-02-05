<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
                                Select::make('categories')
                                    ->label(__('messages.categories'))
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                                Select::make('collections')
                                    ->label(__('messages.collections'))
                                    ->relationship('collections', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('admin.products.images'))
                    ->schema([
                        Repeater::make('images')
                            ->relationship('images', fn ($query) => $query->withoutGlobalScopes())
                            ->schema([
                                FileUpload::make('path')
                                    ->label(__('admin.products.image'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('product-images')
                                    ->required()
                                    ->columnSpanFull()
                                    ->imageEditor()
                                    ->imagePreviewHeight('250'),
                                TextInput::make('alt_text')
                                    ->label(__('admin.products.alt_text'))
                                    ->columnSpanFull(),
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('is_default')
                                            ->label(__('translations.is_default'))
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get, $component) {
                                                if (! $state) {
                                                    return;
                                                }

                                                $items = $get('../../images') ?? [];
                                                $statePath = $component->getStatePath();

                                                foreach (array_keys($items) as $key) {
                                                    if (! str_contains($statePath, ".{$key}.")) {
                                                        $set("../../images.{$key}.is_default", false);
                                                    }
                                                }
                                            }),
                                        Toggle::make('is_active')
                                            ->label(__('messages.active'))
                                            ->default(true),
                                    ]),
                            ])
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $normalizedPath = self::normalizeImagePath($data['path'] ?? null);
                                if ($normalizedPath !== null) {
                                    $data['path'] = $normalizedPath;
                                }
                                $data['is_active'] = (bool) ($data['is_active'] ?? true);
                                $data['is_default'] = (bool) ($data['is_default'] ?? false);

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                $normalizedPath = self::normalizeImagePath($data['path'] ?? null);
                                if ($normalizedPath !== null) {
                                    $data['path'] = $normalizedPath;
                                } else {
                                    unset($data['path']);
                                }
                                $data['is_active'] = (bool) ($data['is_active'] ?? true);
                                $data['is_default'] = (bool) ($data['is_default'] ?? false);

                                return $data;
                            })
                            ->columnSpanFull()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                $label = $state['alt_text'] ?? null;

                                if (is_string($label) && $label !== '') {
                                    return $label;
                                }

                                $path = $state['path'] ?? null;

                                if ($path instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                    return $path->getClientOriginalName();
                                }

                                if (is_array($path)) {
                                    $first = $path[0] ?? null;

                                    if ($first instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                        return $first->getClientOriginalName();
                                    }

                                    if (is_string($first) && $first !== '') {
                                        return $first;
                                    }
                                }

                                if (is_string($path) && $path !== '') {
                                    return $path;
                                }

                                return 'Image';
                            }),
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
                    ])
                    ->columnSpanFull(),

                Section::make(__('admin.products.seo'))
                    ->schema([
                        TextInput::make('seo_title')
                            ->label(__('admin.products.seo_title'))
                            ->maxLength(255),
                        Textarea::make('seo_description')
                            ->label(__('admin.products.seo_description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

            ]);
    }

    private static function normalizeImagePath(mixed $path): ?string
    {
        if ($path instanceof TemporaryUploadedFile) {
            return $path->store('product-images', 'public');
        }

        if (is_array($path)) {
            foreach ($path as $value) {
                $normalized = self::normalizeImagePath($value);

                if ($normalized !== null) {
                    return $normalized;
                }
            }

            return null;
        }

        if (! is_string($path)) {
            return null;
        }

        $path = trim($path);

        return $path !== '' ? $path : null;
    }
}
