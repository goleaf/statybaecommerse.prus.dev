<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Collection Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Collection::class, 'slug', ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Collection Rules')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'manual' => 'Manual',
                                'automatic' => 'Automatic',
                            ])
                            ->required()
                            ->live()
                            ->default('manual'),
                        Forms\Components\Textarea::make('conditions')
                            ->visible(fn(Forms\Get $get): bool => $get('type') === 'automatic')
                            ->helperText('JSON conditions for automatic collection')
                            ->rows(5),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Translations')
                    ->schema([
                        Forms\Components\Repeater::make('translations')
                            ->relationship('translations')
                            ->schema([
                                Forms\Components\Select::make('locale')
                                    ->options([
                                        'en' => 'English',
                                        'lt' => 'Lithuanian',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->maxLength(1000)
                                    ->rows(3),
                            ])
                            ->columns(3)
                            ->defaultItems(2)
                            ->addActionLabel('Add Translation')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'manual' => 'primary',
                        'automatic' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'manual' => 'Manual',
                        'automatic' => 'Automatic',
                    ]),
                Tables\Filters\Filter::make('enabled')
                    ->query(fn($query) => $query->where('is_enabled', true)),
                Tables\Filters\Filter::make('featured')
                    ->query(fn($query) => $query->where('is_featured', true)),
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
            ]);
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
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'view' => Pages\ViewCollection::route('/{record}'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
