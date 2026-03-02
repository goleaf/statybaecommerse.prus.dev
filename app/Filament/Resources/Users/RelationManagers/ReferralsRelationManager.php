<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Referrals\ReferralResource;
use App\Models\Referral;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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
                            ->whereDoesntHave('referredBy')
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
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->recordTitleAttribute('referral_code')
            ->columns([
                TextColumn::make('referred.name')
                    ->label(__('admin.labels.referred_user'))
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
                Action::make('create')
                    ->icon('heroicon-m-plus')
                    ->url(fn (): string => ReferralResource::getUrl('create', [
                        'user_id'  => $this->getOwnerRecord()->getKey(),
                        'redirect' => request()->fullUrl(),
                    ])),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Referral $record): string => ReferralResource::getUrl('view', [
                        'record'   => $record,
                        'redirect' => request()->fullUrl(),
                    ])),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Referral $record): string => ReferralResource::getUrl('edit', [
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
}

