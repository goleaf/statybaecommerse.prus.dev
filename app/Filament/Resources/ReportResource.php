<?php

declare(strict_types=1);
namespace App\Filament\Resources;
use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use App\Models\User;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
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
/**
 * ReportResource
 * 
 * Filament v4 resource for Report management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    
    // protected static $navigationGroup = NavigationGroup::System;
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
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "System";
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('reports.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('reports.single');
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
    public static function form(Schema $schema): Schema
    {$state}"))
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
                        'pdf' => 'red',
                        'excel' => 'green',
                        'csv' => 'blue',
                        'json' => 'purple',
                        'html' => 'orange',
                TextColumn::make('frequency')
                    ->label(__('reports.frequency'))
                    ->formatStateUsing(fn (string $state): string => __("reports.frequencies.{$state}"))
                        'once' => 'gray',
                        'daily' => 'blue',
                        'weekly' => 'green',
                        'monthly' => 'purple',
                        'quarterly' => 'orange',
                        'yearly' => 'red',
                TextColumn::make('max_rows')
                    ->label(__('reports.max_rows'))
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('scheduled_at')
                    ->label(__('reports.scheduled_at'))
                    ->dateTime()
                TextColumn::make('timezone')
                    ->label(__('reports.timezone'))
                IconColumn::make('is_scheduled')
                    ->label(__('reports.is_scheduled'))
                    ->boolean()
                IconColumn::make('auto_generate')
                    ->label(__('reports.auto_generate'))
                TextColumn::make('users_count')
                    ->label(__('reports.recipients_count'))
                    ->counts('users')
                IconColumn::make('is_active')
                    ->label(__('reports.is_active'))
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('reports.sort_order'))
                TextColumn::make('created_at')
                    ->label(__('reports.created_at'))
                TextColumn::make('updated_at')
                    ->label(__('reports.updated_at'))
            ])
            ->filters([
                SelectFilter::make('type')
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
                        'pdf' => __('reports.formats.pdf'),
                        'excel' => __('reports.formats.excel'),
                        'csv' => __('reports.formats.csv'),
                        'json' => __('reports.formats.json'),
                        'html' => __('reports.formats.html'),
                SelectFilter::make('frequency')
                        'once' => __('reports.frequencies.once'),
                        'daily' => __('reports.frequencies.daily'),
                        'weekly' => __('reports.frequencies.weekly'),
                        'monthly' => __('reports.frequencies.monthly'),
                        'quarterly' => __('reports.frequencies.quarterly'),
                        'yearly' => __('reports.frequencies.yearly'),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('reports.active_only'))
                    ->falseLabel(__('reports.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_scheduled')
                    ->trueLabel(__('reports.scheduled_only'))
                    ->falseLabel(__('reports.manual_only'))
                TernaryFilter::make('auto_generate')
                    ->trueLabel(__('reports.auto_generate_only'))
                    ->falseLabel(__('reports.manual_generate_only'))
            ->actions([
                // Actions will be added later
            ->bulkActions([
                // Bulk actions will be added later
            ->defaultSort('sort_order');
     * Get the relations for this resource.
     * @return array
    public static function getRelations(): array
        return [
            //
        ];
     * Get the pages for this resource.
    public static function getPages(): array
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
}
