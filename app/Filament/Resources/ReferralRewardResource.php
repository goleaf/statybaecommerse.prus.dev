<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ReferralRewardResource\Pages;
use App\Models\ReferralReward;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;

final class ReferralRewardResource extends Resource
{
    protected static ?string $model = ReferralReward::class;
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?int $navigationSort = 15;
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): string
    {
        return NavigationGroup::Referrals->getLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                Section::make('Reward Details')
                    ->columns(2)
                    ->schema([
                        Select::make('referral_id')
                            ->relationship('referral', 'code')
                            ->required(),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required(),
                        Select::make('order_id')
                            ->relationship('order', 'id')
                            ->nullable(),
                        Select::make('type')
                            ->options([
                                'discount' => 'Discount',
                                'credit' => 'Credit',
                                'points' => 'Points',
                                'gift' => 'Gift',
                            ])
                            ->required(),
                        TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->default(0.00),
                        TextInput::make('currency_code')
                            ->required()
                            ->maxLength(3),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'applied' => 'Applied',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        DatePicker::make('applied_at')
                            ->nullable(),
                        DatePicker::make('expires_at')
                            ->nullable(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->maxLength(65535)
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->inline(false)
                            ->default(true),
                        TextInput::make('priority')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        KeyValue::make('conditions')
                            ->label('Conditions (JSON)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addActionLabel('Add Condition')
                            ->columnSpanFull(),
                        KeyValue::make('reward_data')
                            ->label('Reward Data (JSON)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addActionLabel('Add Reward Data')
                            ->columnSpanFull(),
                        KeyValue::make('metadata')
                            ->label('Metadata (JSON)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addActionLabel('Add Metadata Item')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referral.code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money(fn (ReferralReward $record) => $record->currency_code)
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('applied_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean(),
                SelectFilter::make('type')
                    ->options([
                        'discount' => 'Discount',
                        'credit' => 'Credit',
                        'points' => 'Points',
                        'gift' => 'Gift',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'applied' => 'Applied',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('referral_id')
                    ->relationship('referral', 'code'),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListReferralRewards::route('/'),
            'create' => Pages\CreateReferralReward::route('/create'),
            'view' => Pages\ViewReferralReward::route('/{record}'),
            'edit' => Pages\EditReferralReward::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'type', 'status'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }
}