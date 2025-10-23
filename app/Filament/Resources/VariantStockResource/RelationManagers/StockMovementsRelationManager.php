<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantStockResource\RelationManagers;


use Filament\Schemas\Schema;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Filament\Components\Flatpickr;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
class StockMovementsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'stockMovements';

    protected static ?string $title = 'Stock Movements';

    protected static ?string $modelLabel = 'Stock Movement';

    protected static ?string $pluralModelLabel = 'Stock Movements';

    public function form(Schema $schema): Schema   
    {
        return $schema
            ->components([
                TextInput::make('quantity')
                    ->label(__('inventory.quantity'))
                    ->numeric()
                    ->required(),
                Select::make('type')
                    ->label(__('inventory.type'))
                    ->options([
                        'in'  => __('inventory.stock_in'),
                        'out' => __('inventory.stock_out'),
                    ])
                    ->required(),
                Select::make('reason')
                    ->label(__('inventory.reason'))
                    ->options([
                        'sale'              => __('inventory.reason_sale'),
                        'return'            => __('inventory.reason_return'),
                        'adjustment'        => __('inventory.reason_adjustment'),
                        'manual_adjustment' => __('inventory.reason_manual_adjustment'),
                        'restock'           => __('inventory.reason_restock'),
                        'damage'            => __('inventory.reason_damage'),
                        'theft'             => __('inventory.reason_theft'),
                        'transfer'          => __('inventory.reason_transfer'),
                    ])
                    ->required(),
                TextInput::make('reference')
                    ->label(__('inventory.reference')),
                Textarea::make('notes')
                    ->label(__('inventory.notes'))
                    ->rows(3),
                Flatpickr::makeDateTime('moved_at')
                    ->label(__('inventory.moved_at'))
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table   
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('quantity')
            ->columns([
                TextColumn::make('quantity')
                    ->label(__('inventory.quantity'))
                    ->numeric()
                    ->sortable(),
                BadgeColumn::make('type')
                    ->label(__('inventory.type'))
                    ->colors([
                        'success' => 'in',
                        'danger'  => 'out',
                    ]),
                TextColumn::make('reason')
                    ->label(__('inventory.reason'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sale'              => __('inventory.reason_sale'),
                        'return'            => __('inventory.reason_return'),
                        'adjustment'        => __('inventory.reason_adjustment'),
                        'manual_adjustment' => __('inventory.reason_manual_adjustment'),
                        'restock'           => __('inventory.reason_restock'),
                        'damage'            => __('inventory.reason_damage'),
                        'theft'             => __('inventory.reason_theft'),
                        'transfer'          => __('inventory.reason_transfer'),
                        default             => $state,
                    }),
                TextColumn::make('reference')
                    ->label(__('inventory.reference'))
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label(__('inventory.user'))
                    ->searchable(),
                TextColumn::make('moved_at')
                    ->label(__('inventory.moved_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('inventory.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('inventory.type'))
                    ->options([
                        'in'  => __('inventory.stock_in'),
                        'out' => __('inventory.stock_out'),
                    ]),
                SelectFilter::make('reason')
                    ->label(__('inventory.reason'))
                    ->options([
                        'sale'              => __('inventory.reason_sale'),
                        'return'            => __('inventory.reason_return'),
                        'adjustment'        => __('inventory.reason_adjustment'),
                        'manual_adjustment' => __('inventory.reason_manual_adjustment'),
                        'restock'           => __('inventory.reason_restock'),
                        'damage'            => __('inventory.reason_damage'),
                        'theft'             => __('inventory.reason_theft'),
                        'transfer'          => __('inventory.reason_transfer'),
                    ]),
                Filter::make('recent')
                    ->label(__('inventory.recent_movements'))
                    ->query(fn (Builder $query): Builder => $query->recent()),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkDeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}