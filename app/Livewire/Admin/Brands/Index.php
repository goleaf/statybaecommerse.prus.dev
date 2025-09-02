<?php declare(strict_types=1);

namespace App\Livewire\Admin\Brands;

use App\Livewire\Pages\AbstractPageComponent;
use App\Models\Brand;
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
            ->query(Brand::query()->latest())
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->striped()
            ->paginationPageOptions([10, 25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label(__('Slug'))->badge()->color('gray')->searchable(),
                Tables\Columns\TextColumn::make('website')->label(__('Website')),
                Tables\Columns\IconColumn::make('is_enabled')->label(__('Enabled'))->boolean()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label(__('Updated'))->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('name')
                    ->form([Forms\Components\TextInput::make('value')->label(__('Name'))])
                    ->query(function (Builder $query, array $data) {
                        $v = (string) ($data['value'] ?? '');
                        return $v !== '' ? $query->where('name', 'like', '%' . $v . '%') : $query;
                    })
                    ->indicateUsing(fn(array $d): ?string => ($d['value'] ?? null) ? __('Name') . ': ' . $d['value'] : null),
                Tables\Filters\Filter::make('slug')
                    ->form([Forms\Components\TextInput::make('value')->label(__('Slug'))])
                    ->query(function (Builder $query, array $data) {
                        $v = (string) ($data['value'] ?? '');
                        return $v !== '' ? $query->where('slug', 'like', '%' . $v . '%') : $query;
                    })
                    ->indicateUsing(fn(array $d): ?string => ($d['value'] ?? null) ? __('Slug') . ': ' . $d['value'] : null),
                Tables\Filters\TernaryFilter::make('is_enabled')->label(__('Enabled')),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label(__('Edit'))
                    ->icon('untitledui-edit-02')
                    ->action(fn($record) => $this->dispatch('openPanel', component: 'shopper-slide-overs.brand-form', arguments: ['brandId' => $record->id])),
                Tables\Actions\DeleteAction::make('delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('enable')->label(__('Enable'))->icon('heroicon-m-check')->color('success')->action(fn($records) => $records->each->update(['is_enabled' => true])),
                Tables\Actions\BulkAction::make('disable')->label(__('Disable'))->icon('heroicon-m-x-mark')->color('warning')->action(fn($records) => $records->each->update(['is_enabled' => false])),
            ])
            ->headerActions([
                Tables\Actions\Action::make('refresh')->label(__('Refresh'))->icon('heroicon-m-arrow-path')->color('gray')->action(fn() => $this->dispatch('$refresh')),
                Tables\Actions\Action::make('reset_filters')->label(__('Reset Filters'))->icon('heroicon-m-x-mark')->color('warning')->action(fn() => $this->resetTable()),
                \Filament\Tables\Actions\ExportAction::make('export')
                    ->label(__('Export'))
                    ->exporter(\App\Filament\Exports\BrandExporter::class)
                    ->formats([\Filament\Actions\Exports\Enums\ExportFormat::Csv])
                    ->fileName(fn(\Filament\Actions\Exports\Models\Export $export): string => 'brands-' . now()->format('Ymd_His') . '.csv'),
            ]);
    }

    #[Layout('layouts.templates.app')]
    public function render()
    {
        return view('livewire.admin.brands.index');
    }
}
