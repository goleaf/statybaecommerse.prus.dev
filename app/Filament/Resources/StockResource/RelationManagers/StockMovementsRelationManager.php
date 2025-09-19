<?php declare(strict_types=1);

namespace App\Filament\Resources\StockResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    protected static ?string $title = 'inventory.stock_movements';

    protected static ?string $modelLabel = 'inventory.stock_movement';

    protected static ?string $pluralModelLabel = 'inventory.stock_movements';

    public function getTitle(): string
    {
        return __('inventory.stock_movements');
    }

    public function getModelLabel(): string
    {
        return __('inventory.stock_movement');
    }

    public function getPluralModelLabel(): string
    {
        return __('inventory.stock_movements');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('quantity')
                    ->label(__('inventory.quantity'))
                    ->numeric(),
                    ->required(),
                    ->helperText(__('inventory.quantity_help')),

                Select::make('type')
                    ->label(__('inventory.movement_type'))
                    ->options([
                        'in' => __('inventory.stock_in'),
                        'out' => __('inventory.stock_out'),
                    ])
                    ->required(),
                    ->live(),

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
                    ->searchable(),

                TextInput::make('reference')
                    ->label(__('inventory.reference'))
                    ->maxLength(255),
                    ->helperText(__('inventory.reference_help')),

                Textarea::make('notes')
                    ->label(__('inventory.notes'))
                    ->rows(3),
                    ->maxLength(1000),

                DateTimePicker::make('moved_at')
                    ->label(__('inventory.moved_at'))
                    ->default(now())
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('reference')
            ->columns([
                TextColumn::make('moved_at')
                    ->label(__('inventory.moved_at'))
                    ->dateTime(),
                    ->sortable(),
                    ->weight('bold'),

                BadgeColumn::make('type')
                    ->label(__('inventory.movement_type'))
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                    ])
                    ->formatStateUsing(fn (string $state): string => 
                        __('inventory.' . $state)
                    ),

                TextColumn::make('quantity')
                    ->label(__('inventory.quantity'))
                    ->sortable(),
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($record): string => 
                        $record->type === 'in' ? 'success' : 'danger'
                    ),

                BadgeColumn::make('reason')
                    ->label(__('inventory.reason'))
                    ->colors([
                        'primary' => 'sale',
                        'success' => 'return',
                        'warning' => 'adjustment',
                        'info' => 'manual_adjustment',
                        'success' => 'restock',
                        'danger' => 'damage',
                        'danger' => 'theft',
                        'info' => 'transfer',
                    ])
                    ->formatStateUsing(fn (string $state): string => 
                        __('inventory.reason_' . $state)
                    ),

                TextColumn::make('reference')
                    ->label(__('inventory.reference'))
                    ->searchable(),
                    ->limit(30)
                    ->placeholder(__('inventory.no_reference')),

                TextColumn::make('user.name')
                    ->label(__('inventory.user'))
                    ->searchable(),
                    ->sortable(),
                    ->badge(),
                    ->color('gray')
                    ->placeholder(__('inventory.system')),

                TextColumn::make('notes')
                    ->label(__('inventory.notes'))
                    ->limit(50),
                    ->placeholder(__('inventory.no_notes'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('inventory.movement_type'))
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
                    ])
                    ->multiple(),

                Filter::make('recent')
                    ->label(__('inventory.recent_movements'))
                    ->query(fn (Builder $query): Builder => $query->recent(7)),

                Filter::make('this_month')
                    ->label(__('inventory.this_month'))
                    ->query(fn (Builder $query): Builder => 
                        $query->where('moved_at', '>=', now()->startOfMonth())
                    ),

                Filter::make('this_year')
                    ->label(__('inventory.this_year'))
                    ->query(fn (Builder $query): Builder => 
                        $query->where('moved_at', '>=', now()->startOfYear())
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    })
                    ->after(function ($record) {
                        // Update the variant inventory stock based on movement
                        $variantInventory = $this->ownerRecord;
                        $quantity = $record->quantity;
                        
                        if ($record->type === 'in') {
                            $variantInventory->increment('stock', $quantity);
                        } else {
                            $variantInventory->decrement('stock', $quantity);
                        }
                        
                        // Update last restocked/sold timestamps
                        if ($record->type === 'in' && in_array($record->reason, ['restock', 'return'])) {
                            $variantInventory->update(['last_restocked_at' => $record->moved_at]);
                        } elseif ($record->type === 'out' && $record->reason === 'sale') {
                            $variantInventory->update(['last_sold_at' => $record->moved_at]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->after(function ($record) {
                        // Reverse the stock movement when deleting
                        $variantInventory = $this->ownerRecord;
                        $quantity = $record->quantity;
                        
                        if ($record->type === 'in') {
                            $variantInventory->decrement('stock', $quantity);
                        } else {
                            $variantInventory->increment('stock', $quantity);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function ($records) {
                            // Reverse all stock movements when bulk deleting
                            $variantInventory = $this->ownerRecord;
                            
                            foreach ($records as $record) {
                                $quantity = $record->quantity;
                                
                                if ($record->type === 'in') {
                                    $variantInventory->decrement('stock', $quantity);
                                } else {
                                    $variantInventory->increment('stock', $quantity);
                                }
                            }
                        }),
                ]),
            ])
            ->groups([
                Group::make('type')
                    ->label(__('inventory.group_by_type'))
                    ->collapsible(),
                
                Group::make('reason')
                    ->label(__('inventory.group_by_reason'))
                    ->collapsible(),
                
                Group::make('moved_at')
                    ->label(__('inventory.group_by_date'))
                    ->date()
                    ->collapsible(),
            ])
            ->defaultSort('moved_at', 'desc')
            ->poll('30s')
            ->deferLoading()
            ->striped();
    }
}
