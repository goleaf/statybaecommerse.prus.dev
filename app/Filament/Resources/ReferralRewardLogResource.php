<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\ReferralRewardLogResource\Pages;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Filament\Tables\Actions\ViewAction as TableViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * ReferralRewardLogResource
 *
 * Filament v4 resource for ReferralRewardLog management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ReferralRewardLogResource extends Resource
{
    use HasNav;

    protected static ?string $model = ReferralRewardLog::class;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'action';

    /**
     * Navigation group for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationGroup = 'Analytics';

    public static function getNavigationLabel(): string
    {
        return __('admin.referral_reward_logs.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.referral_reward_logs.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.referral_reward_logs.model_label');
    }

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return $form
            ->schema([
                Section::make(__('admin.referral_reward_logs.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('referral_reward_id')
                                    ->label(__('admin.referral_reward_logs.referral_reward'))
                                    ->options(ReferralReward::pluck('id', 'id'))
                                    ->required()
                                    ->searchable(),

                                Select::make('user_id')
                                    ->label(__('admin.referral_reward_logs.user'))
                                    ->options(User::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),

                                Select::make('action')
                                    ->label(__('admin.referral_reward_logs.action'))
                                    ->options(self::getActionOptions())
                                    ->required()
                                    ->default(ReferralRewardLog::ACTION_EARNED),

                                TextInput::make('ip_address')
                                    ->label(__('admin.referral_reward_logs.ip_address'))
                                    ->ip()
                                    ->maxLength(45),

                                TextInput::make('user_agent')
                                    ->label(__('admin.referral_reward_logs.user_agent'))
                                    ->maxLength(500),
                            ]),

                        Textarea::make('data')
                            ->label(__('admin.referral_reward_logs.data'))
                            ->rows(5)
                            ->helperText(__('admin.referral_reward_logs.data_help')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->columns([
                TextColumn::make('referralReward.id')
                    ->label(__('admin.referral_reward_logs.referral_reward'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label(__('admin.referral_reward_logs.user'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('action')
                    ->label(__('admin.referral_reward_logs.action'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        ReferralRewardLog::ACTION_EARNED => 'success',
                        ReferralRewardLog::ACTION_REDEEMED => 'info',
                        ReferralRewardLog::ACTION_EXPIRED => 'warning',
                        ReferralRewardLog::ACTION_CANCELLED => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('ip_address')
                    ->label(__('admin.referral_reward_logs.ip_address'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user_agent')
                    ->label(__('admin.referral_reward_logs.user_agent'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (! is_string($state)) {
                            return null;
                        }

                        return mb_strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('referral_reward_id')
                    ->label(__('admin.referral_reward_logs.referral_reward'))
                    ->options(ReferralReward::pluck('id', 'id'))
                    ->searchable(),

                SelectFilter::make('user_id')
                    ->label(__('admin.referral_reward_logs.user'))
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('action')
                    ->label(__('admin.referral_reward_logs.action'))
                    ->options(self::getActionOptions()),
            ])
            ->recordActions([
                TableViewAction::make(),
                TableEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function getActionOptions(): array
    {
        return array_combine(
            ReferralRewardLog::ACTIONS,
            array_map(
                fn (string $action): string => __('admin.referral_reward_logs.actions.' . $action),
                ReferralRewardLog::ACTIONS,
            ),
        );
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
            'index'  => Pages\ListReferralRewardLogs::route('/'),
            'create' => Pages\CreateReferralRewardLog::route('/create'),
            'view'   => Pages\ViewReferralRewardLog::route('/{record}'),
            'edit'   => Pages\EditReferralRewardLog::route('/{record}/edit'),
        ];
    }
}
