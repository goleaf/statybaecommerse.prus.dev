<?php declare(strict_types=1);

namespace App\Filament\Resources\VariantStockResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    protected static ?string $title = 'Stock Movements';

    protected static ?string $modelLabel = 'Stock Movement';

    protected static ?string $pluralModelLabel = 'Stock Movements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('quantity')
                    ->label(__('inventory.quantity'))
                    ->numeric(),
                    ->required(),

                Select::make('type')
                    ->label(__('inventory.type'))
                    ->options([
                        'in' => __('inventory.stock_in'),
                        'out' => __('inventory.stock_out'),
                    ])
                    ->required(),

                Select::make('reason')
                    ->label(__('inventory.reason'))
                    ->options([
                        'sale' => __('inventory.reason_sale'),
                        'return' => __('inventory.reason_return'),
                        'adjustment' => __('inventory.reason_adjustment'),
                        'manual_adjustment' => __('inventory.reason_manual_adjustment'),
                        'restock' => __('inventory.reason_restock'),
                        'damage' => __('inventory.reason_damage'),
                        'theft' => __('inventory.reason_theft'),
                        'transfer' => __('inventory.reason_transfer'),
                    ])
                    ->required(),

                TextInput::make('reference')
                    ->label(__('inventory.reference')),

                Textarea::make('notes')
                    ->label(__('inventory.notes'))
                    ->rows(3),

                DateTimePicker::make('moved_at')
                    ->label(__('inventory.moved_at'))
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('quantity')
            ->columns([
                TextColumn::make('quantity')
                    ->label(__('inventory.quantity'))
                    ->numeric(),
                    ->sortable(),

                BadgeColumn::make('type')
                    ->label(__('inventory.type'))
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                    ]),

                TextColumn::make('reason')
                    ->label(__('inventory.reason'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sale' => __('inventory.reason_sale'),
                        'return' => __('inventory.reason_return'),
                        'adjustment' => __('inventory.reason_adjustment'),
                        'manual_adjustment' => __('inventory.reason_manual_adjustment'),
                        'restock' => __('inventory.reason_restock'),
                        'damage' => __('inventory.reason_damage'),
                        'theft' => __('inventory.reason_theft'),
                        'transfer' => __('inventory.reason_transfer'),
                        default => $state,
                    }),

                TextColumn::make('reference')
                    ->label(__('inventory.reference'))
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label(__('inventory.user'))
                    ->searchable(),

                TextColumn::make('moved_at')
                    ->label(__('inventory.moved_at'))
                    ->dateTime(),
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('inventory.created_at'))
                    ->dateTime(),
                    ->sortable(),
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('inventory.type'))
                    ->options([
                        'in' => __('inventory.stock_in'),
                        'out' => __('inventory.stock_out'),
                    ]),

                SelectFilter::make('reason')
                    ->label(__('inventory.reason'))
                    ->options([
                        'sale' => __('inventory.reason_sale'),
                        'return' => __('inventory.reason_return'),
                        'adjustment' => __('inventory.reason_adjustment'),
                        'manual_adjustment' => __('inventory.reason_manual_adjustment'),
                        'restock' => __('inventory.reason_restock'),
                        'damage' => __('inventory.reason_damage'),
                        'theft' => __('inventory.reason_theft'),
                        'transfer' => __('inventory.reason_transfer'),
                    ]),

                Filter::make('recent')
                    ->label(__('inventory.recent_movements'))
                    ->query(fn (Builder $query): Builder => $query->recent()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkDeleteAction::make(),
            ])
            ->defaultSort("created_at", "desc");
    }
}
    }
}

