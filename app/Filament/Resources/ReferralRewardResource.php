<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Filament\Resources\ReferralRewardResource\Pages;
use App\Models\ReferralReward;
use App\Support\Filament\Components\Flatpickr;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use UnitEnum;

final class ReferralRewardResource extends Resource
{
    use TranslatableResource;

    protected static ?string $model = ReferralReward::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Referral';

    public static function form(Schema $schema): Schema
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('referral_rewards.sections.reward_details'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('referral_id')
                            ->label(__('referral_rewards.fields.referral'))
                            ->relationship(
                                name: 'referral',
                                titleAttribute: 'referral_code',
                                modifyQueryUsing: static fn (Builder $query): Builder => $query->withoutGlobalScopes(),
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('user_id')
                            ->label(__('referral_rewards.fields.user'))
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: static fn (Builder $query): Builder => $query->withoutGlobalScopes(),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('order_id')
                            ->label(__('referral_rewards.fields.order'))
                            ->relationship(
                                name: 'order',
                                titleAttribute: 'id',
                                modifyQueryUsing: static fn (Builder $query): Builder => $query->withoutGlobalScopes(),
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('type')
                            ->label(__('referral_rewards.fields.type'))
                            ->options([
                                'discount' => __('referral_rewards.types.discount'),
                                'credit'   => __('referral_rewards.types.credit'),
                                'points'   => __('referral_rewards.types.points'),
                                'gift'     => __('referral_rewards.types.gift'),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label(__('referral_rewards.fields.amount'))
                            ->numeric()
                            ->required()
                            ->prefix('€'),
                        Forms\Components\TextInput::make('currency_code')
                            ->label(__('referral_rewards.fields.currency_code'))
                            ->required()
                            ->maxLength(3)
                            ->default('EUR'),
                        Forms\Components\Select::make('status')
                            ->label(__('referral_rewards.fields.status'))
                            ->options([
                                'pending'   => __('referral_rewards.status.pending'),
                                'applied'   => __('referral_rewards.status.applied'),
                                'expired'   => __('referral_rewards.status.expired'),
                                'cancelled' => __('referral_rewards.status.cancelled'),
                            ])
                            ->required(),
                        Flatpickr::makeDate('applied_at')
                            ->label(__('referral_rewards.fields.applied_at'))
                            ->nullable(),
                        Flatpickr::makeDate('expires_at')
                            ->label(__('referral_rewards.fields.expires_at'))
                            ->nullable(),
                        Forms\Components\TextInput::make('title')
                            ->label(__('referral_rewards.fields.title'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label(__('referral_rewards.fields.description'))
                            ->maxLength(65535)
                            ->nullable(),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('referral_rewards.fields.is_active'))
                            ->inline(false)
                            ->default(true),
                        Forms\Components\TextInput::make('priority')
                            ->label(__('referral_rewards.fields.priority'))
                            ->numeric()
                            ->integer()
                            ->default(0),
                        Forms\Components\KeyValue::make('conditions')
                            ->label(__('referral_rewards.fields.conditions'))
                            ->keyLabel(__('referral_rewards.fields.condition_key'))
                            ->valueLabel(__('referral_rewards.fields.condition_value'))
                            ->reorderable()
                            ->addActionLabel(__('referral_rewards.actions.add_condition'))
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('reward_data')
                            ->label(__('referral_rewards.fields.reward_data'))
                            ->keyLabel(__('referral_rewards.fields.reward_key'))
                            ->valueLabel(__('referral_rewards.fields.reward_value'))
                            ->reorderable()
                            ->addActionLabel(__('referral_rewards.actions.add_reward_data'))
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('metadata')
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
                Tables\Columns\TextColumn::make('title')
                    ->label(__('referral_rewards.fields.title'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('referral.referral_code')
                    ->label(__('referral_rewards.fields.referral_code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('referral_rewards.fields.user_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('referral_rewards.fields.type'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money(fn (ReferralReward $record): string => $record->currency_code)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('referral_rewards.fields.status'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('referral_rewards.fields.is_active')),
                Tables\Columns\TextColumn::make('applied_at')
                    ->label(__('referral_rewards.fields.applied_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('referral_rewards.fields.expires_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('referral_rewards.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('referral_rewards.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('referral_rewards.filters.is_active'))
                    ->boolean(),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('referral_rewards.filters.type'))
                    ->options([
                        'discount' => __('referral_rewards.types.discount'),
                        'credit'   => __('referral_rewards.types.credit'),
                        'points'   => __('referral_rewards.types.points'),
                        'gift'     => __('referral_rewards.types.gift'),
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('referral_rewards.filters.status'))
                    ->options([
                        'pending'   => __('referral_rewards.status.pending'),
                        'applied'   => __('referral_rewards.status.applied'),
                        'expired'   => __('referral_rewards.status.expired'),
                        'cancelled' => __('referral_rewards.status.cancelled'),
                    ]),
                Tables\Filters\SelectFilter::make('referral_id')
                    ->label(__('referral_rewards.filters.referral'))
                    ->relationship('referral', 'referral_code'),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('referral_rewards.filters.user'))
                    ->relationship('user', 'name'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('apply')
                    ->label(__('referral_rewards.actions.apply'))
                    ->requiresConfirmation()
                    ->action(static function (ReferralReward $record): void {
                        $record->apply();
                    }),
                Action::make('expire')
                    ->label(__('referral_rewards.actions.expire'))
                    ->requiresConfirmation()
                    ->action(static function (ReferralReward $record): void {
                        $record->markAsExpired();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('apply')
                        ->label(__('referral_rewards.actions.apply_selected'))
                        ->requiresConfirmation()
                        ->action(static function (Collection $records): void {
                            $records
                                ->filter(static fn ($record): bool => $record instanceof ReferralReward)
                                ->each(static function (ReferralReward $record): void {
                                    $record->apply();
                                });
                        }),
                    BulkAction::make('expire')
                        ->label(__('referral_rewards.actions.expire_selected'))
                        ->requiresConfirmation()
                        ->action(static function (Collection $records): void {
                            $records
                                ->filter(static fn ($record): bool => $record instanceof ReferralReward)
                                ->each(static function (ReferralReward $record): void {
                                    $record->markAsExpired();
                                });
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(__('referral_rewards.sections.reward_details'))
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label(__('referral_rewards.fields.title')),
                        Infolists\Components\TextEntry::make('description')
                            ->label(__('referral_rewards.fields.description')),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label(__('referral_rewards.fields.user_name')),
                        Infolists\Components\TextEntry::make('referral_code')
                            ->label(__('referral_rewards.fields.referral_code'))
                            ->state(static fn (ReferralReward $record): ?string => $record->referral?->referral_code)
                            ->visible(static fn (ReferralReward $record): bool => filled($record->referral?->referral_code)),
                        Infolists\Components\TextEntry::make('order.id')
                            ->label(__('referral_rewards.fields.order')),
                    ])
                    ->columns(1),
            ]);
    }

    public static function getNavigationIcon(): ?string
    {
        $icon = self::$navigationIcon;

        return $icon instanceof BackedEnum ? $icon->value : $icon;
    }

    public static function getNavigationGroup(): ?string
    {
        $group = self::$navigationGroup;

        return $group instanceof UnitEnum ? $group->value : $group;
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
            'index'  => Pages\ListReferralRewards::route('/'),
            'create' => Pages\CreateReferralReward::route('/create'),
            'view'   => Pages\ViewReferralReward::route('/{record}'),
            'edit'   => Pages\EditReferralReward::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'type', 'status'];
    }

    public static function getNavigationBadge(): ?string
    {
        $modelClass = self::getModel();
        $count = (int) $modelClass::count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * @return Builder<ReferralReward>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with([
                'referral' => static fn (Relation $relation) => $relation->withoutGlobalScopes(),
                'user'     => static fn (Relation $relation) => $relation->withoutGlobalScopes(),
                'order'    => static fn (Relation $relation) => $relation->withoutGlobalScopes(),
            ]);
    }
}
