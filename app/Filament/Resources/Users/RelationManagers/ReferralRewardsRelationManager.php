<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

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

class ReferralRewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'referralRewards';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('referral_id')
                    ->relationship('referral', 'referral_code')
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
                    ->required(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'applied' => 'Applied',
                        'expired' => 'Expired',
                    ])
                    ->required(),
                Select::make('type')
                    ->options([
                        'referrer_bonus'    => 'Referrer Bonus',
                        'referred_discount' => 'Referred Discount',
                    ])
                    ->default('referrer_bonus')
                    ->required(),
                DateTimePicker::make('expires_at'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
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
                    ->mutateDataUsing(static function (array $data): array {
                        $title = trim((string) ($data['title'] ?? ''));
                        $description = trim((string) ($data['description'] ?? ''));

                        $data['title'] = [
                            'lt' => $title !== '' ? $title : 'Referral reward',
                            'en' => $title !== '' ? $title : 'Referral reward',
                        ];
                        $data['description'] = $description !== '' ? [
                            'lt' => $description,
                            'en' => $description,
                        ] : null;
                        $data['currency_code'] = (string) ($data['currency_code'] ?? 'EUR');
                        $data['status'] = (string) ($data['status'] ?? 'pending');
                        $data['is_active'] = (bool) ($data['is_active'] ?? true);

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
