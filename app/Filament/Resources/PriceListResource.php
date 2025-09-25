<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PriceListResource\Pages;
use BackedEnum;
use App\Models\PriceList;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * PriceListResource
 *
 * Filament v4 resource for PriceList management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;

    protected static UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('price_lists.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Products';
    }

    public static function getPluralModelLabel(): string
    {
        return __('price_lists.plural');
    }

    public static function getModelLabel(): string
    {
        return __('price_lists.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('price_lists.basic_information'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('price_lists.name'))
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label(__('price_lists.description'))
                        ->maxLength(1000)
                        ->rows(3),
                    Select::make('pricing_type')
                        ->label(__('price_lists.pricing_type'))
                        ->options([
                            'fixed' => __('price_lists.fixed'),
                            'tiered' => __('price_lists.tiered'),
                            'volume' => __('price_lists.volume'),
                        ])
                        ->required()
                        ->native(false)
                        ->live(),
                ]),
            Section::make(__('price_lists.tiered_pricing'))
                ->schema([
                    Repeater::make('tiers')
                        ->label(__('price_lists.tiers'))
                        ->schema([
                            TextInput::make('min_quantity')
                                ->label(__('price_lists.min_quantity'))
                                ->numeric()
                                ->required(),
                            TextInput::make('max_quantity')
                                ->label(__('price_lists.max_quantity'))
                                ->numeric(),
                            TextInput::make('price')
                                ->label(__('price_lists.price'))
                                ->numeric()
                                ->required()
                                ->prefix('€'),
                        ])
                        ->defaultItems(1)
                        ->addActionLabel(__('price_lists.add_tier'))
                        ->visible(fn(Forms\Get $get): bool => $get('pricing_type') === 'tiered'),
                ])
                ->visible(fn(Forms\Get $get): bool => $get('pricing_type') === 'tiered'),
            Section::make(__('price_lists.volume_pricing'))
                ->schema([
                    Repeater::make('volume_tiers')
                        ->label(__('price_lists.volume_tiers'))
                        ->schema([
                            TextInput::make('min_quantity')
                                ->label(__('price_lists.min_quantity'))
                                ->numeric()
                                ->required(),
                            TextInput::make('max_quantity')
                                ->label(__('price_lists.max_quantity'))
                                ->numeric(),
                            TextInput::make('price')
                                ->label(__('price_lists.price'))
                                ->numeric()
                                ->required()
                                ->prefix('€'),
                        ])
                        ->defaultItems(1)
                        ->addActionLabel(__('price_lists.add_tier'))
                        ->visible(fn(Forms\Get $get): bool => $get('pricing_type') === 'volume'),
                ])
                ->visible(fn(Forms\Get $get): bool => $get('pricing_type') === 'volume'),
            Section::make(__('price_lists.settings'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('price_lists.is_active'))
                        ->default(true),
                    DateTimePicker::make('valid_from')
                        ->label(__('price_lists.valid_from')),
                    DateTimePicker::make('valid_until')
                        ->label(__('price_lists.valid_until')),
                    Textarea::make('notes')
                        ->label(__('price_lists.notes'))
                        ->maxLength(500)
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('price_lists.name'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('pricing_type')
                    ->label(__('price_lists.pricing_type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'fixed' => 'success',
                        'tiered' => 'info',
                        'volume' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('description')
                    ->label(__('price_lists.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('price_lists.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('valid_from')
                    ->label(__('price_lists.valid_from'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('valid_until')
                    ->label(__('price_lists.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('price_lists.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('price_lists.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('pricing_type')
                    ->label(__('price_lists.pricing_type'))
                    ->options([
                        'fixed' => __('price_lists.fixed'),
                        'tiered' => __('price_lists.tiered'),
                        'volume' => __('price_lists.volume'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('price_lists.is_active'))
                    ->placeholder(__('price_lists.all_records'))
                    ->trueLabel(__('price_lists.active_only'))
                    ->falseLabel(__('price_lists.inactive_only')),
                Filter::make('valid_from')
                    ->label(__('price_lists.valid_from'))
                    ->form([
                        DateTimePicker::make('valid_from_from')
                            ->label(__('price_lists.valid_from_from')),
                        DateTimePicker::make('valid_from_until')
                            ->label(__('price_lists.valid_from_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['valid_from_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('valid_from', '>=', $date),
                            )
                            ->when(
                                $data['valid_from_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('valid_from', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListPriceLists::route('/'),
            'create' => Pages\CreatePriceList::route('/create'),
            'edit' => Pages\EditPriceList::route('/{record}/edit'),
        ];
    }
}
