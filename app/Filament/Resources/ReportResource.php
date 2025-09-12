<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Filament\Resources\ReportResource\Widgets;
use App\Models\Report;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;
use BackedEnum;

final class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.reports');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.report');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.reports');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Report Details')
                    ->tabs([
                        Tabs\Tab::make(__('admin.sections.basic_information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label(__('admin.reports.fields.name'))
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                                        if ($operation !== 'create') {
                                                            return;
                                                        }
                                                        $set('slug', Str::slug($state));
                                                    }),

                                                TextInput::make('slug')
                                                    ->label(__('admin.reports.fields.slug'))
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->unique(Report::class, 'slug', ignoreRecord: true)
                                                    ->rules(['alpha_dash']),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                Select::make('type')
                                                    ->label(__('admin.reports.fields.type'))
                                                    ->options([
                                                        'sales' => __('admin.reports.types.sales'),
                                                        'products' => __('admin.reports.types.products'),
                                                        'customers' => __('admin.reports.types.customers'),
                                                        'inventory' => __('admin.reports.types.inventory'),
                                                        'analytics' => __('admin.reports.types.analytics'),
                                                        'financial' => __('admin.reports.types.financial'),
                                                        'marketing' => __('admin.reports.types.marketing'),
                                                        'custom' => __('admin.reports.types.custom'),
                                                    ])
                                                    ->required()
                                                    ->searchable(),

                                                Select::make('category')
                                                    ->label(__('admin.reports.fields.category'))
                                                    ->options([
                                                        'sales' => __('admin.reports.categories.sales'),
                                                        'marketing' => __('admin.reports.categories.marketing'),
                                                        'operations' => __('admin.reports.categories.operations'),
                                                        'finance' => __('admin.reports.categories.finance'),
                                                        'customer_service' => __('admin.reports.categories.customer_service'),
                                                        'inventory' => __('admin.reports.categories.inventory'),
                                                        'analytics' => __('admin.reports.categories.analytics'),
                                                    ])
                                                    ->required()
                                                    ->searchable(),
                                            ]),

                                        Textarea::make('description')
                                            ->label(__('admin.reports.fields.description'))
                                            ->maxLength(1000)
                                            ->rows(3),

                                        RichEditor::make('content')
                                            ->label(__('admin.reports.fields.content'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tabs\Tab::make(__('admin.reports.tabs.date_settings'))
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('date_range')
                                                    ->label(__('admin.reports.fields.date_range'))
                                                    ->options([
                                                        'today' => __('admin.reports.date_ranges.today'),
                                                        'yesterday' => __('admin.reports.date_ranges.yesterday'),
                                                        'last_7_days' => __('admin.reports.date_ranges.last_7_days'),
                                                        'last_30_days' => __('admin.reports.date_ranges.last_30_days'),
                                                        'last_90_days' => __('admin.reports.date_ranges.last_90_days'),
                                                        'this_year' => __('admin.reports.date_ranges.this_year'),
                                                        'custom' => __('admin.reports.date_ranges.custom'),
                                                    ])
                                                    ->live()
                                                    ->searchable(),

                                                Toggle::make('is_scheduled')
                                                    ->label(__('admin.reports.fields.is_scheduled'))
                                                    ->live(),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                DatePicker::make('start_date')
                                                    ->label(__('admin.reports.fields.start_date'))
                                                    ->visible(fn (Forms\Get $get) => $get('date_range') === 'custom'),

                                                DatePicker::make('end_date')
                                                    ->label(__('admin.reports.fields.end_date'))
                                                    ->visible(fn (Forms\Get $get) => $get('date_range') === 'custom'),
                                            ]),

                                        Select::make('schedule_frequency')
                                            ->label(__('admin.reports.fields.schedule_frequency'))
                                            ->options([
                                                'daily' => __('admin.reports.schedule_frequencies.daily'),
                                                'weekly' => __('admin.reports.schedule_frequencies.weekly'),
                                                'monthly' => __('admin.reports.schedule_frequencies.monthly'),
                                                'quarterly' => __('admin.reports.schedule_frequencies.quarterly'),
                                                'yearly' => __('admin.reports.schedule_frequencies.yearly'),
                                            ])
                                            ->visible(fn (Forms\Get $get) => $get('is_scheduled'))
                                            ->searchable(),
                                    ]),
                            ]),

                        Tabs\Tab::make(__('admin.reports.tabs.filters'))
                            ->icon('heroicon-o-funnel')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        KeyValue::make('filters')
                                            ->label(__('admin.reports.fields.filters'))
                                            ->keyLabel(__('admin.reports.fields.filter_key'))
                                            ->valueLabel(__('admin.reports.fields.filter_value'))
                                            ->addActionLabel(__('admin.reports.actions.add_filter')),

                                        Repeater::make('advanced_filters')
                                            ->label(__('admin.reports.fields.advanced_filters'))
                                            ->schema([
                                                Select::make('field')
                                                    ->label(__('admin.reports.fields.filter_field'))
                                                    ->options([
                                                        'status' => __('admin.reports.filter_fields.status'),
                                                        'category' => __('admin.reports.filter_fields.category'),
                                                        'brand' => __('admin.reports.filter_fields.brand'),
                                                        'price_range' => __('admin.reports.filter_fields.price_range'),
                                                        'date_range' => __('admin.reports.filter_fields.date_range'),
                                                    ])
                                                    ->required(),

                                                Select::make('operator')
                                                    ->label(__('admin.reports.fields.filter_operator'))
                                                    ->options([
                                                        'equals' => __('admin.reports.filter_operators.equals'),
                                                        'not_equals' => __('admin.reports.filter_operators.not_equals'),
                                                        'contains' => __('admin.reports.filter_operators.contains'),
                                                        'not_contains' => __('admin.reports.filter_operators.not_contains'),
                                                        'greater_than' => __('admin.reports.filter_operators.greater_than'),
                                                        'less_than' => __('admin.reports.filter_operators.less_than'),
                                                        'between' => __('admin.reports.filter_operators.between'),
                                                    ])
                                                    ->required(),

                                                TextInput::make('value')
                                                    ->label(__('admin.reports.fields.filter_value'))
                                                    ->required(),
                                            ])
                                            ->columns(3)
                                            ->addActionLabel(__('admin.reports.actions.add_advanced_filter')),
                                    ]),
                            ]),

                        Tabs\Tab::make(__('admin.reports.tabs.settings'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('is_active')
                                                    ->label(__('admin.reports.fields.is_active'))
                                                    ->default(true),

                                                Toggle::make('is_public')
                                                    ->label(__('admin.reports.fields.is_public'))
                                                    ->default(false),
                                            ]),

                                        KeyValue::make('settings')
                                            ->label(__('admin.reports.fields.settings'))
                                            ->keyLabel(__('admin.reports.fields.setting_key'))
                                            ->valueLabel(__('admin.reports.fields.setting_value'))
                                            ->addActionLabel(__('admin.reports.actions.add_setting')),

                                        KeyValue::make('metadata')
                                            ->label(__('admin.reports.fields.metadata'))
                                            ->keyLabel(__('admin.reports.fields.metadata_key'))
                                            ->valueLabel(__('admin.reports.fields.metadata_value'))
                                            ->addActionLabel(__('admin.reports.actions.add_metadata')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.reports.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color(Color::Primary),

                BadgeColumn::make('type')
                    ->label(__('admin.reports.fields.type'))
                    ->colors([
                        'primary' => 'sales',
                        'success' => 'products',
                        'warning' => 'customers',
                        'danger' => 'inventory',
                        'info' => 'analytics',
                        'secondary' => 'financial',
                        'gray' => 'marketing',
                        'slate' => 'custom',
                    ])
                    ->formatStateUsing(fn (string $state): string => __("admin.reports.types.{$state}")),

                BadgeColumn::make('category')
                    ->label(__('admin.reports.fields.category'))
                    ->colors([
                        'primary' => 'sales',
                        'success' => 'marketing',
                        'warning' => 'operations',
                        'danger' => 'finance',
                        'info' => 'customer_service',
                        'secondary' => 'inventory',
                        'gray' => 'analytics',
                    ])
                    ->formatStateUsing(fn (string $state): string => __("admin.reports.categories.{$state}")),

                TextColumn::make('date_range')
                    ->label(__('admin.reports.fields.date_range'))
                    ->formatStateUsing(fn (?string $state): string => $state ? __("admin.reports.date_ranges.{$state}") : '-')
                    ->toggleable(),

                TextColumn::make('view_count')
                    ->label(__('admin.reports.fields.view_count'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('download_count')
                    ->label(__('admin.reports.fields.download_count'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('admin.reports.fields.is_active'))
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_public')
                    ->label(__('admin.reports.fields.is_public'))
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_scheduled')
                    ->label(__('admin.reports.fields.is_scheduled'))
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('last_generated_at')
                    ->label(__('admin.reports.fields.last_generated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('generator.name')
                    ->label(__('admin.reports.fields.generated_by'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('admin.reports.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('admin.reports.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.reports.fields.type'))
                    ->options([
                        'sales' => __('admin.reports.types.sales'),
                        'products' => __('admin.reports.types.products'),
                        'customers' => __('admin.reports.types.customers'),
                        'inventory' => __('admin.reports.types.inventory'),
                        'analytics' => __('admin.reports.types.analytics'),
                        'financial' => __('admin.reports.types.financial'),
                        'marketing' => __('admin.reports.types.marketing'),
                        'custom' => __('admin.reports.types.custom'),
                    ])
                    ->multiple(),

                SelectFilter::make('category')
                    ->label(__('admin.reports.fields.category'))
                    ->options([
                        'sales' => __('admin.reports.categories.sales'),
                        'marketing' => __('admin.reports.categories.marketing'),
                        'operations' => __('admin.reports.categories.operations'),
                        'finance' => __('admin.reports.categories.finance'),
                        'customer_service' => __('admin.reports.categories.customer_service'),
                        'inventory' => __('admin.reports.categories.inventory'),
                        'analytics' => __('admin.reports.categories.analytics'),
                    ])
                    ->multiple(),

                TernaryFilter::make('is_active')
                    ->label(__('admin.reports.fields.is_active')),

                TernaryFilter::make('is_public')
                    ->label(__('admin.reports.fields.is_public')),

                TernaryFilter::make('is_scheduled')
                    ->label(__('admin.reports.fields.is_scheduled')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Action::make('generate')
                    ->label(__('admin.reports.actions.generate'))
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (Report $record) {
                        // Generate report logic here
                        $record->update([
                            'last_generated_at' => now(),
                            'generated_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title(__('admin.reports.notifications.generated'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Report $record): bool => $record->is_active),

                Action::make('view')
                    ->label(__('admin.reports.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Report $record): string => route('reports.show', $record))
                    ->openUrlInNewTab(),

                Action::make('download')
                    ->label(__('admin.reports.actions.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function (Report $record) {
                        $record->incrementDownloadCount();
                        
                        Notification::make()
                            ->title(__('admin.reports.notifications.downloaded'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Report $record): bool => $record->isGenerated()),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),

                    BulkAction::make('activate')
                        ->label(__('admin.reports.actions.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                            
                            Notification::make()
                                ->title(__('admin.reports.notifications.activated'))
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('deactivate')
                        ->label(__('admin.reports.actions.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                            
                            Notification::make()
                                ->title(__('admin.reports.notifications.deactivated'))
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('generate')
                        ->label(__('admin.reports.actions.generate_selected'))
                        ->icon('heroicon-o-play')
                        ->color('primary')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'last_generated_at' => now(),
                                    'generated_by' => auth()->id(),
                                ]);
                            });
                            
                            Notification::make()
                                ->title(__('admin.reports.notifications.generated_selected'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\ReportStatsWidget::class,
            Widgets\ReportTypesWidget::class,
            Widgets\RecentReportsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
