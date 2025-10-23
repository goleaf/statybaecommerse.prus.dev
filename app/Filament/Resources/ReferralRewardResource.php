<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use Filament\Schemas\Schema;
use App\Filament\Resources\ReferralRewardResource\Pages;
use App\Models\ReferralReward;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable as SpatieTranslatableResource;

final class ReferralRewardResource extends Resource
{
    use SpatieTranslatableResource; // Enable locale-aware management for Spatie translatable attributes.

    protected static ?string $model = ReferralReward::class;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-gift';

    protected static ?int $navigationSort = 15;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static \UnitEnum|string|null $navigationGroup = 'Referral';

    public static function form(Schema $schema): Schema   
    {
        return $schema
            ->schema([
                Forms\Components\SchemaSection::make(__('referral_rewards.sections.reward_details'))
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('referral_id')
                            ->label(__('referral_rewards.fields.referral'))
                            ->relationship(
                                name: 'referral',
                                titleAttribute: 'referral_code',
                                modifyQueryUsing: fn (Builder $query) => $query->withoutGlobalScopes(),
                            )
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('user_id')
                            ->label(__('referral_rewards.fields.user'))
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->withoutGlobalScopes(),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('order_id')
                            ->label(__('referral_rewards.fields.order'))
                            ->relationship(
                                name: 'order',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn (Builder $query) => $query->withoutGlobalScopes(),
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
                        SupportFlatpickr::makeDate('applied_at')
                            ->label(__('referral_rewards.fields.applied_at'))
                            ->nullable(),
                        SupportFlatpickr::makeDate('expires_at')
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
        // Configure the table definition for the streamlined Filament v4 return type.
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
                TextColumn::make('amount')
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

    public static function infolist(Schema $schema): Schema   
    {
        return $schema
            ->schema([
                Infolists\Components\SchemaSection::make(__('referral_rewards.sections.reward_details'))
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('referral_rewards.fields.title')),
                        TextEntry::make('description')
                            ->label(__('referral_rewards.fields.description')),
                        TextEntry::make('user.name')
                            ->label(__('referral_rewards.fields.user_name')),
                        TextEntry::make('referral_code')
                            ->label(__('referral_rewards.fields.referral_code'))
                            ->state(fn (ReferralReward $record): ?string => $record->referral?->referral_code)
                            ->visible(fn (ReferralReward $record): bool => filled($record->referral?->referral_code)),
                        TextEntry::make('order.id')
                            ->label(__('referral_rewards.fields.order')),
                    ])
                    ->columns(1),
            ]);
    }

    public static function getNavigationIcon(): ?string
    {
        $icon = self::$navigationIcon;

        if ($icon instanceof BackedEnum) {
            return $icon->value;
        }

        if ($icon instanceof \UnitEnum) {
            return method_exists($icon, 'value') ? $icon->value : $icon->name;
        }

        return $icon;
    }

    public static function getNavigationGroup(): \UnitEnum|string|null
    {
        $group = self::$navigationGroup;

        if ($group instanceof BackedEnum) {
            return $group->value;
        }

        if ($group instanceof \UnitEnum) {
            return method_exists($group, 'value') ? $group->value : $group->name;
        }

        return $group;
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
