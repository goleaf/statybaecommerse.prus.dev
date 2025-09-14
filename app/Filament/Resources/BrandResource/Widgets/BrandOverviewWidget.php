<?php

declare (strict_types=1);
namespace App\Filament\Resources\BrandResource\Widgets;

use App\Models\Brand;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
/**
 * BrandOverviewWidget
 * 
 * Filament v4 resource for BrandOverviewWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property int|string|array $columnSpan
 * @property string|null $heading
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class BrandOverviewWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'admin.brands.widgets.overview_heading';
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query(Brand::query()->with(['products', 'translations'])->latest()->limit(10))->columns([Tables\Columns\TextColumn::make('name')->label(__('admin.brands.fields.name'))->searchable()->sortable()->formatStateUsing(fn($record): string => $record->trans('name') ?: $record->name), Tables\Columns\TextColumn::make('products_count')->counts('products')->label(__('admin.brands.fields.products_count'))->badge()->color('primary'), Tables\Columns\TextColumn::make('translations_count')->counts('translations')->label(__('admin.brands.fields.translations_count'))->badge()->color(fn($state): string => $state > 0 ? 'success' : 'gray'), Tables\Columns\IconColumn::make('is_enabled')->label(__('admin.brands.fields.is_enabled'))->boolean()->trueColor('success')->falseColor('danger'), Tables\Columns\TextColumn::make('created_at')->label(__('admin.brands.fields.created_at'))->date('Y-m-d H:i')->sortable()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()])->defaultSort('created_at', 'desc');
    }
}