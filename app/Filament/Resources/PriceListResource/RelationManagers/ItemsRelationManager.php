<?php

declare (strict_types=1);
namespace App\Filament\Resources\PriceListResource\RelationManagers;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
/**
 * ItemsRelationManager
 * 
 * Filament v4 resource for ItemsRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $recordTitleAttribute
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $recordTitleAttribute = 'product_id';
    /**
     * Handle getTitle functionality with proper error handling.
     * @param mixed $ownerRecord
     * @param string $pageClass
     * @return string
     */
    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('admin.price_lists.items');
    }
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $form
     * @return Schema
     */
    public function form(Schema $form): Schema
    {
        return $schema->schema([Section::make(__('admin.price_lists.item_information'))->schema([Grid::make(2)->schema([Select::make('product_id')->label(__('admin.price_lists.fields.product'))->relationship('product', 'name')->required()->searchable()->preload()->live()->afterStateUpdated(function ($state, Forms\Set $set) {
            if ($state) {
                $product = Product::find($state);
                if ($product) {
                    $set('net_amount', $product->price);
                }
            }
        }), Select::make('variant_id')->label(__('admin.price_lists.fields.variant'))->relationship('variant', 'name')->searchable()->preload()->live()->afterStateUpdated(function ($state, Forms\Set $set) {
            if ($state) {
                $variant = ProductVariant::find($state);
                if ($variant) {
                    $set('net_amount', $variant->price);
                }
            }
        }), TextInput::make('net_amount')->label(__('admin.price_lists.fields.net_amount'))->required()->numeric()->prefix('€')->step(0.01), TextInput::make('compare_amount')->label(__('admin.price_lists.fields.compare_amount'))->numeric()->prefix('€')->step(0.01)])])]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('product.name')->label(__('admin.price_lists.fields.product'))->searchable()->sortable(), TextColumn::make('variant.name')->label(__('admin.price_lists.fields.variant'))->searchable()->sortable()->placeholder(__('admin.common.none')), TextColumn::make('net_amount')->label(__('admin.price_lists.fields.net_amount'))->money('EUR')->alignEnd()->sortable(), TextColumn::make('compare_amount')->label(__('admin.price_lists.fields.compare_amount'))->money('EUR')->alignEnd()->sortable()->placeholder(__('admin.common.none')), BadgeColumn::make('discount_percentage')->label(__('admin.price_lists.fields.discount_percentage'))->getStateUsing(function ($record) {
            if (!$record->compare_amount || $record->compare_amount <= $record->net_amount) {
                return null;
            }
            return round(($record->compare_amount - $record->net_amount) / $record->compare_amount * 100) . '%';
        })->color('success')->placeholder(__('admin.common.none')), TextColumn::make('created_at')->label(__('admin.price_lists.fields.created_at'))->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true)])->filters([SelectFilter::make('product_id')->label(__('admin.price_lists.fields.product'))->relationship('product', 'name')->searchable()->preload(), SelectFilter::make('variant_id')->label(__('admin.price_lists.fields.variant'))->relationship('variant', 'name')->searchable()->preload()])->headerActions([Tables\Actions\CreateAction::make()->label(__('admin.price_lists.create_item'))->mutateFormDataUsing(function (array $data): array {
            $data['price_list_id'] = $this->ownerRecord->id;
            return $data;
        })])->actions([ViewAction::make()->label(__('admin.actions.view')), EditAction::make()->label(__('admin.actions.edit')), DeleteAction::make()->label(__('admin.actions.delete'))])->defaultSort('created_at', 'desc')->striped()->paginated([10, 25, 50]);
    }
}