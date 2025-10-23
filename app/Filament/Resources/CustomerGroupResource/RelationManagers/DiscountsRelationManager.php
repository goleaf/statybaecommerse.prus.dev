<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

final class DiscountsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'discounts';

    protected static ?string $title = 'customer_groups.relation_discounts';

    public function form(Schema $form): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->maxLength(50),
            ]);
    }

    public function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('discounts.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('discounts.code'))
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('discounts.type'))
                    ->color(fn (string $state): string => match ($state) {
                        'percentage'    => 'success',
                        'fixed'         => 'warning',
                        'free_shipping' => 'info',
                        default         => 'gray',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('discounts.value'))
                    ->numeric()
                    ->suffix(fn ($record) => $record->type === 'percentage' ? '%' : '€'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('discounts.is_active'))
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage'    => __('discounts.percentage'),
                        'fixed'         => __('discounts.fixed'),
                        'free_shipping' => __('discounts.free_shipping'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('discounts.is_active')),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                Tables\Actions\AttachAction::make()
                    ->label(__('customer_groups.attach_discount')),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label(__('customer_groups.detach_discount')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label(__('customer_groups.detach_selected')),
                ]),
            ]);
    }
}