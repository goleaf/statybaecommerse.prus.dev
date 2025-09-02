<?php declare(strict_types=1);

namespace App\Livewire\Admin\Orders;

use App\Livewire\Pages\AbstractPageComponent;
use App\Models\Order;
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
            ->query(Order::query()->with(['customer'])->latest())
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->striped()
            ->paginationPageOptions([10, 25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('number')->label(__('Number'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.email')->label(__('Customer'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->label(__('Status'))->badge()->sortable(),
                Tables\Columns\TextColumn::make('grand_total_amount')->label(__('Total'))->money('EUR')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label(__('Created'))->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('number')
                    ->form([
                        Forms\Components\TextInput::make('value')->label(__('Number')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $v = (string) ($data['value'] ?? '');
                        return $v !== '' ? $query->where('number', 'like', '%' . $v . '%') : $query;
                    })
                    ->indicateUsing(fn(array $d): ?string => ($d['value'] ?? null) ? __('Number') . ': ' . $d['value'] : null),
                Tables\Filters\Filter::make('email')
                    ->form([
                        Forms\Components\TextInput::make('value')->label(__('Customer email')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $v = (string) ($data['value'] ?? '');
                        return $v !== '' ? $query->whereHas('customer', fn($q) => $q->where('email', 'like', '%' . $v . '%')) : $query;
                    })
                    ->indicateUsing(fn(array $d): ?string => ($d['value'] ?? null) ? __('Email') . ': ' . $d['value'] : null),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => __('Pending'),
                        'processing' => __('Processing'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                        'refunded' => __('Refunded'),
                    ]),
                Tables\Filters\Filter::make('created_between')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label(__('From')),
                        Forms\Components\DatePicker::make('until')->label(__('Until')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['until'] ?? null, fn($q, $d) => $q->whereDate('created_at', '<=', $d));
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
                    ->url(fn(Order $record): string => route('admin.orders.status.edit', ['number' => $record->number]))
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_processing')
                    ->label(__('Mark Processing'))
                    ->icon('heroicon-m-cog-6-tooth')
                    ->color('primary')
                    ->action(fn($records) => $records->each->update(['status' => 'processing'])),
                Tables\Actions\BulkAction::make('mark_completed')
                    ->label(__('Mark Completed'))
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->action(fn($records) => $records->each->update(['status' => 'completed'])),
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
                    ->exporter(\App\Filament\Exports\OrderExporter::class)
                    ->formats([\Filament\Actions\Exports\Enums\ExportFormat::Csv])
                    ->fileName(fn(\Filament\Actions\Exports\Models\Export $export): string => 'orders-' . now()->format('Ymd_His') . '.csv'),
            ]);
    }

    #[Layout('layouts.templates.app')]
    public function render()
    {
        return view('livewire.admin.orders.index');
    }
}
