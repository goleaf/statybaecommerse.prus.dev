<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use App\Support\Filament\Components\Flatpickr;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Filament\Components\Flatpickr;
use Filament\Schemas\Schema;

final class RedemptionsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'redemptions';

    protected static ?string $title = 'Discount Redemptions';

    protected static ?string $modelLabel = 'Redemption';

    protected static ?string $pluralModelLabel = 'Redemptions';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Redemption Details')
                ->schema([
                    Select::make('code_id')
                        ->relationship('code', 'code')
                        ->label('Discount Code')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('Customer')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('order_id')
                        ->relationship('order', 'number')
                        ->label('Order')
                        ->searchable()
                        ->preload(),
                    TextInput::make('amount_saved')
                        ->label('Amount Saved')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('€')
                        ->required(),
                    TextInput::make('currency_code')
                        ->label('Currency')
                        ->length(3)
                        ->default('EUR')
                        ->required(),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending'   => 'Pending',
                            'redeemed'  => 'Redeemed',
                            'expired'   => 'Expired',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('pending')
                        ->required(),
                    Flatpickr::makeDateTime('redeemed_at')
                        ->label('Redeemed At')
                        ->seconds(false)
                        ->required(),
                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3),
                    KeyValue::make('metadata')
                        ->label('Metadata')
                        ->keyLabel('Key')
                        ->valueLabel('Value')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('code.code')
            ->columns([
                TextColumn::make('code.code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.number')
                    ->label('Order')
                    ->formatStateUsing(fn (?string $state) => $state ? "#{$state}" : '-')
                    ->sortable(),
                TextColumn::make('amount_saved')
                    ->label('Amount Saved')
                    ->money(fn ($record) => $record->currency_code ?? 'EUR')
                    ->sortable(),
                TextColumn::make('currency_code')
                    ->label('Currency')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'redeemed',
                        'warning' => 'pending',
                        'danger'  => 'expired',
                        'gray'    => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-m-check-circle'         => 'redeemed',
                        'heroicon-m-clock'                => 'pending',
                        'heroicon-m-x-mark'               => 'cancelled',
                        'heroicon-m-exclamation-triangle' => 'expired',
                    ])
                    ->sortable(),
                TextColumn::make('redeemed_at')
                    ->label('Redeemed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'   => 'Pending',
                        'redeemed'  => 'Redeemed',
                        'expired'   => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('currency_code')
                    ->label('Currency')
                    ->options([
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                        'GBP' => 'GBP',
                    ]),
                Filter::make('has_order')
                    ->label('Has Order')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('order_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('redeemed_at', 'desc');
    }
}