<?php

declare (strict_types=1);
namespace App\Filament\Resources\ProductHistoryResource\Widgets;

use App\Models\ProductHistory;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
/**
 * RecentProductChangesWidget
 * 
 * Filament v4 resource for RecentProductChangesWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property int|string|array $columnSpan
 * @property string|null $heading
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class RecentProductChangesWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Recent Product Changes';
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query(ProductHistory::query()->with(['product:id,name,sku', 'user:id,name'])->latest()->limit(10))->columns([Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable()->sortable()->limit(25), Tables\Columns\TextColumn::make('product.sku')->label('SKU')->searchable()->sortable()->toggleable(), Tables\Columns\BadgeColumn::make('action')->colors(['success' => 'created', 'warning' => 'updated', 'danger' => 'deleted', 'info' => 'restored', 'primary' => 'price_changed', 'secondary' => 'stock_updated', 'gray' => 'status_changed'])->formatStateUsing(fn(string $state): string => match ($state) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'price_changed' => 'Price Changed',
            'stock_updated' => 'Stock Updated',
            'status_changed' => 'Status Changed',
            default => ucfirst($state),
        }), Tables\Columns\TextColumn::make('field_name')->label('Field')->searchable()->sortable()->placeholder('N/A'), Tables\Columns\TextColumn::make('description')->limit(40)->tooltip(function (Tables\Columns\TextColumn $column): ?string {
            $state = $column->getState();
            return strlen($state) > 40 ? $state : null;
        }), Tables\Columns\TextColumn::make('user.name')->label('User')->searchable()->sortable()->placeholder('System'), Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime()->sortable()->since()])->actions([Tables\Actions\Action::make('view')->label('View')->icon('heroicon-o-eye')->url(fn(ProductHistory $record): string => route('filament.admin.resources.product-histories.view', $record))->openUrlInNewTab()])->paginated(false);
    }
}