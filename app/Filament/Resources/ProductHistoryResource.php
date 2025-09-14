<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductHistoryResource\Pages;
use App\Filament\Resources\ProductHistoryResource\RelationManagers;
use App\Models\ProductHistory;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Enums\NavigationGroup;

final class ProductHistoryResource extends Resource
{
    protected static ?string $model = ProductHistory::class;

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Products->label();
    }

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema {
        return $schema->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('sku')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Select::make('action')
                            ->options([
                                'created' => 'Created',
                                'updated' => 'Updated',
                                'deleted' => 'Deleted',
                                'restored' => 'Restored',
                                'price_changed' => 'Price Changed',
                                'stock_updated' => 'Stock Updated',
                                'status_changed' => 'Status Changed',
                            ])
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('field_name')
                            ->maxLength(255)
                            ->placeholder('e.g., price, stock_quantity, status'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Change Details')
                    ->schema([
                        Forms\Components\KeyValue::make('old_value')
                            ->label('Old Value')
                            ->nullable(),

                        Forms\Components\KeyValue::make('new_value')
                            ->label('New Value')
                            ->nullable(),

                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->nullable(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Technical Information')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\Textarea::make('user_agent')
                            ->label('User Agent')
                            ->maxLength(65535)
                            ->nullable()
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('Updated At')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->placeholder('System'),

                Tables\Columns\BadgeColumn::make('action')
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                        'info' => 'restored',
                        'primary' => 'price_changed',
                        'secondary' => 'stock_updated',
                        'gray' => 'status_changed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                        'price_changed' => 'Price Changed',
                        'stock_updated' => 'Stock Updated',
                        'status_changed' => 'Status Changed',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('field_name')
                    ->label('Field')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('old_value')
                    ->label('Old Value')
                    ->formatStateUsing(fn ($state) => $state ? (is_array($state) ? json_encode($state) : $state) : 'N/A')
                    ->limit(20)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 20 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('new_value')
                    ->label('New Value')
                    ->formatStateUsing(fn ($state) => $state ? (is_array($state) ? json_encode($state) : $state) : 'N/A')
                    ->limit(20)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 20 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                        'price_changed' => 'Price Changed',
                        'stock_updated' => 'Stock Updated',
                        'status_changed' => 'Status Changed',
                    ]),

                Tables\Filters\SelectFilter::make('field_name')
                    ->options([
                        'name' => 'Name',
                        'price' => 'Price',
                        'stock_quantity' => 'Stock Quantity',
                        'status' => 'Status',
                        'is_visible' => 'Visibility',
                        'description' => 'Description',
                        'categories' => 'Categories',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('significant_changes')
                    ->label('Significant Changes Only')
                    ->query(fn (Builder $query): Builder => $query->whereIn('field_name', [
                        'price', 'sale_price', 'stock_quantity', 'status', 'is_visible'
                    ])),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
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
            'index' => Pages\ListProductHistories::route('/'),
            'create' => Pages\CreateProductHistory::route('/create'),
            'view' => Pages\ViewProductHistory::route('/{record}'),
            'edit' => Pages\EditProductHistory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->description ?: "Product History #{$record->id}";
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Product' => $record->product?->name,
            'Action' => $record->action,
            'Field' => $record->field_name,
            'Date' => $record->created_at?->format('M j, Y g:i A'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['description', 'action', 'field_name', 'product.name', 'user.name'];
    }
}
