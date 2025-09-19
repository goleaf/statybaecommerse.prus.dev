<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\ReferralCampaignResource\Pages;
use App\Models\ReferralCampaign;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use UnitEnum;
use BackedEnum;
final class ReferralCampaignResource extends Resource
{
    protected static ?string $model = ReferralCampaign::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Marketing;
    protected static ?int $navigationSort = 4;
    public static function form(Schema $schema): Schema
    {
        return $schema
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
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('End Date')
                            ->after('start_date'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Reward Settings')
                        Forms\Components\TextInput::make('reward_amount')
                            ->label('Reward Amount')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01),
                        Forms\Components\Select::make('reward_type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed Amount',
                                'points' => 'Points',
                            ])
                            ->default('fixed'),
                Forms\Components\Section::make('Limits')
                        Forms\Components\TextInput::make('max_referrals_per_user')
                            ->label('Max Referrals Per User')
                            ->minValue(0),
                        Forms\Components\TextInput::make('max_total_referrals')
                            ->label('Max Total Referrals')
                Forms\Components\Section::make('Advanced Settings')
                        Forms\Components\KeyValue::make('conditions')
                            ->keyLabel('Condition')
                            ->valueLabel('Value')
                        Forms\Components\KeyValue::make('metadata')
                            ->keyLabel('Key')
            ]);
    }
    public static function table(Table $table): Table
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reward_amount')
                    ->label('Reward')
                    ->formatStateUsing(fn($state, $record) =>
                        $record->reward_type === 'percentage' ? $state . '%' : '€' . number_format($state, 2))
                Tables\Columns\BadgeColumn::make('reward_type')
                    ->colors([
                        'primary' => 'percentage',
                        'success' => 'fixed',
                        'info' => 'points',
                Tables\Columns\TextColumn::make('max_referrals_per_user')
                    ->label('Per User Limit')
                    ->sortable()
                Tables\Columns\TextColumn::make('max_total_referrals')
                    ->label('Total Limit')
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->dateTime()
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                Tables\Columns\TextColumn::make('created_at')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('reward_type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                        'points' => 'Points',
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('start_from')
                            ->label('Start From'),
                        Forms\Components\DatePicker::make('start_until')
                            ->label('Start Until'),
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn($query, $date) => $query->whereDate('start_date', '>=', $date),
                            )
                                $data['start_until'],
                                fn($query, $date) => $query->whereDate('start_date', '<=', $date),
                            );
                    }),
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ->defaultSort('start_date', 'desc');
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => Pages\ListReferralCampaigns::route('/'),
            'create' => Pages\CreateReferralCampaign::route('/create'),
            'edit' => Pages\EditReferralCampaign::route('/{record}/edit'),
}
