<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\ProductRequestResource\Pages;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Support\Filament\Components\Flatpickr;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\ProductSearch;
use BackedEnum;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class ProductRequestResource extends Resource
{
    use HasNav;

    protected static ?string $model = ProductRequest::class;

    /**
     * @var string|BackedEnum|null Navigation icon identifier for the resource.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 16;

    public static function getNavigationLabel(): string
    {
        return __('product_requests.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('product_requests.plural');
    }

    public static function getModelLabel(): string
    {
        return __('product_requests.single');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                SearchableInput::make('product_id')
                    ->label(__('product_requests.fields.product'))
                    ->placeholder('SKU / EAN / name')
                    ->required()
                    ->searchUsing(fn (string $search): array => ProductSearch::complex($search))
                    ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) $state : null)
                    ->afterStateHydrated(function (SearchableInput $component, ?int $state, ?ProductRequest $record): void {
                        // Hydrate via helper so metadata lifecycle mirrors docs/forms/SEARCHABLE_INPUT_METADATA.md.
                        SearchableInputHelper::hydrate(
                            $component,
                            $state,
                            static function (int $value) use ($record): ?array {
                                $product = $record?->product ?? Product::query()
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
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->label(__('product_requests.fields.name'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(__('product_requests.fields.email'))
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('product_requests.fields.phone'))
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->label(__('product_requests.fields.message'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('requested_quantity')
                    ->label(__('product_requests.fields.requested_quantity'))
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
                Forms\Components\Select::make('status')
                    ->label(__('product_requests.fields.status'))
                    ->options([
                        'pending'     => __('product_requests.status.pending'),
                        'in_progress' => __('product_requests.status.in_progress'),
                        'completed'   => __('product_requests.status.completed'),
                        'cancelled'   => __('product_requests.status.cancelled'),
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('admin_notes')
                    ->label(__('product_requests.fields.admin_notes'))
                    ->columnSpanFull(),
                Flatpickr::makeDateTime('responded_at')
                    ->label(__('product_requests.fields.responded_at')),
                Forms\Components\Select::make('responded_by')
                    ->relationship('respondedBy', 'name')
                    ->label(__('product_requests.fields.responded_by'))
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('product_requests.fields.product'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('product_requests.fields.user'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('product_requests.fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('product_requests.fields.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('product_requests.fields.phone'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('requested_quantity')
                    ->label(__('product_requests.fields.requested_quantity'))
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('product_requests.fields.status'))
                    ->colors([
                        'warning' => 'pending',
                        'info'    => 'in_progress',
                        'success' => 'completed',
                        'danger'  => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('responded_at')
                    ->label(__('product_requests.fields.responded_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('respondedBy.name')
                    ->label(__('product_requests.fields.responded_by'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('product_requests.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('product_requests.filters.status'))
                    ->options([
                        'pending'     => __('product_requests.status.pending'),
                        'in_progress' => __('product_requests.status.in_progress'),
                        'completed'   => __('product_requests.status.completed'),
                        'cancelled'   => __('product_requests.status.cancelled'),
                    ]),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label(__('product_requests.filters.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('product_requests.filters.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index'  => Pages\ListProductRequests::route('/'),
            'create' => Pages\CreateProductRequest::route('/create'),
            'view'   => Pages\ViewProductRequest::route('/{record}'),
            'edit'   => Pages\EditProductRequest::route('/{record}/edit'),
        ];
    }
}
