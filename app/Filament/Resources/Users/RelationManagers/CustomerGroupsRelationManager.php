<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\CustomerGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CustomerGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'customerGroups';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('messages.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label(__('messages.code'))
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                Select::make('type')
                    ->label(__('messages.type'))
                    ->options([
                        'retail'    => __('messages.retail'),
                        'wholesale' => __('messages.wholesale'),
                        'b2b'       => __('messages.b2b'),
                        'internal'  => __('messages.internal'),
                    ])
                    ->default('retail')
                    ->required(),
                TextInput::make('color')
                    ->label(__('messages.color'))
                    ->maxLength(32),
                TextInput::make('icon')
                    ->label(__('admin.news_images.image'))
                    ->maxLength(64),
                Textarea::make('description')
                    ->label(__('messages.description'))
                    ->columnSpanFull(),
                TextInput::make('discount_percentage')
                    ->label(__('admin.products.price_increase_percentage'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0)
                    ->dehydrateStateUsing(static fn ($state): float => self::normalizeNumericAmount($state))
                    ->suffix('%'),
                TextInput::make('discount_fixed')
                    ->label(__('messages.discount_amount'))
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->dehydrateStateUsing(static fn ($state): float => self::normalizeNumericAmount($state)),
                Toggle::make('has_special_pricing')
                    ->label(__('ui.has_special_pricing'))
                    ->default(false),
                Toggle::make('has_volume_discounts')
                    ->label(__('ui.has_volume_discounts'))
                    ->default(false),
                Toggle::make('can_view_prices')
                    ->label(__('ui.can_view_prices'))
                    ->default(true),
                Toggle::make('can_place_orders')
                    ->label(__('ui.can_place_orders'))
                    ->default(true),
                Toggle::make('can_view_catalog')
                    ->label(__('ui.can_view_catalog'))
                    ->default(true),
                Toggle::make('can_use_coupons')
                    ->label(__('ui.can_use_coupons'))
                    ->default(true),
                TextInput::make('minimum_order_amount')
                    ->label(__('ui.minimum_order_amount'))
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->dehydrateStateUsing(static fn ($state): float => self::normalizeNumericAmount($state)),
                TextInput::make('credit_limit')
                    ->label(__('ui.credit_limit'))
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->dehydrateStateUsing(static fn ($state): float => self::normalizeNumericAmount($state)),
                TextInput::make('payment_terms')
                    ->label(__('messages.payment_terms'))
                    ->maxLength(255)
                    ->default('net_30')
                    ->required()
                    ->dehydrateStateUsing(static fn ($state): string => self::normalizePaymentTerms($state)),
                TextInput::make('sort_order')
                    ->label(__('messages.sort'))
                    ->numeric()
                    ->default(0)
                    ->dehydrateStateUsing(static fn ($state): int => is_numeric($state) ? (int) $state : 0),
                KeyValue::make('metadata')
                    ->label(__('admin.labels.metadata'))
                    ->nullable()
                    ->columnSpanFull(),
                KeyValue::make('conditions')
                    ->label(__('admin.labels.conditions'))
                    ->nullable()
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label(__('messages.active'))
                    ->default(true),
                Toggle::make('is_enabled')
                    ->label(__('messages.enabled'))
                    ->default(true),
                Toggle::make('is_default')
                    ->label(__('messages.default'))
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount_percentage')
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->sortable()
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(static fn (array $data): array => self::normalizePayload($data)),
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'code'])
                    ->recordSelectOptionsQuery(
                        static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->orderBy('name'),
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(static fn (array $data): array => self::normalizePayload($data)),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }

    public function getTableRecordTitle(Model $record): string
    {
        if ($record instanceof CustomerGroup) {
            $name = $record->getAttribute('name');

            if (is_array($name)) {
                $locale = (string) app()->getLocale();
                $fallbackLocale = (string) config('app.fallback_locale', 'en');

                $name = $name[$locale]
                    ?? $name[$fallbackLocale]
                    ?? reset($name)
                    ?? null;
            }

            if (is_string($name) && trim($name) !== '') {
                return $name;
            }
        }

        return (string) $record->getKey();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function normalizePayload(array $data): array
    {
        foreach (['discount_percentage', 'discount_fixed', 'minimum_order_amount', 'credit_limit'] as $field) {
            $data[$field] = self::normalizeNumericAmount($data[$field] ?? null);
        }

        $data['payment_terms'] = self::normalizePaymentTerms($data['payment_terms'] ?? null);
        $data['sort_order'] = is_numeric($data['sort_order'] ?? null) ? (int) $data['sort_order'] : 0;

        return $data;
    }

    private static function normalizeNumericAmount(mixed $value, float $default = 0.0): float
    {
        if (! is_numeric($value)) {
            return $default;
        }

        return round((float) $value, 2);
    }

    private static function normalizePaymentTerms(mixed $value, string $default = 'net_30'): string
    {
        if (! is_scalar($value)) {
            return $default;
        }

        $normalized = strtolower(trim((string) $value));

        return $normalized !== '' ? str_replace([' ', '-'], '_', $normalized) : $default;
    }
}
