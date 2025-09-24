<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

final class ReportResource extends Resource
{
    /**
     * @var UnitEnum|string|null
     */
    public static function getNavigationGroup(): \UnitEnum|string|null
    {
        return NavigationGroup::Reports;
    }

    protected static ?string $model = Report::class;

    /**
     * @var string|\BackedEnum|null
     */
    public static function getNavigationIcon(): \BackedEnum|\Illuminate\Contracts\Support\Htmlable|string|null
    {
        return 'heroicon-o-document-chart-bar';
    }

    protected static ?int $navigationSort = 17;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('reports.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('reports.plural');
    }

    public static function getModelLabel(): string
    {
        return __('reports.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                Section::make(__('reports.sections.basic_info'))
                    ->description(__('reports.sections.basic_info_description'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('reports.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Str::slug($state))),
                        TextInput::make('slug')
                            ->label(__('reports.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Report::class, 'slug', ignoreRecord: true),
                        Select::make('type')
                            ->label(__('reports.fields.type'))
                            ->options([
                                'sales' => __('reports.types.sales'),
                                'inventory' => __('reports.types.inventory'),
                                'customer' => __('reports.types.customer'),
                                'product' => __('reports.types.product'),
                                'financial' => __('reports.types.financial'),
                                'analytics' => __('reports.types.analytics'),
                                'custom' => __('reports.types.custom'),
                            ])
                            ->required()
                            ->reactive(),
                        Select::make('category')
                            ->label(__('reports.fields.category'))
                            ->options([
                                'sales' => __('reports.categories.sales'),
                                'marketing' => __('reports.categories.marketing'),
                                'operations' => __('reports.categories.operations'),
                                'finance' => __('reports.categories.finance'),
                                'customer_service' => __('reports.categories.customer_service'),
                                'inventory' => __('reports.categories.inventory'),
                                'analytics' => __('reports.categories.analytics'),
                            ])
                            ->required(),
                    ]),
                Section::make(__('reports.sections.content'))
                    ->description(__('reports.sections.content_description'))
                    ->columns(1)
                    ->schema([
                        Textarea::make('description')
                            ->label(__('reports.fields.description'))
                            ->maxLength(65535)
                            ->nullable()
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('content')
                            ->label(__('reports.fields.content'))
                            ->maxLength(65535)
                            ->nullable()
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('reports.sections.settings'))
                    ->description(__('reports.sections.settings_description'))
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('reports.fields.is_active'))
                            ->inline(false)
                            ->default(true),
                        Toggle::make('is_public')
                            ->label(__('reports.fields.is_public'))
                            ->inline(false)
                            ->default(false),
                        Toggle::make('is_scheduled')
                            ->label(__('reports.fields.is_scheduled'))
                            ->inline(false)
                            ->default(false)
                            ->reactive(),
                        Select::make('schedule_frequency')
                            ->label(__('reports.fields.schedule_frequency'))
                            ->options([
                                'daily' => __('reports.frequencies.daily'),
                                'weekly' => __('reports.frequencies.weekly'),
                                'monthly' => __('reports.frequencies.monthly'),
                                'quarterly' => __('reports.frequencies.quarterly'),
                                'yearly' => __('reports.frequencies.yearly'),
                            ])
                            ->visible(fn ($get) => $get('is_scheduled')),
                    ]),
                Section::make(__('reports.sections.date_range'))
                    ->description(__('reports.sections.date_range_description'))
                    ->columns(2)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label(__('reports.fields.start_date'))
                            ->nullable(),
                        DatePicker::make('end_date')
                            ->label(__('reports.fields.end_date'))
                            ->nullable()
                            ->after('start_date'),
                        DateTimePicker::make('last_generated_at')
                            ->label(__('reports.fields.last_generated_at'))
                            ->nullable()
                            ->disabled(),
                    ]),
                Section::make(__('reports.sections.advanced'))
                    ->description(__('reports.sections.advanced_description'))
                    ->collapsible()
                    ->schema([
                        KeyValue::make('filters')
                            ->label(__('reports.fields.filters'))
                            ->keyLabel(__('reports.fields.filter_key'))
                            ->valueLabel(__('reports.fields.filter_value'))
                            ->reorderable()
                            ->addActionLabel(__('reports.actions.add_filter'))
                            ->columnSpanFull(),
                        KeyValue::make('settings')
                            ->label(__('reports.fields.settings'))
                            ->keyLabel(__('reports.fields.setting_key'))
                            ->valueLabel(__('reports.fields.setting_value'))
                            ->reorderable()
                            ->addActionLabel(__('reports.actions.add_setting'))
                            ->columnSpanFull(),
                        KeyValue::make('metadata')
                            ->label(__('reports.fields.metadata'))
                            ->keyLabel(__('reports.fields.metadata_key'))
                            ->valueLabel(__('reports.fields.metadata_value'))
                            ->reorderable()
                            ->addActionLabel(__('reports.actions.add_metadata'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('reports.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(50),
                BadgeColumn::make('type')
                    ->label(__('reports.fields.type'))
                    ->searchable()
                    ->sortable()
                    ->colors([
                        'sales' => 'success',
                        'inventory' => 'info',
                        'customer' => 'warning',
                        'product' => 'primary',
                        'financial' => 'danger',
                        'analytics' => 'secondary',
                        'custom' => 'gray',
                    ])
                    ->formatStateUsing(fn (string $state): string => __("reports.types.{$state}")),
                BadgeColumn::make('category')
                    ->label(__('reports.fields.category'))
                    ->searchable()
                    ->sortable()
                    ->colors([
                        'sales' => 'success',
                        'marketing' => 'info',
                        'operations' => 'warning',
                        'finance' => 'danger',
                        'customer_service' => 'primary',
                        'inventory' => 'secondary',
                        'analytics' => 'gray',
                    ])
                    ->formatStateUsing(fn (string $state): string => __("reports.categories.{$state}")),
                TextColumn::make('generator.name')
                    ->label(__('reports.fields.generated_by'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('view_count')
                    ->label(__('reports.fields.view_count'))
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->badge()
                    ->color('info'),
                TextColumn::make('download_count')
                    ->label(__('reports.fields.download_count'))
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->badge()
                    ->color('success'),
                IconColumn::make('is_active')
                    ->label(__('reports.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_public')
                    ->label(__('reports.fields.is_public'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_scheduled')
                    ->label(__('reports.fields.is_scheduled'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('last_generated_at')
                    ->label(__('reports.fields.last_generated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('reports.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('reports.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('reports.filters.is_active'))
                    ->boolean(),
                TernaryFilter::make('is_public')
                    ->label(__('reports.filters.is_public'))
                    ->boolean(),
                TernaryFilter::make('is_scheduled')
                    ->label(__('reports.filters.is_scheduled'))
                    ->boolean(),
                SelectFilter::make('type')
                    ->label(__('reports.filters.type'))
                    ->options([
                        'sales' => __('reports.types.sales'),
                        'inventory' => __('reports.types.inventory'),
                        'customer' => __('reports.types.customer'),
                        'product' => __('reports.types.product'),
                        'financial' => __('reports.types.financial'),
                        'analytics' => __('reports.types.analytics'),
                        'custom' => __('reports.types.custom'),
                    ]),
                SelectFilter::make('category')
                    ->label(__('reports.filters.category'))
                    ->options([
                        'sales' => __('reports.categories.sales'),
                        'marketing' => __('reports.categories.marketing'),
                        'operations' => __('reports.categories.operations'),
                        'finance' => __('reports.categories.finance'),
                        'customer_service' => __('reports.categories.customer_service'),
                        'inventory' => __('reports.categories.inventory'),
                        'analytics' => __('reports.categories.analytics'),
                    ]),
                SelectFilter::make('generated_by')
                    ->label(__('reports.filters.generated_by'))
                    ->relationship('generator', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('generated_recently')
                    ->label(__('reports.filters.generated_recently'))
                    ->query(fn (Builder $query): Builder => $query->where('last_generated_at', '>=', now()->subDays(7))),
                Filter::make('never_generated')
                    ->label(__('reports.filters.never_generated'))
                    ->query(fn (Builder $query): Builder => $query->whereNull('last_generated_at')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('generate')
                    ->label(__('reports.actions.generate'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Report $record): void {
                        // This would typically generate the report
                        $record->update(['last_generated_at' => now()]);
                        Notification::make()
                            ->title(__('reports.notifications.generated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('download')
                    ->label(__('reports.actions.download'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->visible(fn (Report $record): bool => $record->isGenerated())
                    ->url(fn (Report $record): string => route('reports.download', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('generate_all')
                        ->label(__('reports.actions.generate_all'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['last_generated_at' => now()]);
                            Notification::make()
                                ->title(__('reports.notifications.bulk_generated_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('activate')
                        ->label(__('reports.actions.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('reports.notifications.bulk_activated_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('reports.actions.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('reports.notifications.bulk_deactivated_successfully'))
                                ->warning()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('reports.sections.basic_info'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('reports.fields.name'))
                            ->weight('medium'),
                        TextEntry::make('slug')
                            ->label(__('reports.fields.slug'))
                            ->badge()
                            ->color('info'),
                        TextEntry::make('type')
                            ->label(__('reports.fields.type'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'sales' => 'success',
                                'inventory' => 'info',
                                'customer' => 'warning',
                                'product' => 'primary',
                                'financial' => 'danger',
                                'analytics' => 'secondary',
                                'custom' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => __("reports.types.{$state}")),
                        TextEntry::make('category')
                            ->label(__('reports.fields.category'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'sales' => 'success',
                                'marketing' => 'info',
                                'operations' => 'warning',
                                'finance' => 'danger',
                                'customer_service' => 'primary',
                                'inventory' => 'secondary',
                                'analytics' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => __("reports.categories.{$state}")),
                    ])
                    ->columns(2),
                Section::make(__('reports.sections.content'))
                    ->schema([
                        TextEntry::make('description')
                            ->label(__('reports.fields.description'))
                            ->columnSpanFull()
                            ->placeholder(__('reports.placeholders.no_description')),
                        TextEntry::make('content')
                            ->label(__('reports.fields.content'))
                            ->columnSpanFull()
                            ->placeholder(__('reports.placeholders.no_content')),
                    ]),
                Section::make(__('reports.sections.settings'))
                    ->schema([
                        TextEntry::make('is_active')
                            ->label(__('reports.fields.is_active'))
                            ->boolean(),
                        TextEntry::make('is_public')
                            ->label(__('reports.fields.is_public'))
                            ->boolean(),
                        TextEntry::make('is_scheduled')
                            ->label(__('reports.fields.is_scheduled'))
                            ->boolean(),
                        TextEntry::make('schedule_frequency')
                            ->label(__('reports.fields.schedule_frequency'))
                            ->formatStateUsing(fn (string $state): string => __("reports.frequencies.{$state}"))
                            ->placeholder(__('reports.placeholders.no_schedule')),
                    ])
                    ->columns(2),
                Section::make(__('reports.sections.stats'))
                    ->schema([
                        TextEntry::make('view_count')
                            ->label(__('reports.fields.view_count'))
                            ->numeric()
                            ->badge()
                            ->color('info'),
                        TextEntry::make('download_count')
                            ->label(__('reports.fields.download_count'))
                            ->numeric()
                            ->badge()
                            ->color('success'),
                        TextEntry::make('generator.name')
                            ->label(__('reports.fields.generated_by'))
                            ->placeholder(__('reports.placeholders.not_generated')),
                        TextEntry::make('last_generated_at')
                            ->label(__('reports.fields.last_generated_at'))
                            ->dateTime()
                            ->placeholder(__('reports.placeholders.never_generated')),
                    ])
                    ->columns(2),
                Section::make(__('reports.sections.advanced'))
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('filters')
                            ->label(__('reports.fields.filters'))
                            ->schema([
                                TextEntry::make('key')
                                    ->label(__('reports.fields.filter_key')),
                                TextEntry::make('value')
                                    ->label(__('reports.fields.filter_value')),
                            ])
                            ->placeholder(__('reports.placeholders.no_filters')),
                        RepeatableEntry::make('settings')
                            ->label(__('reports.fields.settings'))
                            ->schema([
                                TextEntry::make('key')
                                    ->label(__('reports.fields.setting_key')),
                                TextEntry::make('value')
                                    ->label(__('reports.fields.setting_value')),
                            ])
                            ->placeholder(__('reports.placeholders.no_settings')),
                        RepeatableEntry::make('metadata')
                            ->label(__('reports.fields.metadata'))
                            ->schema([
                                TextEntry::make('key')
                                    ->label(__('reports.fields.metadata_key')),
                                TextEntry::make('value')
                                    ->label(__('reports.fields.metadata_value')),
                            ])
                            ->placeholder(__('reports.placeholders.no_metadata')),
                    ]),
                Section::make(__('reports.sections.timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('reports.fields.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('reports.fields.updated_at'))
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description', 'type', 'category'];
    }

    public static function getNavigationBadge(): ?string
    {
        return self::$model::count();
    }
}
