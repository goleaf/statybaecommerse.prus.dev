<?php declare(strict_types=1);

namespace App\Filament\Resources\ReportResource\Widgets;

use App\Models\Report;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class RecentReportsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Reports';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Report::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.reports.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('type')
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

                Tables\Columns\TextColumn::make('view_count')
                    ->label(__('admin.reports.fields.view_count'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('download_count')
                    ->label(__('admin.reports.fields.download_count'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.reports.fields.is_active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.reports.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('admin.reports.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Report $record): string => route('reports.show', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('generate')
                    ->label(__('admin.reports.actions.generate'))
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (Report $record) {
                        $record->update([
                            'last_generated_at' => now(),
                            'generated_by' => auth()->id(),
                        ]);
                    })
                    ->visible(fn (Report $record): bool => $record->is_active),
            ])
            ->paginated(false);
    }
}
