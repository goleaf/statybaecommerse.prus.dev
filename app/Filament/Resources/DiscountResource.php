<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

final class DiscountResource extends BaseResource
{
    protected static ?string $model = Discount::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return __('admin.discounts.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.discounts.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.discounts.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('admin.discounts.basic_information'))
                ->description(__('admin.discounts.basic_information_description'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('messages.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('messages.code'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(50),
                        ]),
                    RichEditor::make('description')
                        ->label(__('messages.description'))
                        ->columnSpanFull(),
                    SchemaGrid::make(3)
                        ->schema([
                            Select::make('type')
                                ->label(__('messages.type'))
                                ->options([
                                    'percentage' => __('admin.discounts.percentage'),
                                    'fixed'      => __('admin.discounts.fixed_amount'),
                                ])
                                ->required(),
                            TextInput::make('value')
                                ->label(__('messages.value'))
                                ->required()
                                ->numeric()
                                ->minValue(0),
                            Toggle::make('is_active')
                                ->label(__('admin.discounts.is_active'))
                                ->default(true),
                        ]),
                ]),
            SchemaSection::make(__('admin.discounts.validity'))
                ->description(__('admin.discounts.validity_description'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            DateTimePicker::make('valid_from')
                                ->label(__('admin.discounts.valid_from'))
                                ->default(now()),
                            DateTimePicker::make('valid_until')
                                ->label(__('admin.discounts.valid_until')),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('usage_limit')
                                ->label(__('admin.discounts.usage_limit'))
                                ->numeric()
                                ->minValue(1),
                            TextInput::make('minimum_amount')
                                ->label(__('admin.discounts.minimum_amount'))
                                ->numeric()
                                ->minValue(0)
                                ->step(0.01),
                        ]),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('messages.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'percentage' => __('admin.discounts.percentage'),
                        'fixed'      => __('admin.discounts.fixed_amount'),
                        default      => $state,
                    })
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('messages.value'))
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage'
                            ? $state . '%'
                            : '€' . number_format($state, 2)
                    )
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.discounts.is_active')),
                TextColumn::make('valid_from')
                    ->label(__('admin.discounts.valid_from'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label(__('admin.discounts.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('admin.discounts.no_expiry')),
                TextColumn::make('created_at')
                    ->label(__('admin.discounts.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'view'   => Pages\ViewDiscount::route('/{record}'),
            'edit'   => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
