<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Enums\NavigationGroup;
use App\Filament\Resources\ReferralResource\Pages;
use App\Models\Referral;
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
 * ReferralResource
 *
 * Filament v4 resource for Referral management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ReferralResource extends Resource
{
    protected static ?string $model = Referral::class;    /** @var UnitEnum|string|null */
    protected static string | UnitEnum | null $navigationGroup = "Products";
    protected static ?int $navigationSort = 5;
    protected static ?string $recordTitleAttribute = 'referral_code';
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('referrals.title');
    }
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "Marketing";
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('referrals.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('referrals.single');
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
    public static function form(Schema $schema): Schema
        return $schema->components([
            Section::make(__('referrals.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('referrer_id')
                                ->label(__('referrals.referrer'))
                                ->relationship('referrer', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('referrer_name', $user->name);
                                            $set('referrer_email', $user->email);
                                        }
                                    }
                                }),
                            TextInput::make('referrer_name')
                                ->label(__('referrals.referrer_name'))
                                ->maxLength(255)
                                ->disabled(),
                        ]),
                            Select::make('referred_id')
                                ->label(__('referrals.referred'))
                                ->relationship('referred', 'name')
                                            $set('referred_name', $user->name);
                                            $set('referred_email', $user->email);
                            TextInput::make('referred_name')
                                ->label(__('referrals.referred_name'))
                    TextInput::make('referral_code')
                        ->label(__('referrals.referral_code'))
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->rules(['alpha_dash'])
                        ->helperText(__('referrals.referral_code_help')),
                ]),
            Section::make(__('referrals.reward_settings'))
                            TextInput::make('referrer_reward')
                                ->label(__('referrals.referrer_reward'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01)
                                ->minValue(0)
                                ->default(0)
                                ->helperText(__('referrals.referrer_reward_help')),
                            TextInput::make('referred_reward')
                                ->label(__('referrals.referred_reward'))
                                ->helperText(__('referrals.referred_reward_help')),
                            TextInput::make('referrer_reward_percentage')
                                ->label(__('referrals.referrer_reward_percentage'))
                                ->maxValue(100)
                                ->suffix('%')
                                ->helperText(__('referrals.referrer_reward_percentage_help')),
                            TextInput::make('referred_reward_percentage')
                                ->label(__('referrals.referred_reward_percentage'))
                                ->helperText(__('referrals.referred_reward_percentage_help')),
            Section::make(__('referrals.status_information'))
                            Select::make('status')
                                ->label(__('referrals.status'))
                                ->options([
                                    'pending' => __('referrals.statuses.pending'),
                                    'approved' => __('referrals.statuses.approved'),
                                    'rejected' => __('referrals.statuses.rejected'),
                                    'completed' => __('referrals.statuses.completed'),
                                    'expired' => __('referrals.statuses.expired'),
                                ])
                                ->default('pending'),
                            DateTimePicker::make('approved_at')
                                ->label(__('referrals.approved_at'))
                                ->displayFormat('d/m/Y H:i'),
                            DateTimePicker::make('completed_at')
                                ->label(__('referrals.completed_at'))
                            DateTimePicker::make('expires_at')
                                ->label(__('referrals.expires_at'))
            Section::make(__('referrals.settings'))
                            Toggle::make('is_active')
                                ->label(__('referrals.is_active'))
                                ->default(true),
                            Toggle::make('is_automatic')
                                ->label(__('referrals.is_automatic'))
                                ->default(false),
                    Textarea::make('notes')
                        ->label(__('referrals.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
        ]);
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
    public static function table(Table $table): Table
        return $table
            ->columns([
                TextColumn::make('referral_code')
                    ->label(__('referrals.referral_code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->badge()
                    ->color('blue'),
                TextColumn::make('referrer.name')
                    ->label(__('referrals.referrer'))
                    ->limit(50),
                TextColumn::make('referred.name')
                    ->label(__('referrals.referred'))
                TextColumn::make('status')
                    ->label(__('referrals.status'))
                    ->formatStateUsing(fn(string $state): string => __("referrals.statuses.{$state}"))
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'completed' => 'info',
                        'expired' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('referrer_reward')
                    ->label(__('referrals.referrer_reward'))
                    ->money('EUR')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('referred_reward')
                    ->label(__('referrals.referred_reward'))
                TextColumn::make('referrer_reward_percentage')
                    ->label(__('referrals.referrer_reward_percentage'))
                    ->numeric()
                    ->formatStateUsing(fn($state): string => $state ? $state . '%' : '-')
                TextColumn::make('referred_reward_percentage')
                    ->label(__('referrals.referred_reward_percentage'))
                IconColumn::make('is_active')
                    ->label(__('referrals.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_automatic')
                    ->label(__('referrals.is_automatic'))
                TextColumn::make('approved_at')
                    ->label(__('referrals.approved_at'))
                    ->dateTime()
                TextColumn::make('completed_at')
                    ->label(__('referrals.completed_at'))
                TextColumn::make('expires_at')
                    ->label(__('referrals.expires_at'))
                TextColumn::make('created_at')
                    ->label(__('referrals.created_at'))
                TextColumn::make('updated_at')
                    ->label(__('referrals.updated_at'))
            ])
            ->filters([
                SelectFilter::make('referrer_id')
                    ->relationship('referrer', 'name')
                    ->preload(),
                SelectFilter::make('referred_id')
                    ->relationship('referred', 'name')
                SelectFilter::make('status')
                    ->options([
                        'pending' => __('referrals.statuses.pending'),
                        'approved' => __('referrals.statuses.approved'),
                        'rejected' => __('referrals.statuses.rejected'),
                        'completed' => __('referrals.statuses.completed'),
                        'expired' => __('referrals.statuses.expired'),
                    ]),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('referrals.active_only'))
                    ->falseLabel(__('referrals.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_automatic')
                    ->trueLabel(__('referrals.automatic_only'))
                    ->falseLabel(__('referrals.manual_only'))
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('approve')
                    ->label(__('referrals.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Referral $record): bool => $record->status === 'pending')
                    ->action(function (Referral $record): void {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                        ]);
                        Notification::make()
                            ->title(__('referrals.approved_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('reject')
                    ->label(__('referrals.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                        $record->update(['status' => 'rejected']);
                            ->title(__('referrals.rejected_successfully'))
                Action::make('complete')
                    ->label(__('referrals.complete'))
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->visible(fn(Referral $record): bool => $record->status === 'approved')
                            'status' => 'completed',
                            'completed_at' => now(),
                            ->title(__('referrals.completed_successfully'))
                Action::make('toggle_active')
                    ->label(fn(Referral $record): string => $record->is_active ? __('referrals.deactivate') : __('referrals.activate'))
                    ->icon(fn(Referral $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Referral $record): string => $record->is_active ? 'warning' : 'success')
                        $record->update(['is_active' => !$record->is_active]);
                            ->title($record->is_active ? __('referrals.activated_successfully') : __('referrals.deactivated_successfully'))
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve')
                        ->label(__('referrals.approve_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'status' => 'approved',
                                'approved_at' => now(),
                            ]);
                            Notification::make()
                                ->title(__('referrals.bulk_approved_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('reject')
                        ->label(__('referrals.reject_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                            $records->each->update(['status' => 'rejected']);
                                ->title(__('referrals.bulk_rejected_success'))
                    BulkAction::make('activate')
                        ->label(__('referrals.activate_selected'))
                        ->icon('heroicon-o-eye')
                            $records->each->update(['is_active' => true]);
                                ->title(__('referrals.bulk_activated_success'))
                    BulkAction::make('deactivate')
                        ->label(__('referrals.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                            $records->each->update(['is_active' => false]);
                                ->title(__('referrals.bulk_deactivated_success'))
            ->defaultSort('created_at', 'desc');
     * Get the relations for this resource.
     * @return array
    public static function getRelations(): array
        return [
            //
        ];
     * Get the pages for this resource.
    public static function getPages(): array
            'index' => Pages\ListReferrals::route('/'),
            'create' => Pages\CreateReferral::route('/create'),
            'view' => Pages\ViewReferral::route('/{record}'),
            'edit' => Pages\EditReferral::route('/{record}/edit'),
}
