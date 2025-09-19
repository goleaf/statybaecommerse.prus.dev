<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralCampaignResource\Pages;
use App\Models\ReferralCampaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class ReferralCampaignResource extends Resource
{
    protected static ?string $model = ReferralCampaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Campaign Period')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),

                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('End Date')
                            ->after('start_date'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Reward Settings')
                    ->schema([
                        Forms\Components\TextInput::make('reward_amount')
                            ->label('Reward Amount')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01),

                        Forms\Components\Select::make('reward_type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed Amount',
                                'points' => 'Points',
                            ])
                            ->required()
                            ->default('fixed'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Limits')
                    ->schema([
                        Forms\Components\TextInput::make('max_referrals_per_user')
                            ->label('Max Referrals Per User')
                            ->numeric()
                            ->minValue(0),

                        Forms\Components\TextInput::make('max_total_referrals')
                            ->label('Max Total Referrals')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Advanced Settings')
                    ->schema([
                        Forms\Components\KeyValue::make('conditions')
                            ->keyLabel('Condition')
                            ->valueLabel('Value')
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('metadata')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('reward_amount')
                    ->label('Reward')
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->reward_type === 'percentage' ? $state . '%' : '€' . number_format($state, 2)
                    )
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('reward_type')
                    ->colors([
                        'primary' => 'percentage',
                        'success' => 'fixed',
                        'info' => 'points',
                    ]),

                Tables\Columns\TextColumn::make('max_referrals_per_user')
                    ->label('Per User Limit')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('max_total_referrals')
                    ->label('Total Limit')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('reward_type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                        'points' => 'Points',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('start_from')
                            ->label('Start From'),
                        Forms\Components\DatePicker::make('start_until')
                            ->label('Start Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn ($query, $date) => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['start_until'],
                                fn ($query, $date) => $query->whereDate('start_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralCampaigns::route('/'),
            'create' => Pages\CreateReferralCampaign::route('/create'),
            'edit' => Pages\EditReferralCampaign::route('/{record}/edit'),
        ];
    }
}
