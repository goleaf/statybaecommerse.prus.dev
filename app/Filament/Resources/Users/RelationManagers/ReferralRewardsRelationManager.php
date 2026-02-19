<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\ReferralReward;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReferralRewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'referralRewards';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('referral_id')
                    ->relationship(
                        name: 'referral',
                        titleAttribute: 'referral_code',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->orderBy('referral_code'),
                    )
                    ->searchable()
                    ->preload(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->numeric()
                    ->prefix('€')
                    ->minValue(0)
                    ->required(),
                Select::make('status')
                    ->options($this->statusOptions())
                    ->default('pending')
                    ->required(),
                Select::make('type')
                    ->options($this->typeOptions())
                    ->default('referrer_bonus')
                    ->required(),
                DateTimePicker::make('expires_at'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('type')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
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
                    ->mutateDataUsing(fn (array $data): array => $this->normalizeRewardData($data))
                    ->using(function (array $data): ReferralReward {
                        $payload = $this->normalizeRewardData($data);
                        $payload['user_id'] = $this->getOwnerRecord()->getKey();

                        return ReferralReward::withoutGlobalScopes()->create($payload);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->normalizeRewardData($data)),
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
    private function normalizeRewardData(array $data): array
    {
        $title = $this->resolveLocalizedText($data['title'] ?? '');
        $description = $this->resolveLocalizedText($data['description'] ?? '');

        $status = (string) ($data['status'] ?? 'pending');
        if (! in_array($status, array_keys($this->statusOptions()), true)) {
            $status = 'pending';
        }

        $type = (string) ($data['type'] ?? 'referrer_bonus');
        if (! in_array($type, array_keys($this->typeOptions()), true)) {
            $type = 'referrer_bonus';
        }

        $data['title'] = [
            'lt' => $title !== '' ? $title : 'Referral reward',
            'en' => $title !== '' ? $title : 'Referral reward',
        ];
        $data['description'] = $description !== '' ? [
            'lt' => $description,
            'en' => $description,
        ] : null;
        $data['currency_code'] = strtoupper((string) ($data['currency_code'] ?? 'EUR'));
        $data['status'] = $status;
        $data['type'] = $type;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return $data;
    }

    private function resolveLocalizedText(mixed $value): string
    {
        if (is_array($value)) {
            foreach (['lt', 'en'] as $locale) {
                $candidate = $value[$locale] ?? null;

                if (is_scalar($candidate)) {
                    $candidate = trim((string) $candidate);

                    if ($candidate !== '') {
                        return $candidate;
                    }
                }
            }

            foreach ($value as $candidate) {
                if (is_scalar($candidate)) {
                    $candidate = trim((string) $candidate);

                    if ($candidate !== '') {
                        return $candidate;
                    }
                }
            }

            return '';
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return '';
    }

    /**
     * @return array<string, string>
     */
    private function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'applied' => 'Applied',
            'expired' => 'Expired',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function typeOptions(): array
    {
        return [
            'referrer_bonus'    => 'Referrer Bonus',
            'referred_discount' => 'Referred Discount',
        ];
    }
}
