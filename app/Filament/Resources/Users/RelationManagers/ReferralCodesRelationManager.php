<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\ReferralCodes\ReferralCodeResource;
use App\Models\ReferralCode;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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
                    ->label(__('admin.labels.used'))
                    ->sortable(),
                TextColumn::make('usage_limit')
                    ->label(__('admin.labels.limit'))
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
                Action::make('create')
                    ->icon('heroicon-m-plus')
                    ->url(fn (): string => ReferralCodeResource::getUrl('create', [
                        'user_id'  => $this->getOwnerRecord()->getKey(),
                        'redirect' => request()->fullUrl(),
                    ])),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (ReferralCode $record): string => ReferralCodeResource::getUrl('view', [
                        'record'   => $record,
                        'redirect' => request()->fullUrl(),
                    ])),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (ReferralCode $record): string => ReferralCodeResource::getUrl('edit', [
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

