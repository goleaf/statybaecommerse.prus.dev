<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use App\Models\Price;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    protected static ?string $recordTitleAttribute = 'amount';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('currency_id')
                    ->default(static fn (): ?int => self::resolveEuroCurrencyId()),
                TextInput::make('amount')
                    ->label(__('messages.amount') !== 'messages.amount' ? __('messages.amount') : 'Amount')
                    ->required()
                    ->numeric()
                    ->step(0.0001),
                Select::make('type')
                    ->label(__('messages.type'))
                    ->options(array_combine(Price::ALLOWED_TYPES, Price::ALLOWED_TYPES))
                    ->default('retail')
                    ->required(),
                DateTimePicker::make('starts_at')
                    ->label(__('admin.prices.valid_from')),
                DateTimePicker::make('ends_at')
                    ->label(__('admin.prices.valid_until')),
                Toggle::make('is_enabled')
                    ->label(__('messages.enabled'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('currency.code')
                    ->label(__('messages.currency'))
                    ->badge(),
                TextColumn::make('amount')
                    ->sortable()
                    ->label(__('messages.amount') !== 'messages.amount' ? __('messages.amount') : 'Amount')
                    ->money('EUR'),
                TextColumn::make('type')
                    ->sortable()
                    ->label(__('messages.type'))
                    ->badge(),
                IconColumn::make('is_enabled')
                    ->sortable()
                    ->label(__('messages.enabled'))
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->sortable()
                    ->label(__('admin.prices.valid_from'))
                    ->dateTime(),
                TextColumn::make('ends_at')
                    ->sortable()
                    ->label(__('admin.prices.valid_until'))
                    ->dateTime()
                    ->placeholder(__('admin.prices.no_expiry')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->normalizePayload($data))
                    ->using(fn (array $data): Price => $this->getOwnerRecord()->prices()->create($data)),
            ])
            ->actions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->normalizePayload($data)),
                DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizePayload(array $data): array
    {
        $euroCurrencyId = self::resolveEuroCurrencyId();
        if ($euroCurrencyId !== null) {
            $data['currency_id'] = $euroCurrencyId;
        }

        $type = is_string($data['type'] ?? null) ? trim((string) $data['type']) : '';

        if (! in_array($type, Price::ALLOWED_TYPES, true)) {
            $data['type'] = 'retail';
        }

        return $data;
    }

    private static function resolveEuroCurrencyId(): ?int
    {
        $currencyId = \App\Models\Currency::query()
            ->where('code', 'EUR')
            ->value('id');

        if (! is_numeric($currencyId)) {
            $currencyId = \App\Models\Currency::query()
                ->where('is_default', true)
                ->value('id');
        }

        return is_numeric($currencyId) ? (int) $currencyId : null;
    }
}
