<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use BackedEnum;
use UnitEnum;

final class SecurityAudit extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|UnitEnum|null $navigationGroup = \App\Enums\NavigationGroup::System;
    protected static ?int $navigationSort = 2;

    public array $securityStats = [];
    public array $suspiciousActivities = [];

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.security_audit');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Admin');
    }

    public function mount(): void
    {
        $this->loadSecurityStats();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->with(['causer', 'subject'])
                    ->where('created_at', '>=', now()->subDays(7))
                    ->orderByDesc('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label(__('admin.table.log_type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'user' => 'info',
                        'order' => 'success',
                        'product' => 'warning',
                        'security' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.table.activity'))
                    ->limit(50)
                    ->tooltip(fn($record): string => $record->description),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label(__('admin.table.user'))
                    ->default('System')
                    ->searchable(),
                Tables\Columns\TextColumn::make('causer.email')
                    ->label(__('admin.table.email'))
                    ->default('N/A')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label(__('admin.table.subject'))
                    ->getStateUsing(fn($record): string =>
                        $record->subject_type ? class_basename($record->subject_type) : 'N/A'),
                Tables\Columns\TextColumn::make('event')
                    ->label(__('admin.table.event'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        'login' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.table.timestamp'))
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label(__('admin.filters.log_type'))
                    ->options([
                        'user' => __('admin.log_types.user'),
                        'order' => __('admin.log_types.order'),
                        'product' => __('admin.log_types.product'),
                        'security' => __('admin.log_types.security'),
                    ]),
                Tables\Filters\SelectFilter::make('event')
                    ->label(__('admin.filters.event_type'))
                    ->options([
                        'created' => __('admin.events.created'),
                        'updated' => __('admin.events.updated'),
                        'deleted' => __('admin.events.deleted'),
                        'login' => __('admin.events.login'),
                    ]),
                Tables\Filters\Filter::make('suspicious')
                    ->label(__('admin.filters.suspicious_only'))
                    ->query(fn(Builder $query): Builder =>
                        $query
                            ->where('description', 'like', '%failed%')
                            ->orWhere('description', 'like', '%suspicious%')
                            ->orWhere('description', 'like', '%blocked%')),
            ])
            ->recordActions([
                Actions\Action::make('view_details')
                    ->label(__('admin.actions.view_details'))
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn($record) => view('filament.modals.activity-details', compact('record')))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('admin.actions.close')),
                Actions\Action::make('flag_suspicious')
                    ->label(__('admin.actions.flag_suspicious'))
                    ->icon('heroicon-o-flag')
                    ->color('danger')
                    ->visible(fn($record): bool => !str_contains($record->description, 'FLAGGED'))
                    ->action(function ($record): void {
                        $record->update([
                            'description' => $record->description . ' [FLAGGED AS SUSPICIOUS]'
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title(__('admin.notifications.activity_flagged'))
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public function loadSecurityStats(): void
    {
        $this->securityStats = [
            'total_activities' => Activity::count(),
            'activities_today' => Activity::whereDate('created_at', today())->count(),
            'failed_logins' => Activity::where('description', 'like', '%failed%login%')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'admin_activities' => Activity::whereHasMorph('causer', [User::class], function ($query) {
                $query->where('is_admin', true);
            })->where('created_at', '>=', now()->subDays(7))->count(),
            'suspicious_activities' => Activity::where('description', 'like', '%suspicious%')
                ->orWhere('description', 'like', '%blocked%')
                ->orWhere('description', 'like', '%failed%')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];

        // Load suspicious activities
        $this->suspiciousActivities = Activity::where('created_at', '>=', now()->subDays(7))
            ->where(function ($query) {
                $query
                    ->where('description', 'like', '%failed%')
                    ->orWhere('description', 'like', '%suspicious%')
                    ->orWhere('description', 'like', '%blocked%');
            })
            ->with('causer')
            ->limit(10)
            ->get()
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('security_scan')
                ->label(__('admin.actions.security_scan'))
                ->icon('heroicon-o-magnifying-glass')
                ->color('danger')
                ->action(function (): void {
                    $this->performSecurityScan();
                }),
            Action::make('export_audit_log')
                ->label(__('admin.actions.export_audit_log'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function (): void {
                    $this->exportAuditLog();
                }),
            Action::make('clear_old_logs')
                ->label(__('admin.actions.clear_old_logs'))
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription(__('admin.modals.clear_logs_description'))
                ->action(function (): void {
                    $deleted = Activity::where('created_at', '<', now()->subMonths(6))->delete();

                    \Filament\Notifications\Notification::make()
                        ->title(__('admin.notifications.logs_cleared'))
                        ->body(__('admin.notifications.deleted_records', ['count' => $deleted]))
                        ->success()
                        ->send();
                }),
        ];
    }

    private function performSecurityScan(): void
    {
        $issues = [];

        // Check for users without 2FA
        $usersWithout2FA = User::where('is_admin', true)
            ->where('two_factor_enabled', false)
            ->count();
        if ($usersWithout2FA > 0) {
            $issues[] = __('admin.security_issues.admin_without_2fa', ['count' => $usersWithout2FA]);
        }

        // Check for inactive admin accounts
        $inactiveAdmins = User::where('is_admin', true)
            ->where('last_login_at', '<', now()->subMonths(3))
            ->count();
        if ($inactiveAdmins > 0) {
            $issues[] = __('admin.security_issues.inactive_admins', ['count' => $inactiveAdmins]);
        }

        // Check for suspicious login patterns
        $suspiciousLogins = Activity::where('description', 'like', '%failed%login%')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
        if ($suspiciousLogins > 10) {
            $issues[] = __('admin.security_issues.suspicious_logins', ['count' => $suspiciousLogins]);
        }

        \Filament\Notifications\Notification::make()
            ->title(__('admin.notifications.security_scan_completed'))
            ->body(__('admin.notifications.security_issues_found', ['count' => count($issues)]))
            ->color(count($issues) > 0 ? 'warning' : 'success')
            ->send();
    }

    private function exportAuditLog(): void
    {
        $activities = Activity::with(['causer', 'subject'])
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $csv = "Timestamp,Log Type,Event,User,Email,Subject,Description,IP Address\n";

        foreach ($activities as $activity) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $activity->created_at->format('Y-m-d H:i:s'),
                $activity->log_name,
                $activity->event ?? 'N/A',
                $activity->causer?->name ?? 'System',
                $activity->causer?->email ?? 'N/A',
                $activity->subject_type ? class_basename($activity->subject_type) : 'N/A',
                str_replace('"', '""', $activity->description),
                $activity->properties['ip'] ?? 'N/A'
            );
        }

        $filename = 'security_audit_' . now()->format('Y-m-d_H-i-s') . '.csv';
        \Storage::disk('public')->put('exports/' . $filename, $csv);

        \Filament\Notifications\Notification::make()
            ->title(__('admin.notifications.audit_log_exported'))
            ->success()
            ->recordActions([
                \Filament\Notifications\Actions\Action::make('download')
                    ->label(__('admin.actions.download'))
                    ->url(asset('storage/exports/' . $filename))
                    ->openUrlInNewTab(),
            ])
            ->send();
    }
}
