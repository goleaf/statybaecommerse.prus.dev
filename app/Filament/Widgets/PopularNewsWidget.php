<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\News;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
/**
 * PopularNewsWidget
 * 
 * Filament v4 widget for PopularNewsWidget dashboard display with real-time data and interactive features.
 * 
 * @property int|string|array $columnSpan
 * @property string|null $heading
 */
final class PopularNewsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Popular News';
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query(News::query()->published()->orderBy('view_count', 'desc')->limit(5))->columns([Tables\Columns\TextColumn::make('translations.title')->label(__('admin.news.fields.title'))->searchable()->limit(50), Tables\Columns\IconColumn::make('is_featured')->label(__('admin.news.fields.is_featured'))->boolean(), Tables\Columns\TextColumn::make('published_at')->label(__('admin.news.fields.published_at'))->dateTime()->sortable(), Tables\Columns\TextColumn::make('author_name')->label(__('admin.news.fields.author_name'))->searchable(), Tables\Columns\TextColumn::make('view_count')->label(__('admin.news.fields.view_count'))->numeric()->sortable()])->actions([Tables\Actions\Action::make('view')->label(__('admin.news.actions.view'))->url(fn(News $record): string => route('filament.admin.resources.news.view', $record))->openUrlInNewTab()]);
    }
}