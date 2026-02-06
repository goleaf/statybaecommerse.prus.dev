<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Data\ExportRequestData;
use App\Enums\ExportFormat;
use App\Enums\ExportType;
use App\Services\Export\ExportService;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class RequestExportBulkAction
{
    public static function make(ExportType $type): BulkAction
    {
        $config = config(sprintf('exports.entities.%s', $type->value));
        $columns = collect($config['columns'] ?? [])->mapWithKeys(fn ($column, $key) => [$key => $column['label']])->all();

        return BulkAction::make('export_' . $type->value)
            ->label(__('ui.export'))
            ->icon('heroicon-o-arrow-down-tray')
            ->color('info')
            ->form([
                Grid::make(2)->schema([
                    Select::make('format')
                        ->label(__('ui.format'))
                        ->options(collect(ExportFormat::cases())->mapWithKeys(fn (ExportFormat $format) => [$format->value => Str::upper($format->value)])->all())
                        ->default(ExportFormat::CSV->value)
                        ->required(),
                    TextInput::make('locale')
                        ->label(__('ui.locale'))
                        ->default(app()->getLocale()),
                    TextInput::make('timezone')
                        ->label(__('ui.timezone'))
                        ->default(config('app.timezone')),
                ]),
                CheckboxList::make('columns')
                    ->label(__('ui.columns'))
                    ->options($columns)
                    ->default(array_keys($columns))
                    ->columns(2)
                    ->helperText(__('Select which columns should be included in the export. Leave empty for defaults.')),
                KeyValue::make('filters')
                    ->label(__('ui.filters'))
                    ->helperText(__('messages.export_filters_help'))
                    ->keyLabel(__('ui.field'))
                    ->valueLabel(__('messages.value')),
            ])
            ->action(function (Collection $records, array $data, ExportService $exportService) use ($type): void {
                $user = auth()->user();

                if (! $user) {
                    Notification::make()
                        ->title(__('messages.unable_to_identify_the_authenticated_user'))
                        ->danger()
                        ->send();

                    return;
                }

                $filters = array_filter($data['filters'] ?? [], static function ($value): bool {
                    return ! ($value === null || $value === '');
                });

                $request = ExportRequestData::from([
                    'entity'   => $type->value,
                    'filters'  => $filters,
                    'columns'  => array_values($data['columns'] ?? []),
                    'format'   => $data['format'],
                    'locale'   => $data['locale'] ?? app()->getLocale(),
                    'timezone' => $data['timezone'] ?? config('app.timezone'),
                    'ids'      => $records->pluck('id')->all(),
                ]);

                $exportService->queueExport($request, $user);

                Notification::make()
                    ->title(__('ui.export_request_queued'))
                    ->body(__('messages.you_will_be_notified_when_the_export_is_ready_for_download'))
                    ->success()
                    ->send();
            });
    }
}
