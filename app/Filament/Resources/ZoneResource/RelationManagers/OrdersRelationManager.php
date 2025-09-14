<?php

declare (strict_types=1);
namespace App\Filament\Resources\ZoneResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
/**
 * OrdersRelationManager
 * 
 * Filament v4 resource for OrdersRelationManager management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $relationship
 * @property string|null $title
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title = 'zones.orders';
    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $form
     * @return Schema
     */
    public function form(Schema $form): Schema
    {
        return $schema->components([Forms\Components\TextInput::make('order_number')->label(__('orders.order_number'))->required()->maxLength(255), Forms\Components\Select::make('status')->label(__('orders.status'))->options(['pending' => __('orders.status_pending'), 'processing' => __('orders.status_processing'), 'shipped' => __('orders.status_shipped'), 'delivered' => __('orders.status_delivered'), 'cancelled' => __('orders.status_cancelled')])->required(), Forms\Components\TextInput::make('total_amount')->label(__('orders.total_amount'))->numeric()->prefix('€')->required()]);
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('order_number')->columns([Tables\Columns\TextColumn::make('order_number')->label(__('orders.order_number'))->searchable()->sortable(), Tables\Columns\TextColumn::make('customer.name')->label(__('orders.customer'))->searchable()->sortable(), Tables\Columns\TextColumn::make('status')->label(__('orders.status'))->badge()->color(fn(string $state): string => match ($state) {
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        }), Tables\Columns\TextColumn::make('total_amount')->label(__('orders.total_amount'))->money('EUR')->sortable(), Tables\Columns\TextColumn::make('created_at')->label(__('orders.created_at'))->dateTime()->sortable()])->filters([Tables\Filters\SelectFilter::make('status')->label(__('orders.status'))->options(['pending' => __('orders.status_pending'), 'processing' => __('orders.status_processing'), 'shipped' => __('orders.status_shipped'), 'delivered' => __('orders.status_delivered'), 'cancelled' => __('orders.status_cancelled')])])->headerActions([Tables\Actions\CreateAction::make()->label(__('zones.create_order'))])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}