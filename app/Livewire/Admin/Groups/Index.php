<?php declare(strict_types=1);

namespace App\Livewire\Admin\Groups;

use App\Livewire\Pages\AbstractPageComponent;
use App\Models\CustomerGroup;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

class Index extends AbstractPageComponent implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(CustomerGroup::query())
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->striped()
            ->paginationPageOptions([10, 25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code')->label(__('Code'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('users_count')->counts('users')->label(__('Users'))->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
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
                Tables\Filters\Filter::make('code')
                    ->form([
                        Forms\Components\TextInput::make('value')->label(__('Code')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $v = (string) ($data['value'] ?? '');
                        return $v !== '' ? $query->where('code', 'like', '%' . $v . '%') : $query;
                    })
                    ->indicateUsing(fn(array $d): ?string => ($d['value'] ?? null) ? __('Code') . ': ' . $d['value'] : null),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label(__('Edit'))
                    ->icon('untitledui-edit-02')
                    ->form($this->form()->getSchema())
                    ->mountUsing(function (Forms\Form $form, CustomerGroup $record) {
                        $form->fill($record->only(['name', 'code', 'metadata']));
                    })
                    ->action(function (CustomerGroup $record, array $data) {
                        $record->update($data);
                    }),
                Tables\Actions\DeleteAction::make('delete'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Create'))
                    ->form($this->form()->getSchema()),
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
                    ->exporter(\App\Filament\Exports\CustomerGroupExporter::class)
                    ->formats([\Filament\Actions\Exports\Enums\ExportFormat::Csv])
                    ->fileName(fn(\Filament\Actions\Exports\Models\Export $export): string => 'groups-' . now()->format('Ymd_His') . '.csv'),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('code')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(100),
                Forms\Components\KeyValue::make('metadata')->keyLabel(__('Key'))->valueLabel(__('Value'))->reorderable(),
            ]);
    }

    #[Layout('layouts.templates.app')]
    public function render()
    {
        return view('livewire.admin.groups.index');
    }
}
