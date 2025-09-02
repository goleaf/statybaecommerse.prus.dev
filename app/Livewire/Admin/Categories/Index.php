<?php declare(strict_types=1);

namespace App\Livewire\Admin\Categories;

use App\Livewire\Pages\AbstractPageComponent;
use App\Models\Category as CategoryModel;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms as Forms;
use Filament\Tables;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class Index extends AbstractPageComponent implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function mount(): void
    {
        $this->authorize('browse_categories');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CategoryModel::query()
                    ->with('parent')
                    ->latest()
            )
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->striped()
            ->paginationPageOptions([10, 25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->badge()
                    ->color('gray')
                    ->url(fn($record): string => route('category.show', ['locale' => app()->getLocale(), 'slug' => $record->slug]))
                    ->openUrlInNewTab(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('Visibility'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('name')
                    ->form([
                        Forms\Components\TextInput::make('value')->label(__('Name')),
                    ])
                    ->query(function ($query, array $data) {
                        $value = (string) ($data['value'] ?? '');
                        return $value !== '' ? $query->where('name', 'like', '%' . $value . '%') : $query;
                    })
                    ->indicateUsing(fn(array $data): ?string => ($data['value'] ?? null) ? __('Name') . ': ' . $data['value'] : null),
                Tables\Filters\Filter::make('slug')
                    ->form([
                        Forms\Components\TextInput::make('value')->label(__('Slug')),
                    ])
                    ->query(function ($query, array $data) {
                        $value = (string) ($data['value'] ?? '');
                        return $value !== '' ? $query->where('slug', 'like', '%' . $value . '%') : $query;
                    })
                    ->indicateUsing(fn(array $data): ?string => ($data['value'] ?? null) ? __('Slug') . ': ' . $data['value'] : null),
                Tables\Filters\TernaryFilter::make('is_enabled'),
                Tables\Filters\Filter::make('updated_between')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label(__('Published from')),
                        Forms\Components\DatePicker::make('until')->label(__('Published until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $date) => $q->whereDate('updated_at', '>=', $date))
                            ->when($data['until'] ?? null, fn($q, $date) => $q->whereDate('updated_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $out = [];
                        if ($data['from'] ?? null) {
                            $out[] = __('From') . ': ' . (string) $data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $out[] = __('Until') . ': ' . (string) $data['until'];
                        }
                        return $out;
                    }),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label(__('Edit'))
                    ->icon('heroicon-o-pencil-square')
                    ->action(
                        fn($record) => $this->dispatch(
                            'openPanel',
                            component: 'livewire.slide-overs.category-form',
                            arguments: ['categoryId' => $record->id]
                        )
                    ),
                Tables\Actions\Action::make('view')
                    ->label(__('View'))
                    ->icon('heroicon-o-eye')
                    ->url(fn($record): string => route('category.show', ['locale' => app()->getLocale(), 'slug' => $record->slug]))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make()
                    ->label(__('Delete'))
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),
            ])
            ->groupedBulkActions([
                Tables\Actions\BulkAction::make('enabled')
                    ->label(__('Enable'))
                    ->icon('heroicon-o-check')
                    ->action(function (Collection $records): void {
                        $records->each->updateStatus();

                        Notification::make()
                            ->title(__('Enabled'))
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('disabled')
                    ->label(__('Disable'))
                    ->icon('heroicon-o-no-symbol')
                    ->action(function (Collection $records): void {
                        $records->each->updateStatus(status: false);

                        Notification::make()
                            ->title(__('Disabled'))
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\DeleteBulkAction::make()
                    ->label(__('Delete'))
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('refresh')
                    ->label(__('Refresh'))
                    ->icon('heroicon-m-arrow-path')
                    ->color('gray')
                    ->action(fn() => $this->dispatch('$refresh')),
                Tables\Actions\Action::make('reset_filters')
                    ->label(__('Reset Filters'))
                    ->icon('heroicon-m-x-mark')
                    ->color('warning')
                    ->action(fn() => $this->resetTable()),
                \Filament\Tables\Actions\ExportAction::make('export')
                    ->label(__('Export'))
                    ->exporter(\App\Filament\Exports\CategoryExporter::class)
                    ->formats([\Filament\Actions\Exports\Enums\ExportFormat::Csv])
                    ->fileName(fn(\Filament\Actions\Exports\Models\Export $export): string => 'categories-' . now()->format('Ymd_His') . '.csv'),
            ]);
    }

    public function render(): View
    {
        return view('livewire.admin.categories.index')
            ->title(__('Categories'));
    }
}
