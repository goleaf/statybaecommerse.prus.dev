<?php

declare (strict_types=1);
namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
/**
 * CartItemsRelationManager
 * 
 * Filament v4 resource for CartItemsRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $recordTitleAttribute
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CartItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'cartItems';
    protected static ?string $recordTitleAttribute = 'product.name';
    /**
     * Handle getTitle functionality with proper error handling.
     * @param mixed $ownerRecord
     * @param string $pageClass
     * @return string
     */
    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('admin.customers.cart_items');
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $form
     * @return Schema
     */
    public function form(Schema $form): Schema
    {
        return $schema->components([Section::make(__('admin.cart_items.cart_information'))->schema([Grid::make(2)->schema([Select::make('product_id')->label(__('admin.cart_items.fields.product'))->relationship('product', 'name')->searchable()->preload()->required(), TextInput::make('quantity')->label(__('admin.cart_items.fields.quantity'))->numeric()->required()->minValue(1)->default(1), TextInput::make('price')->label(__('admin.cart_items.fields.price'))->numeric()->prefix('€')->minValue(0)->required(), TextInput::make('total')->label(__('admin.cart_items.fields.total'))->numeric()->prefix('€')->minValue(0)->disabled()->dehydrated(false)])])]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('product.name')->label(__('admin.cart_items.fields.product'))->searchable()->sortable()->weight('bold')->url(fn($record) => route('filament.admin.resources.products.view', $record->product)), TextColumn::make('product.sku')->label(__('admin.cart_items.fields.sku'))->searchable()->sortable()->copyable()->copyMessage(__('admin.cart_items.fields.sku') . ' ' . __('admin.common.copied')), TextColumn::make('quantity')->label(__('admin.cart_items.fields.quantity'))->numeric()->sortable()->alignCenter()->badge()->color('info'), TextColumn::make('price')->label(__('admin.cart_items.fields.price'))->money('EUR')->sortable()->alignEnd(), TextColumn::make('total')->label(__('admin.cart_items.fields.total'))->money('EUR')->sortable()->alignEnd()->color('success')->weight('bold'), TextColumn::make('created_at')->label(__('admin.cart_items.fields.created_at'))->dateTime('d/m/Y H:i')->sortable()->since()->tooltip(fn($record) => $record->created_at?->format('d/m/Y H:i:s')), TextColumn::make('updated_at')->label(__('admin.cart_items.fields.updated_at'))->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true)->tooltip(fn($record) => $record->updated_at?->format('d/m/Y H:i:s'))])->filters([SelectFilter::make('product')->label(__('admin.cart_items.filters.product'))->relationship('product', 'name')->searchable()->preload(), DateFilter::make('created_at')->label(__('admin.cart_items.filters.created_at'))->displayFormat('d/m/Y'), DateFilter::make('updated_at')->label(__('admin.cart_items.filters.updated_at'))->displayFormat('d/m/Y')])->headerActions([Tables\Actions\CreateAction::make()->label(__('admin.cart_items.create'))->mutateFormDataUsing(function (array $data): array {
            $data['user_id'] = $this->ownerRecord->id;
            return $data;
        })])->actions([ViewAction::make()->label(__('admin.actions.view')), EditAction::make()->label(__('admin.actions.edit')), DeleteAction::make()->label(__('admin.actions.delete'))])->defaultSort('created_at', 'desc')->striped()->paginated([10, 25, 50]);
    }
}