<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

final class ReportResource extends Resource
{
    protected static ?string $model = Report::class;


    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.analytics');
    }

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
            ->components([
                Section::make(__('admin.reports.sales_report'))
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.form_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label(__('admin.fields.report_type'))
                            ->options([
                                'sales' => __('admin.reports.sales_report'),
                                'products' => __('admin.reports.product_performance'),
                                'customers' => __('admin.reports.customer_analysis'),
                                'inventory' => __('admin.reports.inventory_report'),
                            ])
                            ->required(),
                        Forms\Components\Select::make('date_range')
                            ->label(__('admin.fields.date_range'))
                            ->options([
                                'today' => __('admin.date_ranges.today'),
                                'yesterday' => __('admin.date_ranges.yesterday'),
                                'last_7_days' => __('admin.date_ranges.last_7_days'),
                                'last_30_days' => __('admin.date_ranges.last_30_days'),
                                'last_90_days' => __('admin.date_ranges.last_90_days'),
                                'this_year' => __('admin.date_ranges.this_year'),
                                'custom' => __('admin.date_ranges.custom'),
                            ])
                            ->native(false),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->label(__('admin.fields.start_date')),
                                Forms\Components\DatePicker::make('end_date')
                                    ->label(__('admin.fields.end_date')),
                            ]),
                        Forms\Components\KeyValue::make('filters')
                            ->label(__('admin.filament.table.filters'))
                            ->nullable(),
                        Forms\Components\Textarea::make('description')
                            ->label(__('admin.form_description'))
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('admin.products.status.active'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.form_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('admin.fields.report_type'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_range')
                    ->label(__('admin.fields.date_range'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.products.status.active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('admin.fields.report_type'))
                    ->options([
                        'sales' => __('admin.reports.sales_report'),
                        'products' => __('admin.reports.product_performance'),
                        'customers' => __('admin.reports.customer_analysis'),
                        'inventory' => __('admin.reports.inventory_report'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.products.status.active')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
