<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use App\Models\User;
use App\Enums\NavigationGroup;
use Filament\Forms;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Form;

/**
 * ReportResource
 * 
 * Filament v4 resource for Report management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    
    protected static $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 17;
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('reports.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'System'->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('reports.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('reports.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('reports.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('reports.name'))
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('code')
                                ->label(__('reports.code'))
                                ->required()
                                ->maxLength(50)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    
                    Textarea::make('description')
                        ->label(__('reports.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('reports.report_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('reports.type'))
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
                                ->default('sales'),
                            
                            Select::make('format')
                                ->label(__('reports.format'))
                                ->options([
                                    'pdf' => __('reports.formats.pdf'),
                                    'excel' => __('reports.formats.excel'),
                                    'csv' => __('reports.formats.csv'),
                                    'json' => __('reports.formats.json'),
                                    'html' => __('reports.formats.html'),
                                ])
                                ->required()
                                ->default('pdf'),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            Select::make('frequency')
                                ->label(__('reports.frequency'))
                                ->options([
                                    'once' => __('reports.frequencies.once'),
                                    'daily' => __('reports.frequencies.daily'),
                                    'weekly' => __('reports.frequencies.weekly'),
                                    'monthly' => __('reports.frequencies.monthly'),
                                    'quarterly' => __('reports.frequencies.quarterly'),
                                    'yearly' => __('reports.frequencies.yearly'),
                                ])
                                ->required()
                                ->default('once'),
                            
                            TextInput::make('max_rows')
                                ->label(__('reports.max_rows'))
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100000)
                                ->default(1000)
                                ->helperText(__('reports.max_rows_help')),
                        ]),
                ]),
            
            Section::make(__('reports.scheduling'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('scheduled_at')
                                ->label(__('reports.scheduled_at'))
                                ->helperText(__('reports.scheduled_at_help')),
                            
                            Select::make('timezone')
                                ->label(__('reports.timezone'))
                                ->options([
                                    'UTC' => 'UTC',
                                    'Europe/Vilnius' => 'Europe/Vilnius',
                                    'Europe/London' => 'Europe/London',
                                    'America/New_York' => 'America/New_York',
                                    'Asia/Tokyo' => 'Asia/Tokyo',
                                ])
                                ->default('Europe/Vilnius')
                                ->searchable(),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_scheduled')
                                ->label(__('reports.is_scheduled'))
                                ->default(false)
                                ->helperText(__('reports.is_scheduled_help')),
                            
                            Toggle::make('auto_generate')
                                ->label(__('reports.auto_generate'))
                                ->default(false)
                                ->helperText(__('reports.auto_generate_help')),
                        ]),
                ]),
            
            Section::make(__('reports.recipients'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('recipients')
                                ->label(__('reports.recipients'))
                                ->relationship('users', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('email')
                                        ->email()
                                        ->required()
                                        ->maxLength(255),
                                ]),
                            
                            TextInput::make('email_recipients')
                                ->label(__('reports.email_recipients'))
                                ->email()
                                ->multiple()
                                ->helperText(__('reports.email_recipients_help')),
                        ]),
                ]),
            
            Section::make(__('reports.parameters'))
                ->schema([
                    Forms\Components\Repeater::make('parameters')
                        ->label(__('reports.parameters'))
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('name')
                                        ->label(__('reports.parameter_name'))
                                        ->required()
                                        ->maxLength(100),
                                    
                                    Select::make('type')
                                        ->label(__('reports.parameter_type'))
                                        ->options([
                                            'string' => __('reports.parameter_types.string'),
                                            'integer' => __('reports.parameter_types.integer'),
                                            'float' => __('reports.parameter_types.float'),
                                            'boolean' => __('reports.parameter_types.boolean'),
                                            'date' => __('reports.parameter_types.date'),
                                            'datetime' => __('reports.parameter_types.datetime'),
                                        ])
                                        ->required()
                                        ->default('string'),
                                    
                                    TextInput::make('default_value')
                                        ->label(__('reports.parameter_default_value'))
                                        ->maxLength(255),
                                ]),
                            
                            Textarea::make('description')
                                ->label(__('reports.parameter_description'))
                                ->rows(2)
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                        ->addActionLabel(__('reports.add_parameter'))
                        ->helperText(__('reports.parameters_help')),
                ]),
            
            Section::make(__('reports.output'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            FileUpload::make('template_file')
                                ->label(__('reports.template_file'))
                                ->acceptedFileTypes(['pdf', 'xlsx', 'xls', 'csv', 'html'])
                                ->maxSize(10240)
                                ->helperText(__('reports.template_file_help')),
                            
                            TextInput::make('output_path')
                                ->label(__('reports.output_path'))
                                ->maxLength(500)
                                ->helperText(__('reports.output_path_help')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('reports.is_active'))
                                ->default(true),
                            
                            TextInput::make('sort_order')
                                ->label(__('reports.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                ]),
            
            Section::make(__('reports.settings'))
                ->schema([
                    Textarea::make('notes')
                        ->label(__('reports.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
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
                TextColumn::make('name')
                    ->label(__('reports.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('code')
                    ->label(__('reports.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('type')
                    ->label(__('reports.type'))
                    ->formatStateUsing(fn (string $state): string => __("reports.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sales' => 'blue',
                        'inventory' => 'green',
                        'customer' => 'purple',
                        'product' => 'orange',
                        'financial' => 'red',
                        'analytics' => 'indigo',
                        'custom' => 'gray',
                        default => 'gray',
                    }),
                
                TextColumn::make('format')
                    ->label(__('reports.format'))
                    ->formatStateUsing(fn (string $state): string => __("reports.formats.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pdf' => 'red',
                        'excel' => 'green',
                        'csv' => 'blue',
                        'json' => 'purple',
                        'html' => 'orange',
                        default => 'gray',
                    }),
                
                TextColumn::make('frequency')
                    ->label(__('reports.frequency'))
                    ->formatStateUsing(fn (string $state): string => __("reports.frequencies.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'once' => 'gray',
                        'daily' => 'blue',
                        'weekly' => 'green',
                        'monthly' => 'purple',
                        'quarterly' => 'orange',
                        'yearly' => 'red',
                        default => 'gray',
                    }),
                
                TextColumn::make('max_rows')
                    ->label(__('reports.max_rows'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('scheduled_at')
                    ->label(__('reports.scheduled_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('timezone')
                    ->label(__('reports.timezone'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_scheduled')
                    ->label(__('reports.is_scheduled'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('auto_generate')
                    ->label(__('reports.auto_generate'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('users_count')
                    ->label(__('reports.recipients_count'))
                    ->counts('users')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_active')
                    ->label(__('reports.is_active'))
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('sort_order')
                    ->label(__('reports.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('reports.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('reports.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('reports.type'))
                    ->options([
                        'sales' => __('reports.types.sales'),
                        'inventory' => __('reports.types.inventory'),
                        'customer' => __('reports.types.customer'),
                        'product' => __('reports.types.product'),
                        'financial' => __('reports.types.financial'),
                        'analytics' => __('reports.types.analytics'),
                        'custom' => __('reports.types.custom'),
                    ]),
                
                SelectFilter::make('format')
                    ->label(__('reports.format'))
                    ->options([
                        'pdf' => __('reports.formats.pdf'),
                        'excel' => __('reports.formats.excel'),
                        'csv' => __('reports.formats.csv'),
                        'json' => __('reports.formats.json'),
                        'html' => __('reports.formats.html'),
                    ]),
                
                SelectFilter::make('frequency')
                    ->label(__('reports.frequency'))
                    ->options([
                        'once' => __('reports.frequencies.once'),
                        'daily' => __('reports.frequencies.daily'),
                        'weekly' => __('reports.frequencies.weekly'),
                        'monthly' => __('reports.frequencies.monthly'),
                        'quarterly' => __('reports.frequencies.quarterly'),
                        'yearly' => __('reports.frequencies.yearly'),
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label(__('reports.is_active'))
                    ->boolean()
                    ->trueLabel(__('reports.active_only'))
                    ->falseLabel(__('reports.inactive_only'))
                    ->native(false),
                
                TernaryFilter::make('is_scheduled')
                    ->label(__('reports.is_scheduled'))
                    ->boolean()
                    ->trueLabel(__('reports.scheduled_only'))
                    ->falseLabel(__('reports.manual_only'))
                    ->native(false),
                
                TernaryFilter::make('auto_generate')
                    ->label(__('reports.auto_generate'))
                    ->boolean()
                    ->trueLabel(__('reports.auto_generate_only'))
                    ->falseLabel(__('reports.manual_generate_only'))
                    ->native(false),
            ])
            ->actions([
                // Actions will be added later
            ])
            ->bulkActions([
                // Bulk actions will be added later
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
