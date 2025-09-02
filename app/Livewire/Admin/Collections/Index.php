<?php declare(strict_types=1);

namespace App\Livewire\Admin\Collections;

use App\Livewire\Pages\AbstractPageComponent;
use App\Models\Collection as CollectionModel;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms as Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

final class Index extends AbstractPageComponent implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(CollectionModel::query()->latest())
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->striped()
            ->paginationPageOptions([10, 25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->badge()
                    ->color('gray')
                    ->url(fn($record): string => route('collection.show', ['locale' => app()->getLocale(), 'slug' => $record->slug]))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('type')->label(__('Type'))->badge()->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')->label(__('Enabled'))->boolean()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->label(__('Updated'))->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('name')
                    ->form([
                        Forms\Components\TextInput::make('value')->label(__('Name')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $v = (string) ($data['value'] ?? '');
                        return $v !== '' ? $query->where('name', 'like', '%' . $v . '%') : $query;
                    })
                    ->indicateUsing(fn(array $d): ?string => ($d['value'] ?? null) ? __('Name') . ': ' . $d['value'] : null),
                Tables\Filters\Filter::make('slug')
                    ->form([
                        Forms\Components\TextInput::make('value')->label(__('Slug')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $v = (string) ($data['value'] ?? '');
                        return $v !== '' ? $query->where('slug', 'like', '%' . $v . '%') : $query;
                    })
                    ->indicateUsing(fn(array $d): ?string => ($d['value'] ?? null) ? __('Slug') . ': ' . $d['value'] : null),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'manual' => __('Manual'),
                        'auto' => __('Auto'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_enabled')->label(__('Enabled')),
                Tables\Filters\Filter::make('updated_between')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label(__('From')),
                        Forms\Components\DatePicker::make('until')->label(__('Until')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $d) => $q->whereDate('updated_at', '>=', $d))
                            ->when($data['until'] ?? null, fn($q, $d) => $q->whereDate('updated_at', '<=', $d));
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
                Tables\Actions\Action::make('view')
                    ->label(__('View'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn($record): string => route('collection.show', ['locale' => app()->getLocale(), 'slug' => $record->slug]))
                    ->openUrlInNewTab()
                    ->color('gray'),
                Tables\Actions\Action::make('edit')
                    ->label(__('Edit'))
                    ->icon('untitledui-edit-02')
                    ->url(fn($record): string => route('collection.edit', ['locale' => app()->getLocale(), 'slug' => $record->slug]))
                    ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('enable')
                    ->label(__('Enable'))
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->action(fn($records) => $records->each->update(['is_enabled' => true])),
                Tables\Actions\BulkAction::make('disable')
                    ->label(__('Disable'))
                    ->icon('heroicon-m-x-mark')
                    ->color('warning')
                    ->action(fn($records) => $records->each->update(['is_enabled' => false])),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Create'))
                    ->form([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('slug')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(255),
                        Forms\Components\Select::make('type')->options(['manual' => 'manual', 'auto' => 'auto'])->required(),
                        Forms\Components\Toggle::make('is_enabled')->label(__('Enabled'))->default(true),
                    ])
                    ->action(fn(array $data) => CollectionModel::create([
                        'name' => (string) $data['name'],
                        'slug' => (string) $data['slug'],
                        'type' => (string) $data['type'],
                        'is_enabled' => (bool) ($data['is_enabled'] ?? true),
                    ])),
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
                    ->exporter(\App\Filament\Exports\CollectionExporter::class)
                    ->formats([\Filament\Actions\Exports\Enums\ExportFormat::Csv])
                    ->fileName(fn(\Filament\Actions\Exports\Models\Export $export): string => 'collections-' . now()->format('Ymd_His') . '.csv'),
            ]);
    }

    #[Layout('layouts.templates.app')]
    public function render()
    {
        return view('livewire.admin.collections.index');
    }
}
