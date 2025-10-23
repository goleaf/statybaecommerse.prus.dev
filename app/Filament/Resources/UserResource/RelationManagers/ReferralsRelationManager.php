<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;

final class ReferralsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'referrals';

    protected static ?string $title = 'admin.sections.referrals';

    public function form(Form $form): Form|array
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('referred_id')
                    ->relationship('referred', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('reward_amount')
                    ->numeric()
                    ->prefix('€')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table|array
    {
        return $table
            ->recordTitleAttribute('referred.name')
            ->columns([
                Tables\Columns\TextColumn::make('referred.name')
                    ->label(__('admin.fields.referred_user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('referred.email')
                    ->label(__('admin.fields.referred_email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('reward_amount')
                    ->label(__('admin.fields.reward_amount'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'   => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
