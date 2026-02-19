<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\ReferralCode;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReferralCodesRelationManager extends RelationManager
{
    protected static string $relationship = 'referralCodes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->default(static fn (): string => ReferralCode::generateUniqueCode())
                    ->maxLength(20),
                TextInput::make('reward_amount')
                    ->numeric()
                    ->prefix('€'),
                TextInput::make('usage_limit')
                    ->numeric(),
                DateTimePicker::make('expires_at'),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reward_amount')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('usage_count')
                    ->label('Used')
                    ->sortable(),
                TextColumn::make('usage_limit')
                    ->label('Limit')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->sortable()
                    ->boolean(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->normalizePayload($data))
                    ->using(function (array $data): ReferralCode {
                        $payload = $this->normalizePayload($data);
                        $payload['user_id'] = $this->getOwnerRecord()->getKey();

                        return ReferralCode::withoutGlobalScopes()->create($payload);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->normalizePayload($data)),
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
    private function normalizePayload(array $data): array
    {
        $code = strtoupper(trim((string) ($data['code'] ?? '')));
        $resolvedCode = $code !== '' ? $code : ReferralCode::generateUniqueCode();

        $data['code'] = substr($resolvedCode, 0, 20);
        $data['reward_amount'] = is_numeric($data['reward_amount'] ?? null)
            ? round((float) $data['reward_amount'], 2)
            : null;
        $data['usage_limit'] = is_numeric($data['usage_limit'] ?? null)
            ? max(0, (int) $data['usage_limit'])
            : null;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }
}
