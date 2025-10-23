<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Filament\Resources\CouponUsageResource\Pages;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use App\Support\Filament\Components\Flatpickr;

final class CouponUsageResource extends Resource
{
    use HasNav;

    protected static ?string $model = CouponUsage::class;

    public static function getPluralModelLabel(): string
    {
        return __('admin.coupon_usages.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.coupon_usages.single');
    }

    public static function form(Form $form): Form|array
    {
        return $form->components([
            Tabs::make('coupon_usage_tabs')
                ->tabs([
                    Tab::make(__('admin.coupon_usages.form.tabs.basic_information'))
                        ->schema([
                            Section::make(__('admin.coupon_usages.form.sections.basic_information'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            SearchableInput::make('coupon_id')
                                                ->label(__('admin.coupon_usages.form.fields.coupon'))
                                                ->placeholder(__('admin.coupon_usages.form.fields.coupon'))
                                                ->required()
                                                ->searchUsing(fn (string $search): array => CouponSearch::byCode($search))
                                                ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) $state : null)
                                                ->afterStateHydrated(function (SearchableInput $component, ?int $state, ?CouponUsage $record): void {
                                                    // Hydrate via helper to centralise metadata rules from the documentation.
                                                    SearchableInputHelper::hydrate(
                                                        $component,
                                                        $state,
                                                        static function (int $value) use ($record): ?array {
                                                            $coupon = $record?->coupon ?? Coupon::query()
                                                                ->select(['id', 'code', 'name'])
                                                                ->find($value);

                                                            if (! $coupon instanceof Coupon) {
                                                                return null;
                                                            }

                                                            $label = trim(sprintf('%s — %s', (string) ($coupon->code ?? ''), (string) ($coupon->name ?? '')));

                                                            return [
                                                                'value' => $coupon->getKey(),
                                                                'label' => $label,
                                                            ];
                                                        },
                                                    );
                                                })
                                                ->afterStateUpdated(function (?string $state, Set $set): void {
                                                    if ($state === null || $state === '') {
                                                        // Clear the persisted relation id when the lookup resets.
                                                        SearchableInputHelper::clear($set, ['coupon_id' => null]);

                                                        return;
                                                    }

                                                    $set('coupon_id', (int) $state);
                                                }),
                                            SearchableInput::make('user_id')
                                                ->label(__('admin.coupon_usages.form.fields.user'))
                                                ->placeholder('Name, email or phone')
                                                ->required()
                                                ->searchUsing(fn (string $search): array => CustomerSearch::byEmailPhoneName($search))
                                                ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) $state : null)
                                                ->afterStateHydrated(function (SearchableInput $component, ?int $state, ?CouponUsage $record): void {
                                                    // Helper ensures metadata hydration matches docs/forms/SEARCHABLE_INPUT_METADATA.md.
                                                    SearchableInputHelper::hydrate(
                                                        $component,
                                                        $state,
                                                        static function (int $value) use ($record): ?array {
                                                            $user = $record?->user ?? User::query()
                                                                ->select(['id', 'name', 'email'])
                                                                ->find($value);

                                                            if (! $user instanceof User) {
                                                                return null;
                                                            }

                                                            $label = trim(sprintf('%s <%s>', (string) ($user->name ?? ''), (string) ($user->email ?? '')));

                                                            return [
                                                                'value' => $user->getKey(),
                                                                'label' => $label,
                                                            ];
                                                        },
                                                    );
                                                })
                                                ->afterStateUpdated(function (?string $state, Set $set): void {
                                                    if ($state === null || $state === '') {
                                                        SearchableInputHelper::clear($set, ['user_id' => null]);

                                                        return;
                                                    }

                                                    $set('user_id', (int) $state);
                                                }),
                                            Select::make('order_id')
                                                ->label(__('admin.coupon_usages.form.fields.order'))
                                                ->relationship('order', 'id')
                                                ->searchable()
                                                ->preload(),
                                            TextInput::make('discount_amount')
                                                ->label(__('admin.coupon_usages.form.fields.discount_amount'))
                                                ->numeric()
                                                ->minValue(0)
                                                ->prefix('€')
                                                ->required(),
                                        ]),
                                    Flatpickr::makeDateTime('used_at')
                                        ->label(__('admin.coupon_usages.form.fields.used_at'))
                                        ->required()
                                        ->default(now())
                                        ->columnSpanFull(),
                                    KeyValue::make('metadata')
                                        ->label(__('admin.coupon_usages.form.fields.metadata'))
                                        ->keyLabel(__('admin.coupon_usages.form.fields.key'))
                                        ->valueLabel(__('admin.coupon_usages.form.fields.value'))
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make(__('admin.coupon_usages.form.tabs.usage_details'))
                        ->schema([
                            Section::make(__('admin.coupon_usages.form.sections.usage_details'))
                                ->schema([
                                    Placeholder::make('coupon_name')
                                        ->label(__('admin.coupon_usages.form.fields.coupon_name'))
                                        ->content(fn (?Model $record) => $record?->coupon?->name ?? '-'),
                                    Placeholder::make('user_email')
                                        ->label(__('admin.coupon_usages.form.fields.user_email'))
                                        ->content(fn (?Model $record) => $record?->user?->email ?? '-'),
                                    Placeholder::make('order_total')
                                        ->label(__('admin.coupon_usages.form.fields.order_total'))
                                        ->content(fn (?Model $record): string => $record?->order
                                            ? Number::currency(
                                                (float) $record->order->total,
                                                $record->order->currency ?? 'EUR',
                                                locale: app()->getLocale(),
                                            )
                                            : '-'),
                                    Textarea::make('notes')
                                        ->label(__('admin.coupon_usages.form.fields.notes'))
                                        ->rows(3),
                                ])->columns(2),
                        ]),
                ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table|array
    {
        return $table
            ->columns([
                TextColumn::make('coupon.code')
                    ->label(__('admin.coupon_usages.form.fields.coupon'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('admin.coupon_usages.form.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.id')
                    ->label(__('admin.coupon_usages.form.fields.order'))
                    ->formatStateUsing(fn ($state) => $state ? "Order #{$state}" : '-')
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->label(__('admin.coupon_usages.form.fields.discount_amount'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('used_at')
                    ->label(__('admin.coupon_usages.form.fields.used_at'))
                    ->dateTime()
                    ->sortable(),
                BadgeColumn::make('usage_period')
                    ->label(__('admin.coupon_usages.form.fields.usage_period'))
                    ->state(fn (CouponUsage $record): string => $record->usage_period)
                    ->colors([
                        'success' => fn (?string $state): bool => in_array($state, [__('admin.coupon_usages.periods.today'), __('admin.coupon_usages.periods.this_week')], true),
                        'warning' => fn (?string $state): bool => $state === __('admin.coupon_usages.periods.this_month'),
                        'danger'  => fn (?string $state): bool => $state === __('admin.coupon_usages.periods.older'),
                    ]),
            ])
            ->filters([
                SelectFilter::make('coupon_id')
                    ->label(__('admin.coupon_usages.filters.coupon'))
                    ->relationship('coupon', 'code')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label(__('admin.coupon_usages.filters.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('order_id')
                    ->label(__('admin.coupon_usages.filters.order'))
                    ->relationship('order', 'id')
                    ->searchable()
                    ->preload(),
                Filter::make('used_at_range')
                    ->label(__('admin.coupon_usages.filters.used_at'))
                    ->form([
                        Flatpickr::makeDateTime('from')->label(__('admin.coupon_usages.filters.used_at_from')),
                        Flatpickr::makeDateTime('until')->label(__('admin.coupon_usages.filters.used_at_until')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->where('used_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->where('used_at', '<=', $date))),
                TernaryFilter::make('used_today')
                    ->label(__('admin.coupon_usages.filters.used_today'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->usedToday(),
                        false: fn (Builder $query): Builder => $query->whereDate('used_at', '!=', today()),
                    ),
                TernaryFilter::make('used_this_week')
                    ->label(__('admin.coupon_usages.filters.used_this_week'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->usedThisWeek(),
                        false: fn (Builder $query): Builder => $query->whereNotBetween('used_at', [now()->startOfWeek(), now()->endOfWeek()]),
                    ),
                TernaryFilter::make('used_this_month')
                    ->label(__('admin.coupon_usages.filters.used_this_month'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->usedThisMonth(),
                        false: fn (Builder $query): Builder => $query->whereNotBetween('used_at', [now()->startOfMonth(), now()->endOfMonth()]),
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('export_usage_report')
                    ->label(__('admin.coupon_usages.actions.export_usage_report'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function (CouponUsage $record): void {
                        FilamentNotification::make()
                            ->title(__('admin.coupon_usages.usage_report_exported_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('export_bulk_report')
                        ->label(__('admin.coupon_usages.actions.export_bulk_report'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function (EloquentCollection $records): void {
                            FilamentNotification::make()
                                ->title(__('admin.coupon_usages.bulk_report_exported_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('used_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCouponUsages::route('/'),
            'create' => Pages\CreateCouponUsage::route('/create'),
            'view'   => Pages\ViewCouponUsage::route('/{record}'),
            'edit'   => Pages\EditCouponUsage::route('/{record}/edit'),
        ];
    }
}
