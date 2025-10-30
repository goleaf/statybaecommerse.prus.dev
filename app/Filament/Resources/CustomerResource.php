<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Widgets\InlineCharts\CustomerOrdersSparkline;
use App\Models\City;
use App\Models\Customer;
use App\Models\Scopes\ActiveScope;
use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use LaraZeus\InlineChart\Tables\Columns\InlineChart;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Tapp\FilamentValueRangeFilter\Filters\ValueRangeFilter;
use UnitEnum;

final class CustomerResource extends Resource
{
    /**
     * Icon displayed in the navigation menu.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $model = Customer::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Users';
    }

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Customers';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('customers.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('customers.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('customers.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('customers.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('email')
                                ->label(__('customers.email'))
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('phone')
                                ->label(__('customers.phone'))
                                ->tel()
                                ->maxLength(20),
                            TextInput::make('address')
                                ->label(__('customers.address'))
                                ->maxLength(500),
                        ]),
                    Textarea::make('description')
                        ->label(__('customers.description'))
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ]),
            SchemaSection::make(__('customers.location'))
                ->schema([
                    SchemaGrid::make(3)
                        ->schema([
                            Select::make('country_id')
                                ->label(__('customers.country'))
                                ->relationship('country', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set): void {
                                    // Reset the dependent city select using Filament's schema Set helper
                                    // so reactive selections work consistently in the Livewire test harness.
                                    if ($state) {
                                        $set('city_id', null);
                                    }
                                }),
                            Select::make('city_id')
                                ->label(__('customers.city'))
                                ->searchable()
                                ->preload()
                                ->live()
                                ->options(function (Get $get): array {
                                    // Resolve the city list using Filament's schema-aware getter so
                                    // Livewire form hydration in tests hits the same code path as production.
                                    $query = City::query()->orderBy('name');

                                    if ($countryId = $get('country_id')) {
                                        $query->where('country_id', $countryId);
                                    }

                                    return $query->pluck('name', 'id')->all();
                                })
                                ->getSearchResultsUsing(function (Get $get, string $search): array {
                                    return City::query()
                                        ->where('name', 'like', "%{$search}%")
                                        ->when($get('country_id'), fn (Builder $query, $countryId): Builder => $query->where('country_id', $countryId))
                                        ->orderBy('name')
                                        ->limit(50)
                                        ->pluck('name', 'id')
                                        ->all();
                                })
                                ->getOptionLabelUsing(fn ($value): ?string => City::query()->find($value)?->name),
                            TextInput::make('postal_code')
                                ->label(__('customers.postal_code'))
                                ->maxLength(20),
                        ]),
                ]),
            SchemaSection::make(__('customers.company'))
                ->schema([
                    Select::make('company_id')
                        ->label(__('customers.company'))
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                ]),
            SchemaSection::make(__('customers.settings'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('customers.is_active'))
                        ->default(true),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                BadgeableColumn::make('name')
                    ->label(__('customers.name'))
                    ->searchable(['name', 'email', 'phone', 'country.name', 'city.name', 'company.name'])
                    ->sortable()
                    ->weight('bold')
                    ->asPills()
                    ->tooltip(fn (Customer $record): ?string => mb_strlen((string) $record->name) > 40 ? $record->name : null)
                    ->prefixBadges(function (Customer $record): array {
                        // Display geographic and organizational context up front.
                        return collect([
                            $record->country?->name
                                ? Badge::make('country')
                                    ->label(__('customers.badges.country', ['country' => $record->country->name]))
                                    ->color('info')
                                : null,
                            $record->city?->name
                                ? Badge::make('city')
                                    ->label(__('customers.badges.city', ['city' => $record->city->name]))
                                    ->color('info')
                                : null,
                            $record->company?->name
                                ? Badge::make('company')
                                    ->label(__('customers.badges.company', ['company' => $record->company->name]))
                                    ->color('gray')
                                : null,
                        ])->filter()->values()->all();
                    })
                    ->suffixBadges(function (Customer $record): array {
                        // Surface activation, verification, and revenue health alongside the customer name.
                        $isVerified = (bool) data_get($record->metadata, 'is_verified', false);
                        $ordersCount = (int) ($record->orders_count ?? 0);
                        $ordersTotal = $record->orders_sum_total ?? $record->orders_total_sum ?? null;

                        return collect([
                            Badge::make('active')
                                ->label($record->is_active ? __('customers.badges.active') : __('customers.badges.inactive'))
                                ->color($record->is_active ? 'success' : 'danger'),
                            Badge::make('verified')
                                ->label($isVerified ? __('customers.badges.verified') : __('customers.badges.unverified'))
                                ->color($isVerified ? 'success' : 'warning'),
                            Badge::make('orders')
                                ->label(__('customers.badges.orders', ['count' => number_format($ordersCount)]))
                                ->color($ordersCount > 0 ? 'primary' : 'gray'),
                            $ordersTotal !== null
                                ? Badge::make('ltv')
                                    ->label(__('customers.badges.ltv', ['total' => Number::currency((float) $ordersTotal, 'EUR', app()->getLocale())]))
                                    ->color('gray')
                                : null,
                        ])->filter()->values()->all();
                    }),
                ViewColumn::make('quick_links')
                    ->label(__('Quick links'))
                    ->view('filament.tables.columns.list-group')
                    ->state(function (Customer $record): array {
                        return collect([
                            filled($record->email) ? [
                                'label' => __('Email :email', ['email' => $record->email]),
                                'url'   => 'mailto:' . $record->email,
                                'icon'  => 'heroicon-o-envelope',
                                'color' => 'info',
                            ] : null,
                            filled($record->phone) ? [
                                'label' => __('Call :phone', ['phone' => $record->phone]),
                                'url'   => 'tel:' . preg_replace('/[^0-9+]/', '', (string) $record->phone),
                                'icon'  => 'heroicon-o-phone',
                                'color' => 'success',
                            ] : null,
                        ])
                            ->filter()
                            ->values()
                            ->all();
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label(__('customers.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                // Badge to show whether the customer confirmed their email address.
                BadgeColumn::make('email_verified_at')
                    ->label(__('customers.email_verified_at'))
                    ->getStateUsing(static fn (Customer $record): string => filled($record->email_verified_at) ? 'verified' : 'unverified')
                    ->formatStateUsing(static fn (string $state): string => __('customers.badges.' . $state))
                    ->color(static fn (string $state): string => match ($state) {
                        'verified'   => 'success',
                        'unverified' => 'warning',
                        default      => 'gray',
                    })
                    ->icon(static fn (string $state): ?string => match ($state) {
                        'verified'   => 'heroicon-o-check-circle',
                        'unverified' => 'heroicon-o-exclamation-triangle',
                        default      => null,
                    })
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label(__('customers.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('address')
                    ->label(__('customers.address'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('postal_code')
                    ->label(__('customers.postal_code'))
                    ->toggleable(isToggledHiddenByDefault: true),
                // Inline orders sparkline to visualize recent activity without leaving the table.
                InlineChart::make('orders_sparkline')
                    ->label(__('customers.orders_trend'))
                    ->chart(CustomerOrdersSparkline::class)
                    ->maxWidth(160)
                    ->maxHeight(48)
                    ->icon('heroicon-o-chart-bar'),
                TextColumn::make('created_at')
                    ->label(__('customers.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('customers.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->relationship('country', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('city_id')
                    ->relationship('city', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('company_id')
                    ->relationship('company', 'name')
                    ->preload()
                    ->searchable(),
                ValueRangeFilter::make('orders_count')
                    ->label(__('customers.orders_count')),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('customers.active_only'))
                    ->falseLabel(__('customers.inactive_only'))
                    ->native(false),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label(__('Export'))
                    ->exports(self::getCustomerExportPresets()),
            ])
            ->actions([
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (Customer $record): string => $record->is_active ? __('customers.deactivate') : __('customers.activate'))
                    ->icon(fn (Customer $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Customer $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Customer $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('customers.activated_successfully') : __('customers.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('customers.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('customers.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('customers.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('customers.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    ExportBulkAction::make()
                        ->label(__('Export selected'))
                        ->exports(self::getCustomerExportPresets()),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * @return array<int, ExcelExport>
     */
    private static function getCustomerExportPresets(): array
    {
        return [
            ExcelExport::make('visible_columns')
                ->fromTable()
                ->queue()
                ->withChunkSize(500),
            ExcelExport::make('ltv_snapshot')
                ->fromTable()
                ->withColumns([
                    Column::make('name')
                        ->heading(__('customers.name')),
                    Column::make('email')
                        ->heading(__('customers.email')),
                    Column::make('orders_count')
                        ->heading(__('customers.orders_count')),
                    Column::make('ltv')
                        ->heading(__('customers.ltv'))
                        ->formatStateUsing(fn ($state, Customer $record): float => (float) $record->orders()->sum('total'))
                        ->format(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE),
                ])
                ->queue()
                ->withChunkSize(500),
        ];
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view'   => Pages\ViewCustomer::route('/{record}'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ActiveScope::class,
            ])
            ->with([
                'country:id,name',
                'city:id,name',
                'company:id,name',
            ])
            ->withCount(['orders'])
            ->withSum('orders', 'total');
    }
}
