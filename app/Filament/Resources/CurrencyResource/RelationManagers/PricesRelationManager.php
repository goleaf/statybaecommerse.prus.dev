<?php

declare(strict_types=1);

namespace App\Filament\Resources\CurrencyResource\RelationManagers;


use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;

final class PricesRelationManager extends BaseRelationManager
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
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->step(0.01),
                Forms\Components\TextInput::make('compare_amount')
                    ->numeric()
                    ->step(0.01),
                Forms\Components\TextInput::make('cost_amount')
                    ->numeric()
                    ->step(0.01),
                Forms\Components\Select::make('type')
                    ->options([
                        'regular'   => 'Regular',
                        'sale'      => 'Sale',
                        'wholesale' => 'Wholesale',
                    ])
                    ->default('regular'),
                SupportFlatpickr::makeDateTime('starts_at'),
                SupportFlatpickr::makeDateTime('ends_at'),
                Forms\Components\Toggle::make('is_enabled')
                    ->default(true),
                Forms\Components\KeyValue::make('metadata')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table   
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('priceable_type')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('priceable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('compare_amount')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_amount')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'regular'   => 'gray',
                        'sale'      => 'success',
                        'wholesale' => 'warning',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'regular'   => 'Regular',
                        'sale'      => 'Sale',
                        'wholesale' => 'Wholesale',
                    ]),
                Tables\Filters\TernaryFilter::make('is_enabled'),
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('is_enabled', true)
                        ->where(function ($q): void {
                            $q
                                ->whereNull('starts_at')
                                ->orWhere('starts_at', '<=', now());
                        })
                        ->where(function ($q): void {
                            $q
                                ->whereNull('ends_at')
                                ->orWhere('ends_at', '>=', now());
                        })),
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
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}