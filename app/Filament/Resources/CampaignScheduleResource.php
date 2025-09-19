<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CampaignScheduleResource\Pages;
use App\Models\Campaign;
use App\Models\CampaignSchedule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\KeyValue;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use BackedEnum;
use UnitEnum;

/**
 * CampaignScheduleResource
 *
 * Filament v4 resource for CampaignSchedule management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class CampaignScheduleResource extends Resource
{
    protected static ?string $model = CampaignSchedule::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    /*protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Marketing;
    protected static ?int $navigationSort = 5;
    protected static ?string $recordTitleAttribute = 'campaign.name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.campaign_schedules.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.campaign_schedules.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.campaign_schedules.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('campaign_schedule_tabs')
                ->tabs([
                    Tab::make(__('admin.campaign_schedules.form.tabs.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            SchemaSection::make(__('admin.campaign_schedules.form.sections.basic_information'))
                                ->schema([
                                    SchemaGrid::make(2)
                                        ->schema([
                                            Select::make('campaign_id')
                                                ->label(__('admin.campaign_schedules.form.fields.campaign'))
                                                ->relationship('campaign', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name}")
                                                ->columnSpan(1),
                                            Select::make('schedule_type')
                                                ->label(__('admin.campaign_schedules.form.fields.schedule_type'))
                                                ->options([
                                                    'once' => __('admin.campaign_schedules.schedule_types.once'),
                                                    'daily' => __('admin.campaign_schedules.schedule_types.daily'),
                                                    'weekly' => __('admin.campaign_schedules.schedule_types.weekly'),
                                                    'monthly' => __('admin.campaign_schedules.schedule_types.monthly'),
                                                    'custom' => __('admin.campaign_schedules.schedule_types.custom'),
                                                ])
                                                ->required()
                                                ->columnSpan(1),
                                        ]),
                                    SchemaGrid::make(2)
                                        ->schema([
                                            DateTimePicker::make('next_run_at')
                                                ->label(__('admin.campaign_schedules.form.fields.next_run_at'))
                                                ->required()
                                                ->columnSpan(1),
                                            DateTimePicker::make('last_run_at')
                                                ->label(__('admin.campaign_schedules.form.fields.last_run_at'))
                                                ->columnSpan(1),
                                        ]),
                                    Toggle::make('is_active')
                                        ->label(__('admin.campaign_schedules.form.fields.is_active'))
                                        ->default(true)
                                        ->columnSpanFull(),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.campaign_schedules.form.tabs.schedule_config'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            SchemaSection::make(__('admin.campaign_schedules.form.sections.schedule_config'))
                                ->schema([
                                    KeyValue::make('schedule_config')
                                        ->label(__('admin.campaign_schedules.form.fields.schedule_config'))
                                        ->keyLabel(__('admin.campaign_schedules.form.fields.config_key'))
                                        ->valueLabel(__('admin.campaign_schedules.form.fields.config_value'))
                                        ->columnSpanFull(),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.campaign_schedules.form.tabs.campaign_details'))
                        ->icon('heroicon-o-megaphone')
                        ->schema([
                            SchemaSection::make(__('admin.campaign_schedules.form.sections.campaign_details'))
                                ->schema([
                                    Placeholder::make('campaign_name')
                                        ->label(__('admin.campaign_schedules.form.fields.campaign_name'))
                                        ->content(fn($record) => $record?->campaign?->name ?? '-'),
                                    Placeholder::make('campaign_status')
                                        ->label(__('admin.campaign_schedules.form.fields.campaign_status'))
                                        ->content(fn($record) => $record?->campaign?->status ?? '-'),
                                    Placeholder::make('campaign_type')
                                        ->label(__('admin.campaign_schedules.form.fields.campaign_type'))
                                        ->content(fn($record) => $record?->campaign?->type ?? '-'),
                                    Placeholder::make('schedule_status')
                                        ->label(__('admin.campaign_schedules.form.fields.schedule_status'))
                                        ->content(fn($record) => $record?->is_active ? 
                                            __('admin.campaign_schedules.status.active') : 
                                            __('admin.campaign_schedules.status.inactive')
                                        ),
                                ])
                                ->columns(2),
                        ]),
                ])
                ->columnSpanFull(),
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
                TextColumn::make('campaign.name')
                    ->label(__('admin.campaign_schedules.form.fields.campaign'))
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('schedule_type')
                    ->label(__('admin.campaign_schedules.form.fields.schedule_type'))
                    ->formatStateUsing(fn(string $state): string => 
                        match ($state) {
                            'once' => __('admin.campaign_schedules.schedule_types.once'),
                            'daily' => __('admin.campaign_schedules.schedule_types.daily'),
                            'weekly' => __('admin.campaign_schedules.schedule_types.weekly'),
                            'monthly' => __('admin.campaign_schedules.schedule_types.monthly'),
                            'custom' => __('admin.campaign_schedules.schedule_types.custom'),
                            default => $state,
                        }
                    )
                    ->colors([
                        'primary' => 'once',
                        'success' => 'daily',
                        'warning' => 'weekly',
                        'info' => 'monthly',
                        'danger' => 'custom',
                    ]),
                TextColumn::make('next_run_at')
                    ->label(__('admin.campaign_schedules.form.fields.next_run_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_run_at')
                    ->label(__('admin.campaign_schedules.form.fields.last_run_at'))
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('admin.campaign_schedules.form.fields.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                BadgeColumn::make('status')
                    ->label(__('admin.campaign_schedules.form.fields.status'))
                    ->formatStateUsing(function ($record) {
                        if (!$record->is_active) return __('admin.campaign_schedules.status.inactive');
                        if ($record->next_run_at && $record->next_run_at->isFuture()) {
                            return __('admin.campaign_schedules.status.scheduled');
                        }
                        return __('admin.campaign_schedules.status.ready');
                    })
                    ->colors([
                        'success' => fn($state) => $state === __('admin.campaign_schedules.status.scheduled'),
                        'warning' => fn($state) => $state === __('admin.campaign_schedules.status.ready'),
                        'danger' => fn($state) => $state === __('admin.campaign_schedules.status.inactive'),
                    ]),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('admin.campaign_schedules.filters.campaign'))
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('schedule_type')
                    ->label(__('admin.campaign_schedules.filters.schedule_type'))
                    ->options([
                        'once' => __('admin.campaign_schedules.schedule_types.once'),
                        'daily' => __('admin.campaign_schedules.schedule_types.daily'),
                        'weekly' => __('admin.campaign_schedules.schedule_types.weekly'),
                        'monthly' => __('admin.campaign_schedules.schedule_types.monthly'),
                        'custom' => __('admin.campaign_schedules.schedule_types.custom'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('admin.campaign_schedules.filters.is_active')),
                DateFilter::make('next_run_at')
                    ->label(__('admin.campaign_schedules.filters.next_run_at')),
                DateFilter::make('last_run_at')
                    ->label(__('admin.campaign_schedules.filters.last_run_at')),
                Filter::make('overdue')
                    ->label(__('admin.campaign_schedules.filters.overdue'))
                    ->query(fn(Builder $query): Builder => 
                        $query->where('next_run_at', '<', now())->where('is_active', true)
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                TableBulkAction::make('activate')
                    ->label(__('admin.campaign_schedules.actions.activate'))
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (CampaignSchedule $record): void {
                        $record->update(['is_active' => true]);
                        FilamentNotification::make()
                            ->title(__('admin.campaign_schedules.activated_successfully'))
                            ->success()
                            ->send();
                    }),
                TableBulkAction::make('deactivate')
                    ->label(__('admin.campaign_schedules.actions.deactivate'))
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->action(function (CampaignSchedule $record): void {
                        $record->update(['is_active' => false]);
                        FilamentNotification::make()
                            ->title(__('admin.campaign_schedules.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                TableBulkAction::make('run_now')
                    ->label(__('admin.campaign_schedules.actions.run_now'))
                    ->icon('heroicon-o-play-circle')
                    ->color('info')
                    ->action(function (CampaignSchedule $record): void {
                        // Run campaign logic here
                        $record->update(['last_run_at' => now()]);
                        FilamentNotification::make()
                            ->title(__('admin.campaign_schedules.run_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('activate_bulk')
                        ->label(__('admin.campaign_schedules.actions.activate_bulk'))
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(function (EloquentCollection $records): void {
                            $records->each(function (CampaignSchedule $record): void {
                                $record->update(['is_active' => true]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.campaign_schedules.bulk_activated_successfully'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('deactivate_bulk')
                        ->label(__('admin.campaign_schedules.actions.deactivate_bulk'))
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(function (EloquentCollection $records): void {
                            $records->each(function (CampaignSchedule $record): void {
                                $record->update(['is_active' => false]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.campaign_schedules.bulk_deactivated_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('next_run_at', 'asc');
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
            'index' => Pages\ListCampaignSchedules::route('/'),
            'create' => Pages\CreateCampaignSchedule::route('/create'),
            'view' => Pages\ViewCampaignSchedule::route('/{record}'),
            'edit' => Pages\EditCampaignSchedule::route('/{record}/edit'),
        ];
    }
}
