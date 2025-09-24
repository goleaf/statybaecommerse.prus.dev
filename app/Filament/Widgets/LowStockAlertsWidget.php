<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class LowStockAlertsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Mažų atsargų įspėjimai';

    public function mount(): void
    {
        abort_unless(auth()->check() && auth()->user()->hasAnyRole(['admin', 'super_admin']), 403);
    }

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'super_admin']);
    }

    public function getHeading(): ?string
    {
        return self::$heading;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('is_visible', true)
                    ->where('manage_stock', true)
                    ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->orderBy('stock_quantity', 'asc')
            )
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('sku')->searchable(),
                TextColumn::make('stock_quantity')->sortable(),
                TextColumn::make('low_stock_threshold')->sortable(),
                BadgeColumn::make('status')
                    ->label(__('translations.status'))
                    ->getStateUsing(fn (Product $record): string => $record->stock_quantity <= 0 ? 'out_of_stock' : 'low_stock')
                    ->colors([
                        'danger' => 'out_of_stock',
                        'warning' => 'low_stock',
                    ]),
            ])
            ->actions([
                Action::make('restock')
                    ->form([
                        TextInput::make('quantity')
                            ->numeric()
                            ->required(),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $record->increment('stock_quantity', (int) ($data['quantity'] ?? 0));
                    }),
                Action::make('edit'),
            ])
            ->emptyStateHeading(__('No Low Stock Items'))
            ->emptyStateDescription(__('All products are well stocked!'));
    }
}
