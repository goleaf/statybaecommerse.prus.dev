<?php declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroupResource\RelationManagers;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
final class PriceListsRelationManager extends RelationManager
{
    protected static string $relationship = 'priceLists';
    protected static ?string $title = 'customer_groups.relation_price_lists';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required(),
                    ->maxLength(255),
                Forms\Components\TextInput::make('currency')
                    ->maxLength(3),
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('price_lists.name'))
                    ->searchable(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label(__('price_lists.currency'))
                    ->sortable(),
                    ->badge(),
                    ->color('info'),
                Tables\Columns\TextColumn::make('is_default')
                    ->label(__('price_lists.is_default'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('price_lists.created_at'))
                    ->dateTime(),
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('price_lists.is_default')),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label(__('customer_groups.attach_price_list')),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label(__('customer_groups.detach_price_list')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label(__('customer_groups.detach_selected')),
                ]),
            ]);
    }
}
