<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\ReferralCodeResource\Pages;
use App\Models\ReferralCode;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use UnitEnum;
use BackedEnum;
final class ReferralCodeResource extends Resource
{
    protected static ?string $model = ReferralCode::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-gift';
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Marketing;
    protected static ?int $navigationSort = 3;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('code')
                            ->maxLength(255)
                            ->unique(ReferralCode::class, 'code', ignoreRecord: true),
                        Forms\Components\TextInput::make('title')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
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
                            ->required(),
                        Forms\Components\TextInput::make('usage_limit')
                            ->label('Usage Limit')
                            ->minValue(0),
                        Forms\Components\TextInput::make('usage_count')
                            ->label('Usage Count')
                            ->disabled(),
                Forms\Components\Section::make('Validity & Campaign')
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At'),
                        Forms\Components\Select::make('campaign_id')
                            ->relationship('campaign', 'name')
                        Forms\Components\Select::make('source')
                                'admin' => 'Admin Created',
                                'user' => 'User Generated',
                                'api' => 'API Generated',
                                'import' => 'Imported',
                            ->default('admin'),
                Forms\Components\Section::make('Tags & Metadata')
                        Forms\Components\TagsInput::make('tags')
                            ->placeholder('Add tags...'),
                        Forms\Components\KeyValue::make('metadata')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                    ]),
            ]);
    }
    public static function table(Table $table): Table
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                Tables\Columns\TextColumn::make('reward_amount')
                    ->label('Reward')
                    ->formatStateUsing(fn($state, $record) =>
                        $record->reward_type === 'percentage' ? $state . '%' : '€' . number_format($state, 2))
                Tables\Columns\BadgeColumn::make('reward_type')
                    ->colors([
                        'primary' => 'percentage',
                        'success' => 'fixed',
                        'info' => 'points',
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Used')
                Tables\Columns\TextColumn::make('usage_limit')
                    ->label('Limit')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                Tables\Columns\TextColumn::make('campaign.name')
                    ->label('Campaign')
                Tables\Columns\TextColumn::make('created_at')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('reward_type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                        'points' => 'Points',
                Tables\Filters\SelectFilter::make('source')
                        'admin' => 'Admin Created',
                        'user' => 'User Generated',
                        'api' => 'API Generated',
                        'import' => 'Imported',
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
                Tables\Filters\Filter::make('expired')
                    ->query(fn($query) => $query->where('expires_at', '<', now())),
                Tables\Filters\SelectFilter::make('campaign_id')
                    ->relationship('campaign', 'name')
                    ->preload(),
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ->defaultSort('created_at', 'desc');
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => Pages\ListReferralCodes::route('/'),
            'create' => Pages\CreateReferralCode::route('/create'),
            'edit' => Pages\EditReferralCode::route('/{record}/edit'),
}
