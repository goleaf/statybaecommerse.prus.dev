<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

final class UserImpersonation extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.user_impersonation');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Admin');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('is_admin', false)
                    ->withCount(['orders'])
                    ->withSum(['orders' => function (Builder $query) {
                        $query->where('status', 'completed');
                    }], 'total')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.table.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label(__('admin.table.orders'))
                    ->numeric()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('orders_sum_total')
                    ->label(__('admin.table.total_spent'))
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label(__('admin.table.last_login'))
                    ->dateTime()
                    ->sortable()
                    ->since(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.table.status'))
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.filters.active_users')),
                    
                Tables\Filters\Filter::make('has_orders')
                    ->label(__('admin.filters.has_orders'))
                    ->query(fn (Builder $query): Builder => $query->has('orders')),
                    
                Tables\Filters\Filter::make('recent_activity')
                    ->label(__('admin.filters.recent_activity'))
                    ->query(fn (Builder $query): Builder => 
                        $query->where('last_login_at', '>=', now()->subDays(30))
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('impersonate')
                    ->label(__('admin.actions.impersonate'))
                    ->icon('heroicon-o-user')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.modals.impersonate_user'))
                    ->modalDescription(__('admin.modals.impersonate_description'))
                    ->modalSubmitActionLabel(__('admin.actions.start_impersonation'))
                    ->action(function (User $record): void {
                        if ($record->is_admin) {
                            Notification::make()
                                ->title(__('admin.notifications.cannot_impersonate_admin'))
                                ->danger()
                                ->send();
                            return;
                        }

                        session([
                            'impersonate' => [
                                'original_user_id' => auth()->id(),
                                'impersonated_user_id' => $record->id,
                                'started_at' => now()->toISOString(),
                            ]
                        ]);

                        auth()->login($record);

                        Notification::make()
                            ->title(__('admin.notifications.impersonation_started'))
                            ->body(__('admin.notifications.impersonating_user', ['name' => $record->name]))
                            ->success()
                            ->send();

                        $this->redirect('/');
                    })
                    ->visible(fn (User $record): bool => !$record->is_admin),

                Tables\Actions\Action::make('view_orders')
                    ->label(__('admin.actions.view_orders'))
                    ->icon('heroicon-o-shopping-bag')
                    ->color('info')
                    ->url(fn (User $record): string => 
                        route('filament.admin.resources.orders.index', [
                            'tableFilters' => ['user' => ['value' => $record->id]]
                        ])
                    )
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('send_notification')
                    ->label(__('admin.actions.send_notification'))
                    ->icon('heroicon-o-bell')
                    ->color('primary')
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.fields.title'))
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\Textarea::make('message')
                            ->label(__('admin.fields.message'))
                            ->required()
                            ->rows(4),
                            
                        Forms\Components\Select::make('type')
                            ->label(__('admin.fields.notification_type'))
                            ->options([
                                'info' => __('admin.notification_types.info'),
                                'success' => __('admin.notification_types.success'),
                                'warning' => __('admin.notification_types.warning'),
                                'danger' => __('admin.notification_types.danger'),
                            ])
                            ->default('info')
                            ->required(),
                    ])
                    ->action(function (array $data, User $record): void {
                        // Send notification to user
                        $record->notify(new \App\Notifications\AdminNotification(
                            $data['title'],
                            $data['message'],
                            $data['type']
                        ));

                        Notification::make()
                            ->title(__('admin.notifications.notification_sent'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label(__('admin.actions.activate'))
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each(fn($record) => $record->update(['is_active' => true])))
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label(__('admin.actions.deactivate'))
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each(fn($record) => $record->update(['is_active' => false])))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('last_login_at', 'desc');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('stop_impersonation')
                ->label(__('admin.actions.stop_impersonation'))
                ->icon('heroicon-o-arrow-left')
                ->color('danger')
                ->visible(fn (): bool => session()->has('impersonate'))
                ->action(function (): void {
                    $impersonateData = session('impersonate');
                    $originalUserId = $impersonateData['original_user_id'] ?? null;

                    if ($originalUserId) {
                        $originalUser = User::find($originalUserId);
                        if ($originalUser) {
                            auth()->login($originalUser);
                            session()->forget('impersonate');

                            Notification::make()
                                ->title(__('admin.notifications.impersonation_stopped'))
                                ->success()
                                ->send();

                            $this->redirect('/admin');
                        }
                    }
                }),
        ];
    }
}
