<?php declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use App\Models\CartItem;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateFilter;
use Illuminate\Database\Eloquent\Builder;

final class CartItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'cartItems';

    protected static ?string $recordTitleAttribute = 'product.name';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('admin.customers.cart_items');
    }

    public function form(Schema $form): Schema
    {
        return $form->components([
                Section::make(__('admin.cart_items.cart_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('admin.cart_items.fields.product'))
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('quantity')
                                    ->label(__('admin.cart_items.fields.quantity'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),
                                TextInput::make('price')
                                    ->label(__('admin.cart_items.fields.price'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->minValue(0)
                                    ->required(),
                                TextInput::make('total')
                                    ->label(__('admin.cart_items.fields.total'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->minValue(0)
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('admin.cart_items.fields.product'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->url(fn ($record) => route('filament.admin.resources.products.view', $record->product)),
                TextColumn::make('product.sku')
                    ->label(__('admin.cart_items.fields.sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('admin.cart_items.fields.sku') . ' ' . __('admin.common.copied')),
                TextColumn::make('quantity')
                    ->label(__('admin.cart_items.fields.quantity'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),
                TextColumn::make('price')
                    ->label(__('admin.cart_items.fields.price'))
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('total')
                    ->label(__('admin.cart_items.fields.total'))
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color('success')
                    ->weight('bold'),
                TextColumn::make('created_at')
                    ->label(__('admin.cart_items.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('d/m/Y H:i:s')),
                TextColumn::make('updated_at')
                    ->label(__('admin.cart_items.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record) => $record->updated_at?->format('d/m/Y H:i:s')),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->label(__('admin.cart_items.filters.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                DateFilter::make('created_at')
                    ->label(__('admin.cart_items.filters.created_at'))
                    ->displayFormat('d/m/Y'),
                DateFilter::make('updated_at')
                    ->label(__('admin.cart_items.filters.updated_at'))
                    ->displayFormat('d/m/Y'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.cart_items.create'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('admin.actions.view')),
                EditAction::make()
                    ->label(__('admin.actions.edit')),
                DeleteAction::make()
                    ->label(__('admin.actions.delete')),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}