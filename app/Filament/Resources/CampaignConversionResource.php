<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CampaignConversionResource\Pages;
use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as InfolistGrid;
use Filament\Schemas\Components\Section as InfolistSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Components\Tabs\Tab;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

/**
 * CampaignConversionResource
 *
 * Filament v4 resource for CampaignConversion management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CampaignConversionResource extends Resource
{
    use HasNav;

    protected static ?string $model = CampaignConversion::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rocket-launch';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::Campaigns;

    public static function getNavigationLabel(): string
    {
        return __('campaign_conversions.title');
    }

    

    public static function getPluralModelLabel(): string
    {
        return __('campaign_conversions.plural');
    }

    public static function getModelLabel(): string
    {
        return __('campaign_conversions.single');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('campaign_conversions.form.basic_information'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('campaign_id')
                                ->label(__('campaign_conversions.form.campaign_id'))
                                ->relationship('campaign', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->getOptionLabelFromRecordUsing(fn (Campaign $record): string => $record->name),
                            Select::make('order_id')
                                ->label(__('campaign_conversions.form.order_id'))
                                ->relationship('order', 'id')
                                ->searchable()
                                ->preload()
                                ->getOptionLabelFromRecordUsing(fn (Order $record): string => (string) $record->getKey())
                                ->placeholder('-'),
                            Select::make('customer_id')
                                ->label(__('campaign_conversions.form.customer_id'))
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()
                                ->getOptionLabelFromRecordUsing(fn (User $record): string => trim($record->name.' <'.$record->email.'>')),
                        ]),
                ])
                ->columns(1),
            Section::make(__('campaign_conversions.form.conversion_details'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('conversion_type')
                                ->label(__('campaign_conversions.form.conversion_type'))
                                ->options([
                                    'purchase' => __('campaign_conversions.conversion_types.purchase'),
                                    'signup' => __('campaign_conversions.conversion_types.signup'),
                                    'download' => __('campaign_conversions.conversion_types.download'),
                                    'subscription' => __('campaign_conversions.conversion_types.subscription'),
                                    'lead' => __('campaign_conversions.conversion_types.lead'),
                                    'trial' => __('campaign_conversions.conversion_types.trial'),
                                    'custom' => __('campaign_conversions.conversion_types.custom'),
                                ])
                                ->required()
                                ->native(false),
                            Select::make('status')
                                ->label(__('campaign_conversions.form.status'))
                                ->options([
                                    'pending' => __('campaign_conversions.statuses.pending'),
                                    'completed' => __('campaign_conversions.statuses.completed'),
                                    'cancelled' => __('campaign_conversions.statuses.cancelled'),
                                    'refunded' => __('campaign_conversions.statuses.refunded'),
                                ])
                                ->native(false),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('conversion_value')
                                ->label(__('campaign_conversions.form.conversion_value'))
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->prefix('€'),
                            DateTimePicker::make('converted_at')
                                ->label(__('campaign_conversions.form.converted_at'))
                                ->required()
                                ->seconds(false),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('session_id')
                                ->label(__('campaign_conversions.form.session_id'))
                                ->maxLength(255),
                            TextInput::make('conversion_rate')
                                ->label(__('campaign_conversions.form.conversion_rate'))
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(1)
                                ->step(0.0001),
                        ]),
                ]),
            Section::make(__('campaign_conversions.form.tracking_information'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('source')
                                ->label(__('campaign_conversions.form.source'))
                                ->maxLength(255),
                            TextInput::make('medium')
                                ->label(__('campaign_conversions.form.medium'))
                                ->maxLength(255),
                            TextInput::make('campaign_name')
                                ->label(__('campaign_conversions.form.campaign_name'))
                                ->maxLength(255),
                            TextInput::make('utm_content')
                                ->label(__('campaign_conversions.form.utm_content'))
                                ->maxLength(255),
                            TextInput::make('utm_term')
                                ->label(__('campaign_conversions.form.utm_term'))
                                ->maxLength(255),
                            TextInput::make('referrer')
                                ->label(__('campaign_conversions.form.referrer'))
                                ->maxLength(500),
                        ]),
                ]),
            Section::make(__('campaign_conversions.form.device_information'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('device_type')
                                ->label(__('campaign_conversions.form.device_type'))
                                ->options([
                                    'mobile' => __('campaign_conversions.device_types.mobile'),
                                    'tablet' => __('campaign_conversions.device_types.tablet'),
                                    'desktop' => __('campaign_conversions.device_types.desktop'),
                                    'unknown' => __('campaign_conversions.device_types.unknown'),
                                ])
                                ->native(false),
                            TextInput::make('browser')
                                ->label(__('campaign_conversions.form.browser'))
                                ->maxLength(255),
                            TextInput::make('os')
                                ->label(__('campaign_conversions.form.os'))
                                ->maxLength(255),
                            TextInput::make('country')
                                ->label(__('campaign_conversions.form.country'))
                                ->maxLength(2),
                            TextInput::make('city')
                                ->label(__('campaign_conversions.form.city'))
                                ->maxLength(255),
                        ]),
                    Grid::make(3)
                        ->schema([
                            Toggle::make('is_mobile')
                                ->label(__('campaign_conversions.form.is_mobile')),
                            Toggle::make('is_tablet')
                                ->label(__('campaign_conversions.form.is_tablet')),
                            Toggle::make('is_desktop')
                                ->label(__('campaign_conversions.form.is_desktop')),
                        ]),
                ]),
            Section::make(__('campaign_conversions.form.additional_information'))
                ->schema([
                    Textarea::make('notes')
                        ->label(__('campaign_conversions.form.notes'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('campaign_conversions.table.id'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('campaign.name')
                    ->label(__('campaign_conversions.table.campaign'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('conversion_type')
                    ->label(__('campaign_conversions.table.conversion_type'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => $state ? __('campaign_conversions.conversion_types.'.$state) : '-')
                    ->toggleable(),
                TextColumn::make('conversion_value')
                    ->label(__('campaign_conversions.table.conversion_value'))
                    ->money('eur')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('campaign_conversions.table.status'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled', 'refunded' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? __('campaign_conversions.statuses.'.$state) : '-')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('customer.name')
                    ->label(__('campaign_conversions.table.customer'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('source')
                    ->label(__('campaign_conversions.table.source'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('medium')
                    ->label(__('campaign_conversions.table.medium'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('device_type')
                    ->label(__('campaign_conversions.table.device_type'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'mobile' => __('campaign_conversions.device_types.mobile'),
                        'tablet' => __('campaign_conversions.device_types.tablet'),
                        'desktop' => __('campaign_conversions.device_types.desktop'),
                        default => __('campaign_conversions.device_types.unknown'),
                    })
                    ->toggleable(),
                TextColumn::make('country')
                    ->label(__('campaign_conversions.table.country'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('roi')
                    ->label(__('campaign_conversions.table.roi'))
                    ->formatStateUsing(fn ($state): string => $state === null ? '-' : number_format((float) $state * 100, 2).' %')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('converted_at')
                    ->label(__('campaign_conversions.table.converted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_conversions.filters.campaign'))
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('conversion_type')
                    ->label(__('campaign_conversions.filters.conversion_type'))
                    ->options([
                        'purchase' => __('campaign_conversions.conversion_types.purchase'),
                        'signup' => __('campaign_conversions.conversion_types.signup'),
                        'download' => __('campaign_conversions.conversion_types.download'),
                        'subscription' => __('campaign_conversions.conversion_types.subscription'),
                        'lead' => __('campaign_conversions.conversion_types.lead'),
                        'trial' => __('campaign_conversions.conversion_types.trial'),
                        'custom' => __('campaign_conversions.conversion_types.custom'),
                    ])
                    ->native(false),
                SelectFilter::make('status')
                    ->label(__('campaign_conversions.filters.status'))
                    ->options([
                        'pending' => __('campaign_conversions.statuses.pending'),
                        'completed' => __('campaign_conversions.statuses.completed'),
                        'cancelled' => __('campaign_conversions.statuses.cancelled'),
                        'refunded' => __('campaign_conversions.statuses.refunded'),
                    ])
                    ->native(false),
                SelectFilter::make('device_type')
                    ->label(__('campaign_conversions.filters.device_type'))
                    ->options([
                        'mobile' => __('campaign_conversions.device_types.mobile'),
                        'tablet' => __('campaign_conversions.device_types.tablet'),
                        'desktop' => __('campaign_conversions.device_types.desktop'),
                    ])
                    ->native(false),
                Filter::make('high_value')
                    ->label(__('campaign_conversions.filters.high_value'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('conversion_value', '>=', 250)),
                Filter::make('recent')
                    ->label(__('campaign_conversions.filters.recent'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('converted_at', '>=', now()->subDays(7))),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('calculate_roi')
                    ->label(__('campaign_conversions.actions.calculate_roi'))
                    ->icon('heroicon-o-calculator')
                    ->form([
                        TextInput::make('cost')
                            ->label(__('campaign_conversions.form.cost'))
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ])
                    ->action(function (CampaignConversion $record, array $data): void {
                        $cost = (float) $data['cost'];
                        if ($cost <= 0.0) {
                            Notification::make()
                                ->title(__('campaign_conversions.actions.calculate_roi'))
                                ->body(__('validation.gt.numeric', [
                                    'attribute' => __('campaign_conversions.form.cost'),
                                    'value' => 0,
                                ]))
                                ->danger()
                                ->send();

                            return;
                        }

                        $roi = ($record->conversion_value - $cost) / $cost;
                        $record->update(['roi' => $roi]);

                        Notification::make()
                            ->title(__('campaign_conversions.actions.calculate_roi'))
                            ->body(__('campaign_conversions.table.roi').': '.number_format($roi * 100, 2).' %')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_completed')
                        ->label(__('campaign_conversions.actions.mark_completed'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'completed']);
                        })
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('export')
                    ->label(__('campaign_conversions.actions.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(fn (): StreamedResponse => response()->streamDownload(function (): void {
                        $handle = fopen('php://output', 'wb');
                        fputcsv($handle, ['id', 'campaign_id', 'customer_id', 'conversion_type', 'conversion_value', 'status', 'converted_at']);

                        CampaignConversion::query()
                            ->orderBy('converted_at', 'desc')
                            ->lazy()
                            ->each(function (CampaignConversion $conversion) use ($handle): void {
                                fputcsv($handle, [
                                    $conversion->getKey(),
                                    $conversion->campaign_id,
                                    $conversion->customer_id,
                                    $conversion->conversion_type,
                                    $conversion->conversion_value,
                                    $conversion->status,
                                    optional($conversion->converted_at)->toDateTimeString(),
                                ]);
                            });

                        fclose($handle);
                    }, 'campaign-conversions.csv', ['Content-Type' => 'text/csv; charset=UTF-8'])),
            ])
            ->defaultSort('converted_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['campaign', 'customer']))
            ->tabs([
                Tab::make('all')
                    ->label(__('campaign_conversions.tabs.all')),
                Tab::make('completed')
                    ->label(__('campaign_conversions.tabs.completed'))
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'completed')),
                Tab::make('pending')
                    ->label(__('campaign_conversions.tabs.pending'))
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'pending')),
                Tab::make('high_value')
                    ->label(__('campaign_conversions.tabs.high_value'))
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('conversion_value', '>=', 250)),
                Tab::make('recent')
                    ->label(__('campaign_conversions.tabs.recent'))
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('converted_at', '>=', now()->subDays(7))),
                Tab::make('mobile')
                    ->label(__('campaign_conversions.tabs.mobile'))
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('device_type', 'mobile')),
                Tab::make('desktop')
                    ->label(__('campaign_conversions.tabs.desktop'))
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('device_type', 'desktop')),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            InfolistSection::make(__('campaign_conversions.infolist.basic_information'))
                ->schema([
                    InfolistGrid::make(2)
                        ->schema([
                            TextEntry::make('id')
                                ->label(__('campaign_conversions.infolist.id')),
                            TextEntry::make('campaign.name')
                                ->label(__('campaign_conversions.infolist.campaign')),
                            TextEntry::make('conversion_type')
                                ->label(__('campaign_conversions.infolist.conversion_type'))
                                ->formatStateUsing(fn (?string $state): string => $state ? __('campaign_conversions.conversion_types.'.$state) : '-'),
                            TextEntry::make('conversion_value')
                                ->label(__('campaign_conversions.infolist.conversion_value'))
                                ->formatStateUsing(fn ($state): string => '€'.number_format((float) $state, 2)),
                            TextEntry::make('status')
                                ->label(__('campaign_conversions.infolist.status'))
                                ->formatStateUsing(fn (?string $state): string => $state ? __('campaign_conversions.statuses.'.$state) : '-'),
                            TextEntry::make('converted_at')
                                ->label(__('campaign_conversions.infolist.converted_at'))
                                ->dateTime(),
                        ]),
                ]),
            InfolistSection::make(__('campaign_conversions.infolist.customer_information'))
                ->schema([
                    InfolistGrid::make(2)
                        ->schema([
                            TextEntry::make('customer.name')
                                ->label(__('campaign_conversions.infolist.customer_name')),
                            TextEntry::make('customer.email')
                                ->label(__('campaign_conversions.infolist.customer_email')),
                            TextEntry::make('order_id')
                                ->label(__('campaign_conversions.infolist.order_id')),
                            TextEntry::make('session_id')
                                ->label(__('campaign_conversions.infolist.session_id')),
                        ]),
                ]),
            InfolistSection::make(__('campaign_conversions.infolist.tracking_information'))
                ->schema([
                    InfolistGrid::make(2)
                        ->schema([
                            TextEntry::make('source')
                                ->label(__('campaign_conversions.infolist.source')),
                            TextEntry::make('medium')
                                ->label(__('campaign_conversions.infolist.medium')),
                            TextEntry::make('campaign_name')
                                ->label(__('campaign_conversions.infolist.campaign_name')),
                            TextEntry::make('utm_content')
                                ->label(__('campaign_conversions.infolist.utm_content')),
                            TextEntry::make('utm_term')
                                ->label(__('campaign_conversions.infolist.utm_term')),
                            TextEntry::make('referrer')
                                ->label(__('campaign_conversions.infolist.referrer')),
                        ]),
                ]),
            InfolistSection::make(__('campaign_conversions.infolist.device_information'))
                ->schema([
                    InfolistGrid::make(2)
                        ->schema([
                            TextEntry::make('device_type')
                                ->label(__('campaign_conversions.infolist.device_type'))
                                ->formatStateUsing(fn (?string $state): string => $state ? __('campaign_conversions.device_types.'.$state) : '-'),
                            TextEntry::make('browser')
                                ->label(__('campaign_conversions.infolist.browser')),
                            TextEntry::make('os')
                                ->label(__('campaign_conversions.infolist.os')),
                            TextEntry::make('country')
                                ->label(__('campaign_conversions.infolist.country')),
                            TextEntry::make('city')
                                ->label(__('campaign_conversions.infolist.city')),
                            TextEntry::make('ip_address')
                                ->label(__('campaign_conversions.infolist.ip_address')),
                        ]),
                ]),
            InfolistSection::make(__('campaign_conversions.infolist.performance_metrics'))
                ->schema([
                    InfolistGrid::make(2)
                        ->schema([
                            TextEntry::make('roi')
                                ->label(__('campaign_conversions.infolist.roi'))
                                ->formatStateUsing(fn ($state): string => $state === null ? '-' : number_format((float) $state * 100, 2).' %'),
                            TextEntry::make('roas')
                                ->label(__('campaign_conversions.infolist.roas')),
                            TextEntry::make('cost_per_conversion')
                                ->label(__('campaign_conversions.infolist.cost_per_conversion'))
                                ->formatStateUsing(fn ($state): string => $state === null ? '-' : '€'.number_format((float) $state, 2)),
                            TextEntry::make('lifetime_value')
                                ->label(__('campaign_conversions.infolist.lifetime_value'))
                                ->formatStateUsing(fn ($state): string => $state === null ? '-' : '€'.number_format((float) $state, 2)),
                            TextEntry::make('customer_acquisition_cost')
                                ->label(__('campaign_conversions.infolist.customer_acquisition_cost'))
                                ->formatStateUsing(fn ($state): string => $state === null ? '-' : '€'.number_format((float) $state, 2)),
                            TextEntry::make('conversion_rate')
                                ->label(__('campaign_conversions.infolist.conversion_rate'))
                                ->formatStateUsing(fn ($state): string => $state === null ? '-' : number_format((float) $state * 100, 2).' %'),
                        ]),
                ]),
            InfolistSection::make(__('campaign_conversions.infolist.additional_information'))
                ->schema([
                    TextEntry::make('notes')
                        ->label(__('campaign_conversions.infolist.notes'))
                        ->columnSpanFull()
                        ->placeholder('-'),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCampaignConversions::route('/'),
            'create' => Pages\CreateCampaignConversion::route('/create'),
            'view' => Pages\ViewCampaignConversion::route('/{record}'),
            'edit' => Pages\EditCampaignConversion::route('/{record}/edit'),
        ];
    }
}
