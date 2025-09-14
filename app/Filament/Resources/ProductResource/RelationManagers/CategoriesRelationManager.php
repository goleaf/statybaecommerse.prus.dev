<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
final class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';
    protected static ?string $title = 'Categories';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('category_id')
                    ->label(__('admin.products.fields.categories'))
                    ->relationship('category', 'name')
                    ->searchable(),
                    ->preload(),
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->label(__('admin.products.fields.sort_order'))
                    ->numeric(),
                    ->default(0),
                Forms\Components\Toggle::make('is_primary')
                    ->label(__('admin.products.fields.is_primary'))
                    ->default(false),
            ]);
    }
    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('admin.products.fields.category'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.description')
                    ->label(__('admin.products.fields.description'))
                    ->limit(50),
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('sort_order')
                Tables\Columns\IconColumn::make('is_primary')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.products.fields.created_at'))
                    ->dateTime(),
                    ->sortable(),
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label(__('admin.products.fields.is_primary')),
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
                        Forms\Components\Toggle::make('is_primary')
                            ->label(__('admin.products.fields.is_primary'))
                            ->default(false),
                    ]),
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->defaultSort("created_at", "desc");
    }
}
    }
}
