<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages;
use App\Models\Attribute;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

final class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('translations.attribute_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('translations.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('translations.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Attribute::class, 'slug', ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->label(__('translations.description'))
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\Select::make('type')
                            ->label(__('translations.type'))
                            ->options([
                                'text' => __('translations.text'),
                                'number' => __('translations.number'),
                                'boolean' => __('translations.boolean'),
                                'select' => __('translations.select'),
                                'multiselect' => __('translations.multiselect'),
                                'color' => __('translations.color'),
                                'date' => __('translations.date'),
                            ])
                            ->required()
                            ->default('text'),
                        Forms\Components\Toggle::make('is_required')
                            ->label(__('translations.required'))
                            ->default(false),
                        Forms\Components\Toggle::make('is_filterable')
                            ->label(__('translations.filterable'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_searchable')
                            ->label(__('translations.searchable'))
                            ->default(false),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('translations.sort_order'))
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('translations.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('translations.slug'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('translations.type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'text' => 'gray',
                        'number' => 'blue',
                        'boolean' => 'green',
                        'select' => 'yellow',
                        'multiselect' => 'orange',
                        'color' => 'purple',
                        'date' => 'red',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('values_count')
                    ->counts('values')
                    ->label(__('translations.values'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_required')
                    ->label(__('translations.required'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_filterable')
                    ->label(__('translations.filterable'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_searchable')
                    ->label(__('translations.searchable'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('translations.type'))
                    ->options([
                        'text' => __('translations.text'),
                        'number' => __('translations.number'),
                        'boolean' => __('translations.boolean'),
                        'select' => __('translations.select'),
                        'multiselect' => __('translations.multiselect'),
                        'color' => __('translations.color'),
                        'date' => __('translations.date'),
                    ]),
                Tables\Filters\Filter::make('required')
                    ->label(__('translations.required_only'))
                    ->query(fn(Builder $query): Builder => $query->where('is_required', true)),
                Tables\Filters\Filter::make('filterable')
                    ->label(__('translations.filterable_only'))
                    ->query(fn(Builder $query): Builder => $query->where('is_filterable', true)),
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
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'view' => Pages\ViewAttribute::route('/{record}'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
