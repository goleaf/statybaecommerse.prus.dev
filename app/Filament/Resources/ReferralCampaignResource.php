<?php

declare(strict_types=1);
declare(strict_types=1);
declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralCampaignResource\Pages;
use App\Models\ReferralCampaign;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

final class ReferralCampaignResource extends Resource
{
    protected static ?string $model = ReferralCampaign::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 14;

    protected static ?string $recordTitleAttribute = 'name';

    protected static UnitEnum|string|null $navigationGroup = 'System';

    public static function getNavigationLabel(): string
    {
        return __('admin.referral_campaigns.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.referral_campaigns.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.referral_campaigns.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('admin.referral_campaigns.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.referral_campaigns.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->translatable(),
                                Textarea::make('description')
                                    ->label(__('admin.referral_campaigns.description'))
                                    ->maxLength(65535)
                                    ->nullable()
                                    ->translatable(),
                                Toggle::make('is_active')
                                    ->label(__('admin.referral_campaigns.is_active'))
                                    ->inline(false)
                                    ->default(true),
                                DatePicker::make('start_date')
                                    ->label(__('admin.referral_campaigns.start_date'))
                                    ->nullable(),
                                DatePicker::make('end_date')
                                    ->label(__('admin.referral_campaigns.end_date'))
                                    ->nullable(),
                            ]),
                    ]),
                Section::make(__('admin.referral_campaigns.reward_settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('reward_amount')
                                    ->label(__('admin.referral_campaigns.reward_amount'))
                                    ->numeric()
                                    ->default(0.0)
                                    ->prefix('€'),
                                Select::make('reward_type')
                                    ->label(__('admin.referral_campaigns.reward_type'))
                                    ->options([
                                        'discount' => __('admin.referral_campaigns.reward_types.discount'),
                                        'credit' => __('admin.referral_campaigns.reward_types.credit'),
                                        'points' => __('admin.referral_campaigns.reward_types.points'),
                                        'gift' => __('admin.referral_campaigns.reward_types.gift'),
                                    ])
                                    ->nullable(),
                                TextInput::make('max_referrals_per_user')
                                    ->label(__('admin.referral_campaigns.max_referrals_per_user'))
                                    ->numeric()
                                    ->integer()
                                    ->nullable()
                                    ->helperText(__('admin.referral_campaigns.max_referrals_per_user_help')),
                                TextInput::make('max_total_referrals')
                                    ->label(__('admin.referral_campaigns.max_total_referrals'))
                                    ->numeric()
                                    ->integer()
                                    ->nullable()
                                    ->helperText(__('admin.referral_campaigns.max_total_referrals_help')),
                            ]),
                    ]),
                Section::make(__('admin.referral_campaigns.advanced_settings'))
                    ->schema([
                        KeyValue::make('conditions')
                            ->label(__('admin.referral_campaigns.conditions'))
                            ->keyLabel(__('admin.referral_campaigns.condition_key'))
                            ->valueLabel(__('admin.referral_campaigns.condition_value'))
                            ->reorderable()
                            ->addActionLabel(__('admin.referral_campaigns.add_condition'))
                            ->columnSpanFull(),
                        KeyValue::make('metadata')
                            ->label(__('admin.referral_campaigns.metadata'))
                            ->keyLabel(__('admin.referral_campaigns.metadata_key'))
                            ->valueLabel(__('admin.referral_campaigns.metadata_value'))
                            ->reorderable()
                            ->addActionLabel(__('admin.referral_campaigns.add_metadata'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.referral_campaigns.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('description')
                    ->label(__('admin.referral_campaigns.description'))
                    ->searchable()
                    ->limit(50),
                TextColumn::make('reward_amount')
                    ->label(__('admin.referral_campaigns.reward_amount'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('reward_type')
                    ->label(__('admin.referral_campaigns.reward_type'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'discount' => 'success',
                        'credit' => 'info',
                        'points' => 'warning',
                        'gift' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('max_referrals_per_user')
                    ->label(__('admin.referral_campaigns.max_referrals_per_user'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?: __('admin.common.unlimited')),
                TextColumn::make('max_total_referrals')
                    ->label(__('admin.referral_campaigns.max_total_referrals'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?: __('admin.common.unlimited')),
                IconColumn::make('is_active')
                    ->label(__('admin.referral_campaigns.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label(__('admin.referral_campaigns.start_date'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('admin.referral_campaigns.end_date'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('admin.referral_campaigns.is_active'))
                    ->boolean(),
                SelectFilter::make('reward_type')
                    ->label(__('admin.referral_campaigns.reward_type'))
                    ->options([
                        'discount' => __('admin.referral_campaigns.reward_types.discount'),
                        'credit' => __('admin.referral_campaigns.reward_types.credit'),
                        'points' => __('admin.referral_campaigns.reward_types.points'),
                        'gift' => __('admin.referral_campaigns.reward_types.gift'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'view' => Pages\ViewReferralCampaign::route('/{record}'),
            'edit' => Pages\EditReferralCampaign::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }
}
