<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use \BackedEnum;

final class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.activity_logs');
    }

    public static function getModelLabel(): string
    {
        return __('admin.activity_logs.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.activity_logs.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.system');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label(__('admin.activity_logs.table.log_name'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'product' => 'success',
                        'order' => 'warning',
                        'user' => 'info',
                        'category' => 'primary',
                        'brand' => 'secondary',
                        'collection' => 'info',
                        'review' => 'success',
                        'discount' => 'warning',
                        'coupon' => 'primary',
                        'campaign' => 'secondary',
                        'media' => 'info',
                        'cart_item' => 'success',
                        'customer_group' => 'warning',
                        'legal_page' => 'primary',
                        'address' => 'secondary',
                        'inventory' => 'info',
                        'backup' => 'warning',
                        'currency' => 'primary',
                        'system' => 'danger',
                        'security' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => __('admin.activity_logs.log_types.' . $state, [], $state))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('event')
                    ->label(__('admin.activity_logs.table.event'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        'login' => 'success',
                        'logout' => 'gray',
                        'failed_login' => 'danger',
                        'password_changed' => 'warning',
                        'email_verified' => 'success',
                        'profile_updated' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => $state ? __('admin.activity_logs.events.' . $state, [], $state) : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.activity_logs.table.description'))
                    ->searchable()
                    ->limit(60)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 60 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label(__('admin.activity_logs.table.subject_type'))
                    ->formatStateUsing(fn(?string $state): string => $state ? __('admin.activity_logs.subject_types.' . $state, [], class_basename($state)) : '-')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label(__('admin.activity_logs.table.subject_id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label(__('admin.activity_logs.table.user'))
                    ->searchable()
                    ->sortable()
                    ->default(__('admin.activity_logs.details.system_generated'))
                    ->formatStateUsing(fn(?string $state, Activity $record): string => $record->causer?->name ?? __('admin.activity_logs.details.system_generated')),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label(__('admin.activity_logs.table.ip_address'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->copyMessage(__('admin.activity_logs.table.ip_address') . ' ' . __('admin.copied')),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label(__('admin.activity_logs.table.user_agent'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('batch_uuid')
                    ->label(__('admin.activity_logs.table.batch_uuid'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->copyMessage(__('admin.activity_logs.table.batch_uuid') . ' ' . __('admin.copied')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.activity_logs.table.date'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('has_properties')
                    ->label(__('admin.activity_logs.table.properties'))
                    ->boolean()
                    ->getStateUsing(fn(Activity $record): bool => !empty($record->properties))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label(__('admin.activity_logs.filters.log_type'))
                    ->options([
                        'product' => __('admin.activity_logs.log_types.product'),
                        'order' => __('admin.activity_logs.log_types.order'),
                        'user' => __('admin.activity_logs.log_types.user'),
                        'category' => __('admin.activity_logs.log_types.category'),
                        'brand' => __('admin.activity_logs.log_types.brand'),
                        'collection' => __('admin.activity_logs.log_types.collection'),
                        'review' => __('admin.activity_logs.log_types.review'),
                        'discount' => __('admin.activity_logs.log_types.discount'),
                        'coupon' => __('admin.activity_logs.log_types.coupon'),
                        'campaign' => __('admin.activity_logs.log_types.campaign'),
                        'media' => __('admin.activity_logs.log_types.media'),
                        'cart_item' => __('admin.activity_logs.log_types.cart_item'),
                        'customer_group' => __('admin.activity_logs.log_types.customer_group'),
                        'legal_page' => __('admin.activity_logs.log_types.legal_page'),
                        'address' => __('admin.activity_logs.log_types.address'),
                        'inventory' => __('admin.activity_logs.log_types.inventory'),
                        'backup' => __('admin.activity_logs.log_types.backup'),
                        'currency' => __('admin.activity_logs.log_types.currency'),
                        'system' => __('admin.activity_logs.log_types.system'),
                        'security' => __('admin.activity_logs.log_types.security'),
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('event')
                    ->label(__('admin.activity_logs.filters.event'))
                    ->options([
                        'created' => __('admin.activity_logs.events.created'),
                        'updated' => __('admin.activity_logs.events.updated'),
                        'deleted' => __('admin.activity_logs.events.deleted'),
                        'restored' => __('admin.activity_logs.events.restored'),
                        'login' => __('admin.activity_logs.events.login'),
                        'logout' => __('admin.activity_logs.events.logout'),
                        'failed_login' => __('admin.activity_logs.events.failed_login'),
                        'password_changed' => __('admin.activity_logs.events.password_changed'),
                        'email_verified' => __('admin.activity_logs.events.email_verified'),
                        'profile_updated' => __('admin.activity_logs.events.profile_updated'),
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label(__('admin.activity_logs.filters.subject_type'))
                    ->options([
                        'App\Models\Product' => __('admin.activity_logs.subject_types.App\Models\Product'),
                        'App\Models\Order' => __('admin.activity_logs.subject_types.App\Models\Order'),
                        'App\Models\User' => __('admin.activity_logs.subject_types.App\Models\User'),
                        'App\Models\Category' => __('admin.activity_logs.subject_types.App\Models\Category'),
                        'App\Models\Brand' => __('admin.activity_logs.subject_types.App\Models\Brand'),
                        'App\Models\Collection' => __('admin.activity_logs.subject_types.App\Models\Collection'),
                        'App\Models\Review' => __('admin.activity_logs.subject_types.App\Models\Review'),
                        'App\Models\Discount' => __('admin.activity_logs.subject_types.App\Models\Discount'),
                        'App\Models\Coupon' => __('admin.activity_logs.subject_types.App\Models\Coupon'),
                        'App\Models\Campaign' => __('admin.activity_logs.subject_types.App\Models\Campaign'),
                        'App\Models\Media' => __('admin.activity_logs.subject_types.App\Models\Media'),
                        'App\Models\CartItem' => __('admin.activity_logs.subject_types.App\Models\CartItem'),
                        'App\Models\CustomerGroup' => __('admin.activity_logs.subject_types.App\Models\CustomerGroup'),
                        'App\Models\LegalPage' => __('admin.activity_logs.subject_types.App\Models\LegalPage'),
                        'App\Models\Address' => __('admin.activity_logs.subject_types.App\Models\Address'),
                        'App\Models\Inventory' => __('admin.activity_logs.subject_types.App\Models\Inventory'),
                        'App\Models\Backup' => __('admin.activity_logs.subject_types.App\Models\Backup'),
                        'App\Models\Currency' => __('admin.activity_logs.subject_types.App\Models\Currency'),
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('causer_id')
                    ->label(__('admin.activity_logs.filters.user'))
                    ->options(function () {
                        return \App\Models\User::pluck('name', 'id')->filter(fn($label) => filled($label))->toArray();
                    })
                    ->searchable(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('admin.activity_logs.filters.from_date')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('admin.activity_logs.filters.until_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('has_properties')
                    ->label(__('admin.activity_logs.table.properties'))
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('properties')),
                Tables\Filters\Filter::make('system_activities')
                    ->label(__('admin.activity_logs.details.system_generated'))
                    ->query(fn(Builder $query): Builder => $query->whereNull('causer_id')),
            ])
            ->actions([
                Action::make('view_details')
                    ->label(__('admin.activity_logs.actions.view_details'))
                    ->icon('heroicon-o-eye')
                    ->modalContent(function (Activity $record) {
                        return view('filament.activity-log.view-modal', [
                            'activity' => $record,
                            'properties' => $record->properties->toArray(),
                        ]);
                    })
                    ->modalWidth('7xl')
                    ->modalHeading(__('admin.activity_logs.details.title')),
                Action::make('view_subject')
                    ->label(__('admin.view_subject'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn(Activity $record): ?string => $record->subject_type && $record->subject_id
                        ? match (class_basename($record->subject_type)) {
                            'Product' => route('filament.admin.resources.products.view', $record->subject_id),
                            'Order' => route('filament.admin.resources.orders.view', $record->subject_id),
                            'User' => route('filament.admin.resources.users.view', $record->subject_id),
                            'Category' => route('filament.admin.resources.categories.view', $record->subject_id),
                            'Brand' => route('filament.admin.resources.brands.view', $record->subject_id),
                            default => null,
                        }
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn(Activity $record): bool => $record->subject_type && $record->subject_id !== null),
            ])
            ->bulkActions([
                BulkAction::make('export')
                    ->label(__('admin.activity_logs.actions.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Collection $records) {
                        // Export logic would go here
                        return redirect()->back()->with('success', __('admin.activity_logs.messages.export_success'));
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->emptyStateHeading(__('admin.activity_logs.messages.no_activities'))
            ->emptyStateDescription(__('admin.activity_logs.messages.loading'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['description', 'log_name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            __('admin.activity_logs.table.log_name') => __('admin.activity_logs.log_types.' . $record->log_name, [], $record->log_name),
            __('admin.activity_logs.table.subject_type') => $record->subject_type ? __('admin.activity_logs.subject_types.' . $record->subject_type, [], class_basename($record->subject_type)) . ' #' . $record->subject_id : null,
            __('admin.activity_logs.table.user') => $record->causer?->name ?? __('admin.activity_logs.details.system_generated'),
            __('admin.activity_logs.table.date') => $record->created_at->format('Y-m-d H:i:s'),
            __('admin.activity_logs.table.event') => $record->event ? __('admin.activity_logs.events.' . $record->event, [], $record->event) : null,
        ];
    }
}
