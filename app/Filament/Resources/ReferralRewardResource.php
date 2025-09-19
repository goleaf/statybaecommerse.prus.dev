<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ReferralRewardResource\Pages;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * ReferralRewardResource
 *
 * Filament v4 resource for ReferralReward management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ReferralRewardResource extends Resource
{
    protected static ?string $model = ReferralReward::class;

    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'amount';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('referral_rewards.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return "Marketing";
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('referral_rewards.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('referral_rewards.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('referral_rewards.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('referral_id')
                                ->label(__('referral_rewards.referral'))
                                ->relationship('referral', 'referral_code')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $referral = Referral::find($state);
                                        if ($referral) {
                                            $set('referral_code', $referral->referral_code);
                                            $set('referrer_id', $referral->referrer_id);
                                            $set('referred_id', $referral->referred_id);
                                        }
                                    }
                                }),
                            TextInput::make('referral_code')
                                ->label(__('referral_rewards.referral_code'))
                                ->required()
                                ->maxLength(50)
                                ->disabled(),
                        ]),
                    Grid::make(2)
                        ->components([
                            Select::make('user_id')
                                ->label(__('referral_rewards.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('user_name', $user->name);
                                            $set('user_email', $user->email);
                                        }
                                    }
                                }),
                            TextInput::make('user_name')
                                ->label(__('referral_rewards.user_name'))
                                ->required()
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                ]),
            Section::make(__('referral_rewards.reward_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('amount')
                                ->label(__('referral_rewards.amount'))
                                ->numeric()
                                ->required()
                                ->prefix('€')
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('referral_rewards.amount_help')),
                            Select::make('type')
                                ->label(__('referral_rewards.type'))
                                ->options([
                                    'referrer' => __('referral_rewards.types.referrer'),
                                    'referred' => __('referral_rewards.types.referred'),
                                    'bonus' => __('referral_rewards.types.bonus'),
                                    'penalty' => __('referral_rewards.types.penalty'),
                                ])
                                ->required()
                                ->default('referrer'),
                        ]),
                    Grid::make(2)
                        ->components([
                            Select::make('status')
                                ->label(__('referral_rewards.status'))
                                ->options([
                                    'pending' => __('referral_rewards.statuses.pending'),
                                    'approved' => __('referral_rewards.statuses.approved'),
                                    'paid' => __('referral_rewards.statuses.paid'),
                                    'cancelled' => __('referral_rewards.statuses.cancelled'),
                                    'expired' => __('referral_rewards.statuses.expired'),
                                ])
                                ->required()
                                ->default('pending'),
                            TextInput::make('currency')
                                ->label(__('referral_rewards.currency'))
                                ->required()
                                ->maxLength(3)
                                ->default('EUR')
                                ->rules(['alpha']),
                        ]),
                ]),
            Section::make(__('referral_rewards.payment_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            DateTimePicker::make('paid_at')
                                ->label(__('referral_rewards.paid_at'))
                                ->displayFormat('d/m/Y H:i'),
                            TextInput::make('payment_method')
                                ->label(__('referral_rewards.payment_method'))
                                ->maxLength(50)
                                ->helperText(__('referral_rewards.payment_method_help')),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('transaction_id')
                                ->label(__('referral_rewards.transaction_id'))
                                ->maxLength(100)
                                ->helperText(__('referral_rewards.transaction_id_help')),
                            TextInput::make('payment_reference')
                                ->label(__('referral_rewards.payment_reference'))
                                ->maxLength(100)
                                ->helperText(__('referral_rewards.payment_reference_help')),
                        ]),
                ]),
            Section::make(__('referral_rewards.settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('referral_rewards.is_active'))
                                ->default(true),
                            Toggle::make('is_automatic')
                                ->label(__('referral_rewards.is_automatic'))
                                ->default(false),
                        ]),
                    Grid::make(2)
                        ->components([
                            DateTimePicker::make('expires_at')
                                ->label(__('referral_rewards.expires_at'))
                                ->displayFormat('d/m/Y H:i'),
                            TextInput::make('priority')
                                ->label(__('referral_rewards.priority'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('referral_rewards.priority_help')),
                        ]),
                    Textarea::make('notes')
                        ->label(__('referral_rewards.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referral_code')
                    ->label(__('referral_rewards.referral_code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('blue'),
                TextColumn::make('user.name')
                    ->label(__('referral_rewards.user'))
                    ->sortable()
                    ->limit(50),
                TextColumn::make('amount')
                    ->label(__('referral_rewards.amount'))
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold')
                    ->alignCenter(),
                TextColumn::make('type')
                    ->label(__('referral_rewards.type'))
                    ->formatStateUsing(fn(string $state): string => __("referral_rewards.types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'referrer' => 'green',
                        'referred' => 'blue',
                        'bonus' => 'purple',
                        'penalty' => 'red',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label(__('referral_rewards.status'))
                    ->formatStateUsing(fn(string $state): string => __("referral_rewards.statuses.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'paid' => 'info',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('currency')
                    ->label(__('referral_rewards.currency'))
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_method')
                    ->label(__('referral_rewards.payment_method'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('transaction_id')
                    ->label(__('referral_rewards.transaction_id'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_reference')
                    ->label(__('referral_rewards.payment_reference'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('referral_rewards.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_automatic')
                    ->label(__('referral_rewards.is_automatic'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('paid_at')
                    ->label(__('referral_rewards.paid_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->label(__('referral_rewards.expires_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('priority')
                    ->label(__('referral_rewards.priority'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('referral_rewards.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('referral_rewards.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('referral_id')
                    ->label(__('referral_rewards.referral'))
                    ->relationship('referral', 'referral_code')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label(__('referral_rewards.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label(__('referral_rewards.type'))
                    ->options([
                        'referrer' => __('referral_rewards.types.referrer'),
                        'referred' => __('referral_rewards.types.referred'),
                        'bonus' => __('referral_rewards.types.bonus'),
                        'penalty' => __('referral_rewards.types.penalty'),
                    ]),
                SelectFilter::make('status')
                    ->label(__('referral_rewards.status'))
                    ->options([
                        'pending' => __('referral_rewards.statuses.pending'),
                        'approved' => __('referral_rewards.statuses.approved'),
                        'paid' => __('referral_rewards.statuses.paid'),
                        'cancelled' => __('referral_rewards.statuses.cancelled'),
                        'expired' => __('referral_rewards.statuses.expired'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('referral_rewards.is_active'))
                    ->boolean()
                    ->trueLabel(__('referral_rewards.active_only'))
                    ->falseLabel(__('referral_rewards.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_automatic')
                    ->label(__('referral_rewards.is_automatic'))
                    ->boolean()
                    ->trueLabel(__('referral_rewards.automatic_only'))
                    ->falseLabel(__('referral_rewards.manual_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('approve')
                    ->label(__('referral_rewards.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(ReferralReward $record): bool => $record->status === 'pending')
                    ->action(function (ReferralReward $record): void {
                        $record->update(['status' => 'approved']);

                        Notification::make()
                            ->title(__('referral_rewards.approved_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('pay')
                    ->label(__('referral_rewards.pay'))
                    ->icon('heroicon-o-currency-euro')
                    ->color('info')
                    ->visible(fn(ReferralReward $record): bool => $record->status === 'approved')
                    ->action(function (ReferralReward $record): void {
                        $record->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);

                        Notification::make()
                            ->title(__('referral_rewards.paid_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('cancel')
                    ->label(__('referral_rewards.cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(ReferralReward $record): bool => in_array($record->status, ['pending', 'approved']))
                    ->action(function (ReferralReward $record): void {
                        $record->update(['status' => 'cancelled']);

                        Notification::make()
                            ->title(__('referral_rewards.cancelled_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('toggle_active')
                    ->label(fn(ReferralReward $record): string => $record->is_active ? __('referral_rewards.deactivate') : __('referral_rewards.activate'))
                    ->icon(fn(ReferralReward $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(ReferralReward $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (ReferralReward $record): void {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('referral_rewards.activated_successfully') : __('referral_rewards.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve')
                        ->label(__('referral_rewards.approve_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'approved']);

                            Notification::make()
                                ->title(__('referral_rewards.bulk_approved_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('pay')
                        ->label(__('referral_rewards.pay_selected'))
                        ->icon('heroicon-o-currency-euro')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'status' => 'paid',
                                'paid_at' => now(),
                            ]);

                            Notification::make()
                                ->title(__('referral_rewards.bulk_paid_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('cancel')
                        ->label(__('referral_rewards.cancel_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'cancelled']);

                            Notification::make()
                                ->title(__('referral_rewards.bulk_cancelled_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('activate')
                        ->label(__('referral_rewards.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('referral_rewards.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('referral_rewards.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('referral_rewards.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralRewards::route('/'),
            'create' => Pages\CreateReferralReward::route('/create'),
            'view' => Pages\ViewReferralReward::route('/{record}'),
            'edit' => Pages\EditReferralReward::route('/{record}/edit'),
        ];
    }
}
