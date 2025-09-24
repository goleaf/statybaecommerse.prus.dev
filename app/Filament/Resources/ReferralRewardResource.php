<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralRewardResource\Pages;
use App\Models\ReferralReward;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

final class ReferralRewardResource extends Resource
{
    protected static ?string $model = ReferralReward::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'title';

    protected static UnitEnum|string|null $navigationGroup = 'Referral';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('referral_rewards.sections.reward_details'))
                    ->columns(2)
                    ->schema([
                        Select::make('referral_id')
                            ->label(__('referral_rewards.fields.referral'))
                            ->relationship('referral', 'referral_code')
                            ->modifyRelationshipQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('user_id')
                            ->label(__('referral_rewards.fields.user'))
                            ->relationship('user', 'name')
                            ->modifyRelationshipQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('order_id')
                            ->label(__('referral_rewards.fields.order'))
                            ->relationship('order', 'id')
                            ->modifyRelationshipQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes())
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('type')
                            ->label(__('referral_rewards.fields.type'))
                            ->options([
                                'discount' => __('referral_rewards.types.discount'),
                                'credit' => __('referral_rewards.types.credit'),
                                'points' => __('referral_rewards.types.points'),
                                'gift' => __('referral_rewards.types.gift'),
                            ])
                            ->required(),
                        TextInput::make('amount')
                            ->label(__('referral_rewards.fields.amount'))
                            ->numeric()
                            ->required()
                            ->prefix('€'),
                        TextInput::make('currency_code')
                            ->label(__('referral_rewards.fields.currency_code'))
                            ->required()
                            ->maxLength(3)
                            ->default('EUR'),
                        Select::make('status')
                            ->label(__('referral_rewards.fields.status'))
                            ->options([
                                'pending' => __('referral_rewards.status.pending'),
                                'active' => __('referral_rewards.status.active'),
                                'applied' => __('referral_rewards.status.applied'),
                                'expired' => __('referral_rewards.status.expired'),
                                'cancelled' => __('referral_rewards.status.cancelled'),
                            ])
                            ->required(),
                        DatePicker::make('applied_at')
                            ->label(__('referral_rewards.fields.applied_at'))
                            ->nullable(),
                        DatePicker::make('expires_at')
                            ->label(__('referral_rewards.fields.expires_at'))
                            ->nullable(),
                        TextInput::make('title')
                            ->label(__('referral_rewards.fields.title'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('referral_rewards.fields.description'))
                            ->maxLength(65535)
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label(__('referral_rewards.fields.is_active'))
                            ->inline(false)
                            ->default(true),
                        TextInput::make('priority')
                            ->label(__('referral_rewards.fields.priority'))
                            ->numeric()
                            ->integer()
                            ->default(0),
                        KeyValue::make('conditions')
                            ->label(__('referral_rewards.fields.conditions'))
                            ->keyLabel(__('referral_rewards.fields.condition_key'))
                            ->valueLabel(__('referral_rewards.fields.condition_value'))
                            ->reorderable()
                            ->addActionLabel(__('referral_rewards.actions.add_condition'))
                            ->columnSpanFull(),
                        KeyValue::make('reward_data')
                            ->label(__('referral_rewards.fields.reward_data'))
                            ->keyLabel(__('referral_rewards.fields.reward_key'))
                            ->valueLabel(__('referral_rewards.fields.reward_value'))
                            ->reorderable()
                            ->addActionLabel(__('referral_rewards.actions.add_reward_data'))
                            ->columnSpanFull(),
                        KeyValue::make('metadata')
                            ->label(__('referral_rewards.fields.metadata'))
                            ->keyLabel(__('referral_rewards.fields.metadata_key'))
                            ->valueLabel(__('referral_rewards.fields.metadata_value'))
                            ->reorderable()
                            ->addActionLabel(__('referral_rewards.actions.add_metadata'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('referral_rewards.fields.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referral.referral_code')
                    ->label(__('referral_rewards.fields.referral_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('referral_rewards.fields.user_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('referral_rewards.fields.type'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money(fn(ReferralReward $record) => $record->currency_code)
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('referral_rewards.fields.status'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('referral_rewards.fields.is_active')),
                TextColumn::make('applied_at')
                    ->label(__('referral_rewards.fields.applied_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label(__('referral_rewards.fields.expires_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('referral_rewards.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('referral_rewards.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('referral_rewards.filters.is_active'))
                    ->boolean(),
                SelectFilter::make('type')
                    ->label(__('referral_rewards.filters.type'))
                    ->options([
                        'discount' => __('referral_rewards.types.discount'),
                        'credit' => __('referral_rewards.types.credit'),
                        'points' => __('referral_rewards.types.points'),
                        'gift' => __('referral_rewards.types.gift'),
                    ]),
                SelectFilter::make('status')
                    ->label(__('referral_rewards.filters.status'))
                    ->options([
                        'pending' => __('referral_rewards.status.pending'),
                        'active' => __('referral_rewards.status.active'),
                        'applied' => __('referral_rewards.status.applied'),
                        'expired' => __('referral_rewards.status.expired'),
                        'cancelled' => __('referral_rewards.status.cancelled'),
                    ]),
                SelectFilter::make('referral_id')
                    ->label(__('referral_rewards.filters.referral'))
                    ->relationship('referral', 'referral_code'),
                SelectFilter::make('user_id')
                    ->label(__('referral_rewards.filters.user'))
                    ->relationship('user', 'name'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('apply')
                    ->label(__('referral_rewards.actions.apply'))
                    ->requiresConfirmation()
                    ->action(fn(ReferralReward $record) => $record->apply()),
                Action::make('expire')
                    ->label(__('referral_rewards.actions.expire'))
                    ->requiresConfirmation()
                    ->action(fn(ReferralReward $record) => $record->markAsExpired()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('apply')
                        ->label(__('referral_rewards.actions.apply_selected'))
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                if ($record instanceof ReferralReward) {
                                    $record->apply();
                                }
                            }
                        }),
                    BulkAction::make('expire')
                        ->label(__('referral_rewards.actions.expire_selected'))
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                if ($record instanceof ReferralReward) {
                                    $record->markAsExpired();
                                }
                            }
                        }),
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
        $count = (int) self::$model::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
}
