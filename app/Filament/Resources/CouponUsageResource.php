<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CouponUsageResource\Pages\CreateCouponUsage;
use App\Filament\Resources\CouponUsageResource\Pages\EditCouponUsage;
use App\Filament\Resources\CouponUsageResource\Pages\ListCouponUsages;
use App\Filament\Resources\CouponUsageResource\Pages\ViewCouponUsage;
use App\Models\CouponUsage;
use App\Models\Scopes\UserOwnedScope;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CouponUsageResource extends Resource
{
    protected static ?string $model = CouponUsage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('coupon_id')
                    ->relationship(
                        name: 'coupon',
                        titleAttribute: 'code',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->orderBy('code'),
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
                    ->default(static fn (): ?int => request()->integer('user_id') ?: null)
                    ->required(),
                Select::make('order_id')
                    ->relationship(
                        name: 'order',
                        titleAttribute: 'number',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->when(
                                request()->integer('user_id') > 0,
                                static fn (Builder $ordersQuery): Builder => $ordersQuery
                                    ->where('user_id', request()->integer('user_id')),
                            )
                            ->latest('id'),
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),
                TextInput::make('discount_amount')
                    ->numeric()
                    ->prefix('€')
                    ->required(),
                DateTimePicker::make('used_at')
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('coupon.code')
                    ->label(__('admin.labels.coupon'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('admin.labels.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.number')
                    ->label(__('admin.labels.order'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('used_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCouponUsages::route('/'),
            'create' => CreateCouponUsage::route('/create'),
            'view'   => ViewCouponUsage::route('/{record}'),
            'edit'   => EditCouponUsage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                UserOwnedScope::class,
            ]);
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalizePayload(array $data): array
    {
        $data['coupon_id'] = (int) ($data['coupon_id'] ?? 0);
        $data['user_id'] = is_numeric($data['user_id'] ?? null) ? (int) $data['user_id'] : null;
        $data['order_id'] = is_numeric($data['order_id'] ?? null) ? (int) $data['order_id'] : null;
        $data['discount_amount'] = is_numeric($data['discount_amount'] ?? null)
            ? round((float) $data['discount_amount'], 2)
            : 0.0;
        $data['used_at'] = $data['used_at'] ?? now();

        return $data;
    }
}
