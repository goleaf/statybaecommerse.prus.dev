<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductRequestResource\Pages;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Support\Filament\Components\SearchableInput;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\ProductSearch;
use App\Support\Search\SearchResult;
use App\Support\Search\SearchResultPayload;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ProductRequestResource extends BaseResource
{
    protected static ?string $model = ProductRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Inventory;

    protected static ?int $navigationSort = 7;

    public static function getNavigationLabel(): string
    {
        return __('admin.product_requests.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.product_requests.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.product_requests.model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('admin.product_requests.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            SearchableInput::make('product_id')
                                ->label(__('messages.product'))
                                ->required()
                                ->searchable()
                                ->searchUsing(static fn (string $search): array => ProductSearch::complex($search))
                                ->dehydrateStateUsing(static fn (?string $state): ?int => $state !== null && $state !== '' ? (int) $state : null)
                                ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
                                    SearchableInputHelper::hydrate(
                                        $component,
                                        $state,
                                        static function (int $value): ?SearchResult {
                                            $product = Product::query()
                                                ->select(['id', 'sku', 'name', 'price'])
                                                ->find($value);

                                            if (! $product instanceof Product) {
                                                return null;
                                            }

                                            $name = $product->getAttribute('name');
                                            if (is_array($name)) {
                                                $locale = app()->getLocale();
                                                $name = $name[$locale] ?? reset($name);
                                            }

                                            $result = SearchResult::make(
                                                (string) $product->getKey(),
                                                ProductSearch::label($product),
                                            );

                                            return SearchResultPayload::normalise($result, [
                                                'product_id' => $product->getKey(),
                                                'sku'        => (string) ($product->getAttribute('sku') ?? ''),
                                                'name'       => is_string($name) ? $name : '',
                                                'price'      => is_numeric($product->getAttribute('price')) ? (float) $product->getAttribute('price') : 0.0,
                                            ]);
                                        },
                                    );
                                }),
                            Select::make('user_id')
                                ->label(__('messages.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload(),
                            TextInput::make('name')
                                ->label(__('messages.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->label(__('messages.email'))
                                ->email()
                                ->required()
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->label(__('messages.phone'))
                                ->maxLength(50),
                            TextInput::make('requested_quantity')
                                ->label(__('admin.products.requested_quantity'))
                                ->numeric()
                                ->integer()
                                ->default(1),
                            Select::make('status')
                                ->label(__('admin.products.status'))
                                ->options([
                                    ProductRequest::STATUS_PENDING => __('admin.products.status_pending'),
                                    ProductRequest::STATUS_IN_PROGRESS => __('admin.products.status_in_progress'),
                                    ProductRequest::STATUS_COMPLETED => __('admin.products.status_completed'),
                                    ProductRequest::STATUS_CANCELLED => __('admin.products.status_cancelled'),
                                ])
                                ->default(ProductRequest::STATUS_PENDING)
                                ->required(),
                        ]),
                    Textarea::make('message')
                        ->label(__('messages.message'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('admin_notes')
                        ->label(__('admin.products.admin_notes'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('messages.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('messages.email'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('admin.products.status'))
                    ->sortable(),
                TextColumn::make('requested_quantity')
                    ->label(__('admin.products.requested_quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.products.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.products.status'))
                    ->options([
                        ProductRequest::STATUS_PENDING => __('admin.products.status_pending'),
                        ProductRequest::STATUS_IN_PROGRESS => __('admin.products.status_in_progress'),
                        ProductRequest::STATUS_COMPLETED => __('admin.products.status_completed'),
                        ProductRequest::STATUS_CANCELLED => __('admin.products.status_cancelled'),
                    ]),
                SelectFilter::make('product_id')
                    ->label(__('messages.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductRequests::route('/'),
            'create' => Pages\CreateProductRequest::route('/create'),
            'view' => Pages\ViewProductRequest::route('/{record}'),
            'edit' => Pages\EditProductRequest::route('/{record}/edit'),
        ];
    }
}
