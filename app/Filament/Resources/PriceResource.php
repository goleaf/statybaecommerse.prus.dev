<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\PriceResource\Pages;
use App\Models\Price;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class PriceResource extends Resource
{
    use HasNav;

    protected static ?string $model = Price::class;

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.prices.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                SearchableInput::make('product_id')
                                    ->label(__('admin.prices.product'))
                                    ->placeholder('SKU / EAN / name')
                                    ->required()
                                    ->searchUsing(fn (string $search): array => ProductSearch::complex($search))
                                    ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) $state : null)
                                    ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
                                        // Hydrate via helper to align with docs/forms/SEARCHABLE_INPUT_METADATA.md.
                                        SearchableInputHelper::hydrate(
                                            $component,
                                            $state,
                                            static function (int $value): ?array {
                                                $product = Product::query()
                                                    ->select(['id', 'sku', 'name'])
                                                    ->find($value);

                                                if (! $product instanceof Product) {
                                                    return null;
                                                }

                                                return [
                                                    'value' => $product->getKey(),
                                                    'label' => ProductSearch::label($product),
                                                ];
                                            },
                                        );
                                    })
                                    ->afterStateUpdated(function (?string $state, Set $set): void {
                                        if ($state === null || $state === '') {
                                            SearchableInputHelper::clear($set, ['product_id' => null]);

                                            return;
                                        }

                                        $product = Product::query()
                                            ->select(['id'])
                                            ->find((int) $state);

                                        if (! $product instanceof Product) {
                                            return;
                                        }

                                        $set('product_id', $product->getKey());
                                    }),
                                TextInput::make('amount')
                                    ->label(__('admin.prices.amount'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->required(),
                            ]),
                    ]),
                SchemaSection::make(__('admin.prices.audit_section'))
                    ->schema([
                        Textarea::make('audit_reason')
                            ->label(__('admin.prices.audit_reason'))
                            ->helperText(__('admin.prices.audit_reason_help'))
                            ->visible(fn (?Price $record): bool => (bool) ($record?->exists))
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('admin.prices.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('admin.prices.amount'))
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('admin.prices.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'edit'   => Pages\EditPrice::route('/{record}/edit'),
        ];
    }
}
