<?php declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';
    protected static ?string $title = 'customer_groups.relation_discounts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->maxLength(50),
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('discounts.name'))
                    ->searchable(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('discounts.code'))
                    ->sortable(),
                    ->badge(),
                    ->color('primary'),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('discounts.type'))
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed' => 'warning',
                        'free_shipping' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('discounts.value'))
                    ->numeric(),
                    ->suffix(fn ($record) => $record->type === 'percentage' ? '%' : '€'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('discounts.is_active'))
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'percentage' => __('discounts.percentage'),
                        'fixed' => __('discounts.fixed'),
                        'free_shipping' => __('discounts.free_shipping'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('discounts.is_active')),
            ])
            ->headerActions([
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