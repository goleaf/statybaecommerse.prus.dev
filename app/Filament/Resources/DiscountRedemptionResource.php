<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountRedemptionResource\Pages\CreateDiscountRedemption;
use App\Filament\Resources\DiscountRedemptionResource\Pages\EditDiscountRedemption;
use App\Filament\Resources\DiscountRedemptionResource\Pages\ListDiscountRedemptions;
use App\Filament\Resources\DiscountRedemptionResource\Pages\ViewDiscountRedemption;
use App\Models\DiscountRedemption;
use App\Models\Scopes\StatusScope;
use App\Models\Scopes\UserOwnedScope;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DiscountRedemptionResource extends Resource
{
    protected static ?string $model = DiscountRedemption::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            UserOwnedScope::class,
            StatusScope::class,
        ]);
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('messages.discounts');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('discount_id')
                    ->relationship(
                        name: 'discount',
                        titleAttribute: 'name',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->orderByDesc('id'),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('code_id')
                    ->relationship(
                        name: 'code',
                        titleAttribute: 'code',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->orderByDesc('id'),
                    )
                    ->searchable()
                    ->preload(),
                Select::make('order_id')
                    ->relationship(
                        name: 'order',
                        titleAttribute: 'number',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->orderByDesc('id'),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->default(static fn (): ?int => request()->integer('user_id') ?: Auth::id()),
                TextInput::make('amount_saved')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->dehydrateStateUsing(static fn ($state): float => self::normalizeAmount($state)),
                TextInput::make('currency_code')
                    ->maxLength(3)
                    ->default('EUR')
                    ->required()
                    ->dehydrateStateUsing(static fn ($state): string => self::normalizeCurrency($state)),
                DateTimePicker::make('redeemed_at')
                    ->default(static fn () => now())
                    ->seconds(false)
                    ->required(),
                KeyValue::make('metadata')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('discount.name')
                    ->label(__('admin.labels.discount'))
                    ->toggleable(),
                TextColumn::make('code.code')
                    ->label(__('admin.labels.code'))
                    ->toggleable(),
                TextColumn::make('order.number')
                    ->label(__('admin.labels.order'))
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label(__('admin.labels.user'))
                    ->toggleable(),
                TextColumn::make('amount_saved')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('currency_code')
                    ->sortable(),
                TextColumn::make('redeemed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDiscountRedemptions::route('/'),
            'create' => CreateDiscountRedemption::route('/create'),
            'view'   => ViewDiscountRedemption::route('/{record}'),
            'edit'   => EditDiscountRedemption::route('/{record}/edit'),
        ];
    }

    public static function normalizePayload(array $data): array
    {
        $data['amount_saved'] = self::normalizeAmount($data['amount_saved'] ?? null);
        $data['currency_code'] = self::normalizeCurrency($data['currency_code'] ?? null);
        $data['redeemed_at'] = $data['redeemed_at'] ?? now();
        $data['user_id'] = $data['user_id'] ?? Auth::id();

        return $data;
    }

    private static function normalizeAmount(mixed $value): float
    {
        if (! is_numeric($value)) {
            return 0.0;
        }

        return round((float) $value, 2);
    }

    private static function normalizeCurrency(mixed $value): string
    {
        $resolved = strtoupper(trim((string) ($value ?? '')));

        if ($resolved === '') {
            return 'EUR';
        }

        return substr($resolved, 0, 3);
    }
}
