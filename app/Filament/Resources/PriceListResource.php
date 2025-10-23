<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Filament\Resources\PriceListResource\Pages;
use App\Models\PriceList;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use App\Support\Filament\Forms\Components\Flatpickr;

/**
 * PriceListResource
 *
 * Filament v4 resource for PriceList management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class PriceListResource extends Resource
{
    use HasNav;

    protected static ?string $model = PriceList::class;

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('price_lists.title');
    }

    

    public static function getPluralModelLabel(): string
    {
        return __('price_lists.plural');
    }

    public static function getModelLabel(): string
    {
        return __('price_lists.single');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('price_lists.basic_information'))
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label(__('price_lists.name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label(__('price_lists.code'))
                        ->required()
                        ->maxLength(64)
                        ->unique(ignoreRecord: true),
                    Select::make('currency_id')
                        ->label(__('price_lists.currency'))
                        ->relationship('currency', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Textarea::make('description')
                        ->label(__('price_lists.description'))
                        ->columnSpanFull()
                        ->rows(3),
                    TextInput::make('priority')
                        ->label(__('price_lists.priority'))
                        ->numeric()
                        ->default(0),
                ])
                ->columns(2),
            Section::make(__('price_lists.settings'))
                ->schema([
                    Toggle::make('is_enabled')
                        ->label(__('price_lists.is_active'))
                        ->default(true),
                    Toggle::make('is_default')
                        ->label(__('price_lists.is_default')),
                    Toggle::make('auto_apply')
                        ->label(__('price_lists.auto_apply')),
                    Flatpickr::make('starts_at')->asDateTime()
                        ->label(__('price_lists.starts_at')),
                    Flatpickr::make('ends_at')->asDateTime()
                        ->label(__('price_lists.ends_at')),
                    TextInput::make('min_order_amount')
                        ->label(__('price_lists.min_order_amount'))
                        ->numeric()
                        ->prefix('€'),
                    TextInput::make('max_order_amount')
                        ->label(__('price_lists.max_order_amount'))
                        ->numeric()
                        ->prefix('€'),
                ])
                ->columns(2),
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
                TextColumn::make('code')
                    ->label(__('price_lists.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('price_lists.currency'))
                    ->sortable()
                    ->badge(),
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
                IconColumn::make('is_enabled')
                    ->label(__('price_lists.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_default')
                    ->label(__('price_lists.is_default'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('auto_apply')
                    ->label(__('price_lists.auto_apply'))
                    ->boolean()
                    ->trueColor('primary')
                    ->falseColor('gray'),
                TextColumn::make('priority')
                    ->label(__('price_lists.priority'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('starts_at')
                    ->label(__('price_lists.valid_from'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label(__('price_lists.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('min_order_amount')
                    ->label(__('price_lists.min_order_amount'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_order_amount')
                    ->label(__('price_lists.max_order_amount'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label(__('price_lists.description'))
                    ->limit(50)
                    ->tooltip(fn (TextColumn $column): ?string => strlen($column->getState() ?? '') > 50 ? $column->getState() : null)
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
                SelectFilter::make('currency_id')
                    ->label(__('price_lists.currency'))
                    ->relationship('currency', 'name'),
                TernaryFilter::make('is_enabled')
                    ->label(__('price_lists.is_active'))
                    ->placeholder(__('price_lists.all_records'))
                    ->trueLabel(__('price_lists.active_only'))
                    ->falseLabel(__('price_lists.inactive_only')),
                TernaryFilter::make('is_default')
                    ->label(__('price_lists.is_default'))
                    ->placeholder(__('price_lists.all_records'))
                    ->trueLabel(__('price_lists.default_only'))
                    ->falseLabel(__('price_lists.non_default_only')),
                Filter::make('starts_at')
                    ->label(__('price_lists.valid_from'))
                    ->form([
                        Flatpickr::make('starts_from')->asDateTime()
                            ->label(__('price_lists.starts_at_from')),
                        Flatpickr::make('starts_until')->asDateTime()
                            ->label(__('price_lists.starts_at_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['starts_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('starts_at', '>=', $date),
                            )
                            ->when(
                                $data['starts_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('starts_at', '<=', $date),
                            );
                    }),
                Filter::make('ends_at')
                    ->label(__('price_lists.ends_at'))
                    ->form([
                        Flatpickr::make('ends_from')->asDateTime()
                            ->label(__('price_lists.ends_at_from')),
                        Flatpickr::make('ends_until')->asDateTime()
                            ->label(__('price_lists.ends_at_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['ends_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('ends_at', '>=', $date),
                            )
                            ->when(
                                $data['ends_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('ends_at', '<=', $date),
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
            'index'  => Pages\ListPriceLists::route('/'),
            'create' => Pages\CreatePriceList::route('/create'),
            'edit'   => Pages\EditPriceList::route('/{record}/edit'),
        ];
    }
}
