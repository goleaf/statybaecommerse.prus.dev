<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CouponUsageResource\Pages;
// Import shared helper to keep searchable inputs consistent with repository conventions.
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use App\Support\Concerns\HasNav;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\CouponSearch;
use App\Support\Search\CustomerSearch;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
// Alias schema grid to match Filament v4 schema-based layouts in the resource.
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Tabs as SchemaTabs;
use Filament\Schemas\Components\Tabs\Tab as SchemaTab;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

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

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaTabs::make('coupon_usage_tabs')
                ->tabs([
                    SchemaTab::make(__('admin.coupon_usages.form.tabs.basic_information'))
                        ->schema([
                            SchemaSection::make(__('admin.coupon_usages.form.sections.basic_information'))
                                ->schema([
                                    SchemaGrid::make(2)
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
                                                ->afterStateUpdated(function (SearchableInput $component, ?string $state, Set $set): void {
                                                    if ($state === null || $state === '') {
                                                        // Clear the persisted relation id when the lookup resets.
                                                        SearchableInputHelper::clear($component, $set, ['coupon_id' => null]);

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
                                                ->afterStateUpdated(function (SearchableInput $component, ?string $state, Set $set): void {
                                                    if ($state === null || $state === '') {
                                                        SearchableInputHelper::clear($component, $set, ['user_id' => null]);

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
                                    SupportFlatpickr::makeDateTime('used_at')
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
                    SchemaTab::make(__('admin.coupon_usages.form.tabs.usage_details'))
                        ->schema([
                            SchemaSection::make(__('admin.coupon_usages.form.sections.usage_details'))
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('admin.coupon_usages.form.sections.basic_information'))
                ->schema([
                    TextEntry::make('coupon.code')
                        ->label(__('admin.coupon_usages.form.fields.coupon'))
                        ->placeholder('-'),
                    TextEntry::make('user.name')
                        ->label(__('admin.coupon_usages.form.fields.user'))
                        ->placeholder('-'),
                    TextEntry::make('order_id')
                        ->label(__('admin.coupon_usages.form.fields.order'))
                        ->formatStateUsing(static fn ($state): string => $state ? "Order #{$state}" : '-'),
                    TextEntry::make('discount_amount')
                        ->label(__('admin.coupon_usages.form.fields.discount_amount'))
                        ->formatStateUsing(static function ($state): string {
                            if ($state === null || $state === '') {
                                return number_format(0, 2, '.', '');
                            }

                            return number_format((float) $state, 2, '.', '');
                        }),
                    TextEntry::make('used_at')
                        ->label(__('admin.coupon_usages.form.fields.used_at'))
                        ->dateTime()
                        ->placeholder('-'),
                ])
                ->columns(2),
            SchemaSection::make(__('admin.coupon_usages.form.sections.usage_details'))
                ->schema([
                    KeyValueEntry::make('metadata')
                        ->label(__('admin.coupon_usages.form.fields.metadata'))
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('created_at')
                        ->label(__('admin.coupon_usages.form.fields.created_at'))
                        ->dateTime()
                        ->placeholder('-'),
                    TextEntry::make('updated_at')
                        ->label(__('admin.coupon_usages.form.fields.updated_at'))
                        ->dateTime()
                        ->placeholder('-'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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
                Filter::make('used_at')
                    ->label(__('admin.coupon_usages.filters.used_at'))
                    ->form([
                        SupportFlatpickr::makeDateTime('from')->label(__('admin.coupon_usages.filters.used_at_from')),
                        SupportFlatpickr::makeDateTime('until')->label(__('admin.coupon_usages.filters.used_at_until')),
                    ])
                    ->query(function (Builder $query, array|string|null $data): Builder {
                        // Allow both the range picker (array payload) and the single date test helper string.
                        $exactDate = is_array($data) ? null : $data;

                        if (filled($exactDate)) {
                            return $query->whereDate('used_at', '=', $exactDate);
                        }

                        $from = is_array($data) ? ($data['from'] ?? null) : null;
                        $until = is_array($data) ? ($data['until'] ?? null) : null;

                        return $query
                            ->when($from, fn (Builder $q, $date): Builder => $q->where('used_at', '>=', $date))
                            ->when($until, fn (Builder $q, $date): Builder => $q->where('used_at', '<=', $date));
                    }),
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
                // Surface the standard delete button so Livewire table actions stay in sync with the feature tests.
                DeleteAction::make(),
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
