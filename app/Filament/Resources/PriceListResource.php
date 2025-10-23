<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\PriceListResource\Pages;
use App\Models\PriceList;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
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
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Select::make('currency_id')
                        ->label(__('price_lists.currency'))
                        ->relationship('currency', 'code')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('priority')
                        ->label(__('price_lists.priority'))
                        ->numeric()
                        ->default(0),
                    Textarea::make('description')
                        ->label(__('price_lists.description'))
                        ->columnSpanFull()
                        ->rows(3),
                ]),
            Section::make(__('price_lists.availability'))
                ->columns(2)
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
                        ->visible(fn (Forms\Get $get): bool => $get('pricing_type') === 'tiered'),
                ])
                ->visible(fn (Forms\Get $get): bool => $get('pricing_type') === 'tiered'),
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
                        ->visible(fn (Forms\Get $get): bool => $get('pricing_type') === 'volume'),
                ])
                ->visible(fn (Forms\Get $get): bool => $get('pricing_type') === 'volume'),
            Section::make(__('price_lists.settings'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('price_lists.is_active'))
                        ->default(true),
                    Toggle::make('is_default')
                        ->label(__('price_lists.is_default')),
                    Toggle::make('auto_apply')
                        ->label(__('price_lists.auto_apply')),
                    Flatpickr::makeDateTime('starts_at')
                        ->label(__('price_lists.starts_at')),
                    Flatpickr::makeDateTime('ends_at')
                        ->label(__('price_lists.ends_at')),
                    TextInput::make('min_order_amount')
                        ->label(__('price_lists.min_order_amount'))
                        ->numeric()
                        ->inputMode('decimal'),
                    TextInput::make('max_order_amount')
                        ->label(__('price_lists.max_order_amount'))
                        ->numeric()
                        ->inputMode('decimal'),
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
                    ->color(fn (string $state): string => match ($state) {
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
                IconColumn::make('is_enabled')
                    ->label(__('price_lists.is_enabled'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_default')
                    ->label(__('price_lists.is_default'))
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('auto_apply')
                    ->label(__('price_lists.auto_apply'))
                    ->boolean()
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('priority')
                    ->label(__('price_lists.priority'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('starts_at')
                    ->label(__('price_lists.starts_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label(__('price_lists.ends_at'))
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
                    ->relationship('currency', 'code'),
                TernaryFilter::make('is_enabled')
                    ->label(__('price_lists.is_enabled'))
                    ->placeholder(__('price_lists.all_records'))
                    ->trueLabel(__('price_lists.enabled_only'))
                    ->falseLabel(__('price_lists.disabled_only')),
                TernaryFilter::make('is_default')
                    ->label(__('price_lists.is_default'))
                    ->placeholder(__('price_lists.all_records'))
                    ->trueLabel(__('price_lists.default_only'))
                    ->falseLabel(__('price_lists.non_default_only')),
                TernaryFilter::make('auto_apply')
                    ->label(__('price_lists.auto_apply'))
                    ->placeholder(__('price_lists.all_records'))
                    ->trueLabel(__('price_lists.auto_apply_only'))
                    ->falseLabel(__('price_lists.manual_only')),
                Filter::make('starts_at')
                    ->label(__('price_lists.starts_at'))
                    ->form([
                        Flatpickr::makeDateTime('starts_from')
                            ->label(__('price_lists.starts_at_from')),
                        Flatpickr::makeDateTime('starts_until')
                            ->label(__('price_lists.starts_at_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['valid_from_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('valid_from', '>=', $date),
                            )
                            ->when(
                                $data['valid_from_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('valid_from', '<=', $date),
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
