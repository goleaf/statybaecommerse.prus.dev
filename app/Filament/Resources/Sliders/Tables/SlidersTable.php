<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\Tables;

use App\Models\Slider;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SlidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('admin.sliders.image'))
                    ->getStateUsing(function ($record): ?string {
                        return $record->getFirstMedia('slider_images')?->getUrl('thumb');
                    })
                    ->defaultImageUrl('/images/placeholder-slider.svg')
                    ->size(60)
                    ->square(),
                TextColumn::make('title')
                    ->label(__('admin.sliders.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),
                TextColumn::make('description')
                    ->label(__('admin.sliders.description'))
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('button_text')
                    ->label(__('admin.sliders.button_text'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('button_url')
                    ->label(__('admin.sliders.button_url'))
                    ->searchable()
                    ->toggleable()
                    ->limit(30),
                TextColumn::make('sort_order')
                    ->label(__('admin.sliders.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                IconColumn::make('is_active')
                    ->label(__('admin.sliders.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label(__('admin.sliders.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.sliders.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('admin.sliders.is_active'))
                    ->placeholder(__('admin.sliders.all_sliders'))
                    ->trueLabel(__('admin.sliders.active_sliders'))
                    ->falseLabel(__('admin.sliders.inactive_sliders')),
            ])
            ->headerActions([
                Action::make('export')
                    ->label(__('translations.export_sliders'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (): ?StreamedResponse {
                        $sliders = Slider::query()->with('translations')->get();

                        if ($sliders->isEmpty()) {
                            Notification::make()
                                ->title(__('translations.no_sliders'))
                                ->warning()
                                ->send();

                            return null;
                        }

                        $payload = $sliders
                            ->map(fn (Slider $slider) => self::formatSliderForExport($slider))
                            ->values()
                            ->all();

                        return response()->streamDownload(
                            static function () use ($payload): void {
                                echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                            },
                            'sliders-export.json',
                            [
                                'Content-Type' => 'application/json',
                            ],
                        );
                    }),
                Action::make('import')
                    ->label(__('Import sliders'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->form([
                        FileUpload::make('import_file')
                            ->label(__('translations.import_file'))
                            ->acceptedFileTypes(['application/json', 'text/json'])
                            ->storeFiles(false),
                        Toggle::make('update_existing')
                            ->label(__('translations.update_existing'))
                            ->default(false),
                    ])
                    ->action(function (array $data): void {
                        $file = $data['import_file'] ?? null;
                        $updateExisting = (bool) ($data['update_existing'] ?? false);

                        if (! $file instanceof TemporaryUploadedFile) {
                            Notification::make()
                                ->title(__('Please select a JSON file to import.'))
                                ->warning()
                                ->send();

                            return;
                        }

                        $contents = $file->get();
                        $decoded = json_decode($contents, true);

                        if (! is_array($decoded)) {
                            Notification::make()
                                ->title(__('Invalid import file.'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $records = self::normaliseImportedPayload($decoded);

                        if (empty($records)) {
                            Notification::make()
                                ->title(__('No sliders found in import file.'))
                                ->warning()
                                ->send();

                            return;
                        }

                        $imported = 0;

                        foreach ($records as $record) {
                            $slider = self::persistImportedSlider($record, $updateExisting);

                            if ($slider !== null) {
                                $imported++;
                            }
                        }

                        $message = match (true) {
                            $imported === 0 => __('No sliders were imported.'),
                            $imported === 1 => __('One slider was imported.'),
                            default         => __(':count sliders were imported.', ['count' => $imported]),
                        };

                        Notification::make()
                            ->title(__('Slider data imported successfully.'))
                            ->body($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    ViewAction::make(),
                    Action::make('toggle_active')
                        ->label(fn (Slider $record): string => $record->is_active
                            ? __('translations.deactivate')
                            : __('translations.activate'))
                        ->icon(fn (Slider $record): string => $record->is_active
                            ? 'heroicon-o-x-circle'
                            : 'heroicon-o-check-circle')
                        ->color(fn (Slider $record): string => $record->is_active ? 'danger' : 'success')
                        ->action(function (Slider $record): void {
                            $record->update(['is_active' => ! $record->is_active]);
                            $record->refresh();

                            Notification::make()
                                ->title($record->is_active
                                    ? __('translations.slider_activated')
                                    : __('translations.slider_deactivated'))
                                ->success()
                                ->send();
                        }),
                    Action::make('duplicate')
                        ->label(__('translations.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function (Slider $record): void {
                            DB::transaction(function () use ($record): void {
                                $newSlider = $record->replicate();
                                $newSlider->title = sprintf('%s (Copy)', $record->title);
                                $newSlider->sort_order = ((int) (Slider::max('sort_order') ?? 0)) + 1;
                                $newSlider->save();

                                foreach ($record->translations as $translation) {
                                    $newTranslation = $translation->replicate();
                                    $newTranslation->slider_id = $newSlider->id;
                                    $newTranslation->save();
                                }

                                foreach (['slider_images', 'slider_backgrounds'] as $collection) {
                                    if ($media = $record->getFirstMedia($collection)) {
                                        $media->copy($newSlider, $collection);
                                    }
                                }
                            });

                            Notification::make()
                                ->title(__('translations.slider_duplicated'))
                                ->success()
                                ->send();
                        }),
                    Action::make('export_slider')
                        ->label(__('translations.export'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function ($record): ?StreamedResponse {
                            $slider = $record instanceof Slider
                                ? $record->loadMissing('translations')
                                : Slider::query()->with('translations')->first();

                            if (! $slider instanceof Slider) {
                                Notification::make()
                                    ->title(__('translations.no_sliders'))
                                    ->warning()
                                    ->send();

                                return null;
                            }

                            $payload = self::formatSliderForExport($slider);

                            return response()->streamDownload(
                                static function () use ($payload): void {
                                    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                },
                                sprintf('slider-%s.json', $slider->id),
                                [
                                    'Content-Type' => 'application/json',
                                ],
                            );
                        }),
                    DeleteAction::make(),
                ])->label('Actions'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->paginated([10, 25, 50]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function formatSliderForExport(Slider $slider): array
    {
        $slider->loadMissing('translations');

        $sliderData = Arr::except($slider->attributesToArray(), ['created_at', 'updated_at']);

        $translations = $slider->translations
            ->map(static fn ($translation) => Arr::except($translation->attributesToArray(), ['id', 'slider_id', 'created_at', 'updated_at']))
            ->values()
            ->all();

        return [
            'slider'       => $sliderData,
            'translations' => $translations,
            'media'        => [
                'slider_images'      => $slider->getFirstMedia('slider_images')?->getUrl(),
                'slider_backgrounds' => $slider->getFirstMedia('slider_backgrounds')?->getUrl(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>             $payload
     * @return array<int, array<string, mixed>>
     */
    private static function normaliseImportedPayload(array $payload): array
    {
        if (isset($payload['slider']) || isset($payload['translations'])) {
            return [
                [
                    'slider'       => Arr::except($payload['slider'] ?? $payload, ['translations', 'media']),
                    'translations' => $payload['translations'] ?? [],
                ],
            ];
        }

        if (isset($payload['sliders']) && is_array($payload['sliders'])) {
            return array_map(
                static fn ($item) => [
                    'slider'       => Arr::except(($item['slider'] ?? $item) ?? [], ['translations', 'media']),
                    'translations' => $item['translations'] ?? ($item['slider']['translations'] ?? []),
                ],
                array_filter($payload['sliders'], 'is_array'),
            );
        }

        if (Arr::isList($payload)) {
            return array_map(
                static fn ($item) => [
                    'slider'       => Arr::except(($item['slider'] ?? $item) ?? [], ['translations', 'media']),
                    'translations' => $item['translations'] ?? ($item['slider']['translations'] ?? []),
                ],
                array_filter($payload, 'is_array'),
            );
        }

        return [
            [
                'slider'       => Arr::except($payload, ['translations', 'media']),
                'translations' => $payload['translations'] ?? [],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function persistImportedSlider(array $payload, bool $updateExisting): ?Slider
    {
        if (! isset($payload['slider']) || ! is_array($payload['slider'])) {
            return null;
        }

        $sliderAttributes = Arr::except($payload['slider'], ['created_at', 'updated_at', 'translations', 'media']);

        if (! isset($sliderAttributes['sort_order'])) {
            $sliderAttributes['sort_order'] = ((int) (Slider::max('sort_order') ?? 0)) + 1;
        }

        return DB::transaction(function () use ($payload, $sliderAttributes, $updateExisting): Slider {
            $existing = null;

            if ($updateExisting) {
                if (isset($payload['slider']['id'])) {
                    $existing = Slider::find($payload['slider']['id']);
                }

                if (! $existing && isset($sliderAttributes['title'])) {
                    $existing = Slider::where('title', $sliderAttributes['title'])->first();
                }
            }

            if ($existing instanceof Slider) {
                $existing->update($sliderAttributes);
                $slider = $existing;
            } else {
                $slider = Slider::create($sliderAttributes);
            }

            $translations = $payload['translations'] ?? [];

            if (is_array($translations)) {
                foreach ($translations as $translation) {
                    if (! is_array($translation) || ! isset($translation['locale'])) {
                        continue;
                    }

                    $translationData = Arr::except($translation, ['id', 'slider_id', 'created_at', 'updated_at']);

                    $slider->translations()->updateOrCreate(
                        ['locale' => $translation['locale']],
                        $translationData,
                    );
                }
            }

            return $slider;
        });
    }
}
