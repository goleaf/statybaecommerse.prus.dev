<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Filament\Resources\ReportResource\Widgets;
use App\Models\Report;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

/**
 * ReportResource
 * 
 * Filament resource for admin panel management.
 */
class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-document-chart-bar';

    // /**
    //  * @var UnitEnum|string|null
    //  */
    // /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Reports;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

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
            ->schema([
                TextInput::make('name')
                    ->label(__('admin.reports.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label(__('admin.reports.fields.slug'))
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label(__('admin.reports.fields.type'))
                    ->options([
                        'sales' => __('admin.reports.types.sales'),
                        'products' => __('admin.reports.types.products'),
                        'customers' => __('admin.reports.types.customers'),
                    ])
                    ->required(),
                Textarea::make('description')
                    ->label(__('admin.reports.fields.description'))
                    ->rows(3),
                Toggle::make('is_active')
                    ->label(__('admin.reports.fields.is_active'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.reports.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('admin.reports.fields.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sales' => 'success',
                        'products' => 'info',
                        'customers' => 'warning',
                        'inventory' => 'secondary',
                        'analytics' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("admin.reports.types.{$state}")),
                TextColumn::make('category')
                    ->label(__('admin.reports.fields.category'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sales' => 'success',
                        'marketing' => 'info',
                        'inventory' => 'secondary',
                        'gray' => 'analytics',
                    })
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
                    ->sortable(),
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
                        'inventory' => __('admin.reports.categories.inventory'),
                        'analytics' => __('admin.reports.categories.analytics'),
                    ])
                    ->multiple(),
                TernaryFilter::make('is_active')
                    ->label(__('admin.reports.fields.is_active'))
                    ->boolean()
                    ->trueLabel(__('admin.common.active'))
                    ->falseLabel(__('admin.common.inactive'))
                    ->native(false),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                //
            ]);
    }
}
