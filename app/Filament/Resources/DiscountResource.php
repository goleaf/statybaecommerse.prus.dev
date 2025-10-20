<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\Discount;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use UnitEnum;

final class DiscountResource extends Resource
{
    private const TYPE_OPTIONS = [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed Amount',
        'free_shipping' => 'Free Shipping',
        'bogo' => 'Buy One Get One',
    ];

    private const STATUS_OPTIONS = [
        'draft' => 'Draft',
        'active' => 'Active',
        'scheduled' => 'Scheduled',
        'expired' => 'Expired',
        'archived' => 'Archived',
    ];

    protected static ?string $model = Discount::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, Get $get): void {
                                if (filled($get('slug'))) {
                                    return;
                                }

                                if (blank($state)) {
                                    $set('slug', null);

                                    return;
                                }

                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->readOnly()
                            ->dehydrated()
                            ->maxLength(255)
                            ->unique(table: (new Discount())->getTable(), column: 'slug', ignoreRecord: true)
                            ->dehydrateStateUsing(static function (?string $state, Get $get): ?string {
                                $slug = filled($state) ? $state : Str::slug((string) $get('name'));

                                return $slug !== '' ? Str::slug($slug) : null;
                            }),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->nullable()
                            ->maxLength(65535),
                        Select::make('type')
                            ->label(__('Type'))
                            ->options(self::TYPE_OPTIONS)
                            ->required()
                            ->rules([Rule::in(array_keys(self::TYPE_OPTIONS))]),
                        TextInput::make('value')
                            ->label(__('Value'))
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->rules(['numeric', 'min:0']),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(self::STATUS_OPTIONS)
                            ->required()
                            ->default('draft')
                            ->rules([Rule::in(array_keys(self::STATUS_OPTIONS))]),
                        Toggle::make('is_active')
                            ->label(__('Is Active'))
                            ->default(true)
                            ->required(),
                        Toggle::make('is_enabled')
                            ->label(__('Is Enabled'))
                            ->default(true)
                            ->required(),
                        DateTimePicker::make('starts_at')
                            ->label(__('Starts At'))
                            ->required()
                            ->seconds(false)
                            ->default(fn () => now())
                            ->rules(['required', 'date']),
                        DateTimePicker::make('ends_at')
                            ->label(__('Ends At'))
                            ->seconds(false)
                            ->rules(['nullable', 'date', 'after:starts_at']),
                        TextInput::make('usage_limit')
                            ->label(__('Usage Limit'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->rules(['nullable', 'integer', 'min:0']),
                        TextInput::make('usage_count')
                            ->label(__('Usage Count'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->rules(['nullable', 'integer', 'min:0']),
                        TextInput::make('minimum_amount')
                            ->label(__('Minimum Amount'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->rules(['nullable', 'numeric', 'min:0'])
                            ->prefix('€'),
                        TextInput::make('maximum_amount')
                            ->label(__('Maximum Amount'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->rules(['nullable', 'numeric', 'min:0'])
                            ->prefix('€'),
                        TextInput::make('priority')
                            ->label(__('Priority'))
                            ->numeric()
                            ->default(0)
                            ->rules(['nullable', 'integer']),
                        Toggle::make('exclusive')
                            ->label(__('Exclusive'))
                            ->default(false),
                        Toggle::make('applies_to_shipping')
                            ->label(__('Applies to Shipping'))
                            ->default(false),
                        Toggle::make('free_shipping')
                            ->label(__('Free Shipping'))
                            ->default(false),
                        Toggle::make('first_order_only')
                            ->label(__('First Order Only'))
                            ->default(false),
                        TextInput::make('per_customer_limit')
                            ->label(__('Per Customer Limit'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->rules(['nullable', 'integer', 'min:0']),
                        TextInput::make('per_code_limit')
                            ->label(__('Per Code Limit'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->rules(['nullable', 'integer', 'min:0']),
                        TextInput::make('per_day_limit')
                            ->label(__('Per Day Limit'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->rules(['nullable', 'integer', 'min:0']),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Discounts';
    }

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('discounts.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('discounts.plural');
    }

    public static function getModelLabel(): string
    {
        return __('discounts.single');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'view' => Pages\ViewDiscount::route('/{record}'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
}
