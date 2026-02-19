<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\OrderStatus;
use BackedEnum;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                TextInput::make('total')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
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
                CreateAction::make()
                    ->mutateDataUsing(static fn (array $data): array => self::normalizeOrderPayload($data)),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(static fn (array $data): array => self::normalizeOrderPayload($data)),
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

    private static function normalizeNumericAmount(mixed $value, float $default = 0.0): float
    {
        if (! is_numeric($value)) {
            return $default;
        }

        return round((float) $value, 2);
    }
}
