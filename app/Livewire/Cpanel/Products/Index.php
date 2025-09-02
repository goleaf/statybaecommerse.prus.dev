<?php declare(strict_types=1);

namespace App\Livewire\Cpanel\Products;

use App\Livewire\Pages\AbstractPageComponent;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ExportAction as TableExportAction;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

final class Index extends AbstractPageComponent implements HasForms, Tables\Contracts\HasTable
{
    use InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = Product::query()
            ->select(['id', 'name', 'slug', 'sku', 'is_visible', 'published_at', 'warehouse_quantity'])
            ->withCount('variants');

        return $table
            ->query($query)
            ->defaultSort('id', 'desc')
            ->filters([
                Filter::make('name')
                    ->form([
                        TextInput::make('value')->label(__('Name')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = (string) ($data['value'] ?? '');
                        return $value !== ''
                            ? $query->where('name', 'like', '%' . $value . '%')
                            : $query;
                    })
                    ->indicateUsing(fn(array $data): ?string => ($data['value'] ?? null) ? __('Name') . ': ' . $data['value'] : null),
                Filter::make('sku')
                    ->form([
                        TextInput::make('value')->label(__('SKU')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = (string) ($data['value'] ?? '');
                        return $value !== ''
                            ? $query->where('sku', 'like', '%' . $value . '%')
                            : $query;
                    })
                    ->indicateUsing(fn(array $data): ?string => ($data['value'] ?? null) ? __('SKU') . ': ' . $data['value'] : null),
                TernaryFilter::make('is_visible')
                    ->label(__('Visible')),
                Filter::make('published_between')
                    ->form([
                        DatePicker::make('from')->label(__('Published from')),
                        DatePicker::make('until')->label(__('Published until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn(Builder $q, $date): Builder => $q->whereDate('published_at', '>=', $date))
                            ->when($data['until'] ?? null, fn(Builder $q, $date): Builder => $q->whereDate('published_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = __('From') . ': ' . (string) $data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = __('Until') . ': ' . (string) $data['until'];
                        }
                        return $indicators;
                    }),
                Filter::make('warehouse_quantity_range')
                    ->form([
                        TextInput::make('min')->numeric()->label(__('Min stock')),
                        TextInput::make('max')->numeric()->label(__('Max stock')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $expr = 'COALESCE(sh_products.warehouse_quantity, (SELECT COALESCE(SUM(CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END), 0) FROM sh_product_variants v JOIN sh_variant_inventories vi ON vi.variant_id = v.id WHERE v.product_id = sh_products.id))';
                        return $query
                            ->when($data['min'] ?? null, fn(Builder $q, $value): Builder => $q->whereRaw($expr . ' >= ?', [(int) $value]))
                            ->when($data['max'] ?? null, fn(Builder $q, $value): Builder => $q->whereRaw($expr . ' <= ?', [(int) $value]));
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns([
                'sm' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession()
            ->striped()
            ->paginationPageOptions([10, 25, 50, 100])
            ->headerActions([
                TableAction::make('refresh')
                    ->label(__('Refresh'))
                    ->icon('heroicon-m-arrow-path')
                    ->color('gray')
                    ->action(function (): void {
                        $this->dispatch('$refresh');
                    }),
                TableAction::make('reset_filters')
                    ->label(__('Reset Filters'))
                    ->icon('heroicon-m-x-mark')
                    ->color('warning')
                    ->action(function (): void {
                        $this->resetTable();
                    }),
                TableAction::make('create_product')
                    ->label(__('Create Product'))
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->action(function (): void {
                        $this->dispatch('openPanel', component: 'shopper-slide-overs.add-product');
                    }),
                TableExportAction::make('export')
                    ->label(__('Export'))
                    ->exporter(\App\Filament\Exports\ProductExporter::class)
                    ->formats([\Filament\Actions\Exports\Enums\ExportFormat::Csv])
                    ->fileName(fn(\Filament\Actions\Exports\Models\Export $export): string => 'products-' . now()->format('Ymd_His') . '.csv'),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('ID'))->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->url(fn(Product $record): string => route('product.show', ['locale' => app()->getLocale(), 'slug' => $record->slug]))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('sku')->label(__('SKU'))->toggleable(isToggledHiddenByDefault: true)->searchable(),
                Tables\Columns\TextColumn::make('variants_count')->label(__('Variants'))->sortable(),
                Tables\Columns\IconColumn::make('is_visible')->boolean()->label(__('Visible'))->sortable(),
                Tables\Columns\TextColumn::make('published_at')->dateTime()->label(__('Published'))->sortable(),
                Tables\Columns\TextColumn::make('warehouse_quantity')
                    ->label(__('Warehouse Stock'))
                    ->state(function (Product $record): int {
                        if ($record->warehouse_quantity !== null) {
                            return (int) $record->warehouse_quantity;
                        }
                        $sum = (int) DB::table('sh_product_variants as v')
                            ->join('sh_variant_inventories as vi', 'vi.variant_id', '=', 'v.id')
                            ->where('v.product_id', $record->id)
                            ->sum(DB::raw('CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END'));
                        if ($sum === 0) {
                            $sum = (int) DB::table('sh_variant_inventories as vi')
                                ->where('vi.variant_id', $record->id)
                                ->sum(DB::raw('CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END'));
                        }
                        return $sum;
                    })
                    ->sortable(),
            ])
            ->actions([
                TableAction::make('edit')
                    ->label(__('Edit'))
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn(Product $record): string => route('admin.products.edit', ['id' => $record->id]))
                    ->color('primary'),
                TableAction::make('view')
                    ->label(__('View'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn(Product $record): string => route('product.show', ['locale' => app()->getLocale(), 'slug' => $record->slug]))
                    ->openUrlInNewTab()
                    ->color('gray'),
                TableAction::make('toggle_visibility')
                    ->label(__('Toggle Visibility'))
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->action(function (Product $record): void {
                        $record->is_visible = $record->is_visible ? 0 : 1;
                        $record->save();
                        $this->dispatch('$refresh');
                    }),
                TableAction::make('delete')
                    ->label(__('Delete'))
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Product $record): void {
                        $record->delete();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('set_visible')
                    ->label(__('Set Visible'))
                    ->icon('heroicon-m-eye')
                    ->color('success')
                    ->action(function (Collection $records): void {
                        DB::table('sh_products')->whereIn('id', $records->pluck('id'))->update(['is_visible' => 1, 'updated_at' => now()]);
                    }),
                BulkAction::make('set_hidden')
                    ->label(__('Set Hidden'))
                    ->icon('heroicon-m-eye-slash')
                    ->color('warning')
                    ->action(function (Collection $records): void {
                        DB::table('sh_products')->whereIn('id', $records->pluck('id'))->update(['is_visible' => 0, 'updated_at' => now()]);
                    }),
            ]);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.cpanel.products.index')
            ->with('title', __('Products'))
            ->title(__('Products'));
    }

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }
}
