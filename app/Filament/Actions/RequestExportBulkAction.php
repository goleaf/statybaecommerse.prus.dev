<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Data\ExportRequestData;
use App\Enums\ExportFormat;
use App\Enums\ExportType;
use App\Services\Export\ExportService;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class RequestExportBulkAction
{
    public static function make(ExportType $type): BulkAction
    {
        $config = config(sprintf('exports.entities.%s', $type->value));
        $columns = collect($config['columns'] ?? [])->mapWithKeys(fn ($column, $key) => [$key => $column['label']])->all();

        return BulkAction::make('export_'.$type->value)
            ->label(__('Export'))
            ->icon('heroicon-o-arrow-down-tray')
            ->color('info')
            ->form([
                Grid::make(2)->schema([
                    Select::make('format')
                        ->label(__('Format'))
                        ->options(collect(ExportFormat::cases())->mapWithKeys(fn (ExportFormat $format) => [$format->value => Str::upper($format->value)])->all())
                        ->default(ExportFormat::CSV->value)
                        ->required(),
                    TextInput::make('locale')
                        ->label(__('Locale'))
                        ->default(app()->getLocale()),
                    TextInput::make('timezone')
                        ->label(__('Timezone'))
                        ->default(config('app.timezone')),
                ]),
                CheckboxList::make('columns')
                    ->label(__('Columns'))
                    ->options($columns)
                    ->default(array_keys($columns))
                    ->columns(2)
                    ->helperText(__('Select which columns should be included in the export. Leave empty for defaults.')),
                KeyValue::make('filters')
                    ->label(__('Filters'))
                    ->helperText(__('Provide optional key/value filters, e.g. status => paid or created_from => 2024-01-01.'))
                    ->keyLabel(__('Field'))
                    ->valueLabel(__('Value')),
            ])
            ->action(function (Collection $records, array $data, ExportService $exportService) use ($type): void {
                $user = auth()->user();

                if (! $user) {
                    Notification::make()
                        ->title(__('Unable to identify the authenticated user.'))
                        ->danger()
                        ->send();

                    return;
                }

                $filters = array_filter($data['filters'] ?? [], static function ($value): bool {
                    return ! ($value === null || $value === '');
                });

                $request = ExportRequestData::from([
                    'entity' => $type->value,
                    'filters' => $filters,
                    'columns' => array_values($data['columns'] ?? []),
                    'format' => $data['format'],
                    'locale' => $data['locale'] ?? app()->getLocale(),
                    'timezone' => $data['timezone'] ?? config('app.timezone'),
                    'ids' => $records->pluck('id')->all(),
                ]);

                $exportService->queueExport($request, $user);

                Notification::make()
                    ->title(__('Export request queued'))
                    ->body(__('You will be notified when the export is ready for download.'))
                    ->success()
                    ->send();
            });
    }
}
