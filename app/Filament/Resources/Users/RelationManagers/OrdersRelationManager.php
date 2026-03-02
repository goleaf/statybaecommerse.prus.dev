<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\OrderResource;
use App\Enums\OrderPaymentState;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->disabled(),
                Select::make('status')
                    ->options(OrderStatus::options())
                    ->default(OrderStatus::PENDING->value)
                    ->required(),
                Select::make('payment_status')
                    ->options(PaymentStatus::options())
                    ->default(PaymentStatus::PENDING->value)
                    ->required(),
                Hidden::make('payment_state')
                    ->default(OrderPaymentState::CREATED->value),
                Hidden::make('currency')
                    ->default('EUR'),
                Hidden::make('subtotal')
                    ->default(0),
                Hidden::make('tax_amount')
                    ->default(0),
                Hidden::make('shipping_amount')
                    ->default(0),
                Hidden::make('discount_amount')
                    ->default(0),
                TextInput::make('total')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->recordTitleAttribute('number')
            ->columns([
                TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('total')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('create')
                    ->icon('heroicon-m-plus')
                    ->url(fn (): string => OrderResource::getUrl('create', [
                        'user_id'  => $this->getOwnerRecord()->getKey(),
                        'redirect' => request()->fullUrl(),
                    ])),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record): string => OrderResource::getUrl('view', [
                        'record'   => $record,
                        'redirect' => request()->fullUrl(),
                    ])),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn ($record): string => OrderResource::getUrl('edit', [
                        'record'   => $record,
                        'redirect' => request()->fullUrl(),
                    ])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function normalizeOrderPayload(array $data): array
    {
        $data['status'] = self::normalizeOrderStatus($data['status'] ?? null);
        $data['payment_status'] = self::normalizePaymentStatus($data['payment_status'] ?? null);
        $data['payment_state'] = self::normalizePaymentState($data['payment_state'] ?? null);
        $data['currency'] = 'EUR';
        $data['subtotal'] = self::normalizeNumericAmount($data['subtotal'] ?? null);
        $data['tax_amount'] = self::normalizeNumericAmount($data['tax_amount'] ?? null);
        $data['shipping_amount'] = self::normalizeNumericAmount($data['shipping_amount'] ?? null);
        $data['discount_amount'] = self::normalizeNumericAmount($data['discount_amount'] ?? null);
        $data['total'] = self::normalizeNumericAmount($data['total'] ?? null);

        return $data;
    }

    private static function normalizeOrderStatus(mixed $value): string
    {
        if ($value instanceof OrderStatus) {
            return $value->value;
        }

        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (! is_scalar($value) && $value !== null) {
            return OrderStatus::PENDING->value;
        }

        $normalizedValue = strtolower(trim((string) ($value ?? '')));

        return OrderStatus::tryFrom($normalizedValue)?->value
            ?? OrderStatus::PENDING->value;
    }

    private static function normalizePaymentStatus(mixed $value): string
    {
        if ($value instanceof PaymentStatus) {
            return $value->value;
        }

        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (! is_scalar($value) && $value !== null) {
            return PaymentStatus::PENDING->value;
        }

        $normalizedValue = strtolower(trim((string) ($value ?? '')));

        return PaymentStatus::tryFrom($normalizedValue)?->value
            ?? PaymentStatus::PENDING->value;
    }

    private static function normalizePaymentState(mixed $value): string
    {
        if ($value instanceof OrderPaymentState) {
            return $value->value;
        }

        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if (! is_scalar($value) && $value !== null) {
            return OrderPaymentState::CREATED->value;
        }

        $normalizedValue = strtolower(trim((string) ($value ?? '')));

        return OrderPaymentState::tryFrom($normalizedValue)?->value
            ?? OrderPaymentState::CREATED->value;
    }

    private static function normalizeNumericAmount(mixed $value, float $default = 0.0): float
    {
        if (! is_numeric($value)) {
            return $default;
        }

        return round((float) $value, 2);
    }
}

