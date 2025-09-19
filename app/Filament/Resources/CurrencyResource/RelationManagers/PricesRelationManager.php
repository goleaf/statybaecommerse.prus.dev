<?php declare(strict_types=1);

namespace App\Filament\Resources\CurrencyResource\RelationManagers;

use App\Models\Price;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    protected static ?string $title = 'Prices';

    protected static ?string $modelLabel = 'Price';

    protected static ?string $pluralModelLabel = 'Prices';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('priceable_type')
                    ->options([
                        'App\Models\Product' => 'Product',
                        'App\Models\Variant' => 'Variant',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('priceable_id')
                    ->required(),
                    ->numeric(),
                Forms\Components\TextInput::make('amount')
                    ->required(),
                    ->numeric(),
                    ->step(0.01),
                Forms\Components\TextInput::make('compare_amount')
                    ->numeric(),
                    ->step(0.01),
                Forms\Components\TextInput::make('cost_amount')
                    ->numeric(),
                    ->step(0.01),
                Forms\Components\Select::make('type')
                    ->options([
                        'regular' => 'Regular',
                        'sale' => 'Sale',
                        'wholesale' => 'Wholesale',
                    ])
                    ->default('regular'),
                Forms\Components\DateTimePicker::make('starts_at'),
                Forms\Components\DateTimePicker::make('ends_at'),
                Forms\Components\Toggle::make('is_enabled')
                    ->default(true),
                Forms\Components\KeyValue::make('metadata')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('priceable_type')
                    ->badge(),
                    ->color('primary'),
                Tables\Columns\TextColumn::make('priceable_id')
                    ->numeric(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('EUR'),
                    ->sortable(),
                Tables\Columns\TextColumn::make('compare_amount')
                    ->money('EUR'),
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_amount')
                    ->money('EUR'),
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                    ->color(fn(string $state): string => match ($state) {
                        'regular' => 'gray',
                        'sale' => 'success',
                        'wholesale' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime(),
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                    ->sortable(),
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'regular' => 'Regular',
                        'sale' => 'Sale',
                        'wholesale' => 'Wholesale',
                    ]),
                Tables\Filters\TernaryFilter::make('is_enabled'),
                Tables\Filters\Filter::make('active')
                    ->query(fn(Builder $query): Builder => $query
                        ->where('is_enabled', true)
                        ->where(function ($q) {
                            $q
                                ->whereNull('starts_at')
                                ->orWhere('starts_at', '<=', now());
                        })
                        ->where(function ($q) {
                            $q
                                ->whereNull('ends_at')
                                ->orWhere('ends_at', '>=', now());
                        })),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
