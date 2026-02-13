<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\Referral;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'referrals';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('referred_id')
                    ->relationship(
                        name: 'referred',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->whereKeyNot($this->getOwnerRecord()->getKey())
                            ->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('referral_code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->default(static fn (): string => Referral::generateUniqueCode())
                    ->maxLength(255),
                Select::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'completed' => 'Completed',
                        'expired'   => 'Expired',
                    ])
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('completed_at'),
                DateTimePicker::make('expires_at'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('referral_code')
            ->columns([
                TextColumn::make('referred.name')
                    ->label('Referred User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referral_code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('completed_at')
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
                        $resolvedCode = trim((string) ($data['referral_code'] ?? ''));

                        $data['referral_code'] = $resolvedCode !== '' ? $resolvedCode : Referral::generateUniqueCode();
                        $data['status'] = (string) ($data['status'] ?? 'pending');

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
