<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Collection;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
final class CollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'collections';
    protected static ?string $title = 'Product Collections';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('collection_id')
                    ->label(__('admin.products.fields.collections'))
                    ->relationship('collection', 'name')
                    ->searchable(),
                    ->preload(),
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->label(__('admin.products.fields.sort_order'))
                    ->numeric(),
                    ->default(0),
                Forms\Components\Toggle::make('is_featured')
                    ->label(__('admin.products.fields.is_featured'))
                    ->default(false),
                Forms\Components\DateTimePicker::make('featured_until')
                    ->label(__('admin.products.fields.featured_until'))
                    ->visible(fn(Forms\Get $get): bool => $get('is_featured')),
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('collection.name')
                    ->label(__('admin.products.fields.collection'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('collection.description')
                    ->label(__('admin.products.fields.description'))
                    ->limit(50),
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('collection.type')
                    ->label(__('admin.products.fields.collection_type'))
                    ->badge(),
                    ->color(fn(string $state): string => match ($state) {
                        'manual' => 'info',
                        'automatic' => 'success',
                        'dynamic' => 'warning',
                        default => 'gray',
                Tables\Columns\TextColumn::make('sort_order')
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('featured_until')
                    ->dateTime(),
                    ->sortable(),
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.products.fields.created_at'))
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('collection')
                    ->preload(),
                Tables\Filters\SelectFilter::make('collection_type')
                    ->options([
                        'manual' => __('admin.collections.types.manual'),
                        'automatic' => __('admin.collections.types.automatic'),
                        'dynamic' => __('admin.collections.types.dynamic'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('admin.products.fields.is_featured')),
                Tables\Filters\Filter::make('featured_expired')
                    ->label(__('admin.products.filters.featured_expired'))
                    ->query(fn(Builder $query): Builder => $query->where('featured_until', '<', now())),
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        $action
                            ->getRecordSelect()
                            ->searchable(),
                            ->preload(),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('admin.products.fields.sort_order'))
                            ->numeric(),
                            ->default(0),
                        Forms\Components\Toggle::make('is_featured')
                            ->label(__('admin.products.fields.is_featured'))
                            ->default(false),
                        Forms\Components\DateTimePicker::make('featured_until')
                            ->label(__('admin.products.fields.featured_until'))
                            ->visible(fn(Forms\Get $get): bool => $get('is_featured')),
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ->defaultSort("created_at", "desc");
    }
}
}
