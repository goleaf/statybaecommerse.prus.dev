<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\ImportExport\ProviderRegistry;
use App\Support\Storage\SecureStorage;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;

final class DataImportExport extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.data-import-export';

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'heroicon-o-arrow-down-tray';
    }

    public static function getNavigationLabel(): string
    {
        return __('translations.data_import_export');
    }

    public function getTitle(): string|Htmlable
    {
        return __('translations.data_import_export');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'provider' => 'xml',
            'only' => 'all',
            'downloadImages' => true,
            'exportPath' => 'catalog-export.xml',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('translations.data_import_export'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('provider')
                                ->label(__('translations.provider'))
                                ->options(collect(ProviderRegistry::providers())->mapWithKeys(fn ($p, $k) => [$k => $p->label()])->all())
                                ->required()
                                ->live(),
                            Select::make('only')
                                ->label(__('translations.scope'))
                                ->options(['all' => 'all', 'categories' => 'categories', 'products' => 'products'])
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            Section::make(__('translations.import'))
                                ->schema([
                                    FileUpload::make('file')
                                        ->label(__('translations.xml_file'))
                                        ->acceptedFileTypes(['application/xml', 'text/xml'])
                                        ->disk(SecureStorage::disk())
                                        ->directory('imports')
                                        ->required(),
                                    Toggle::make('downloadImages')
                                        ->label(__('translations.download_images'))
                                        ->default(true),
                                ])->columnSpan(1),
                            Section::make(__('translations.export'))
                                ->schema([
                                    TextInput::make('exportPath')
                                        ->label(__('translations.export_path'))
                                        ->default('catalog-export.xml')
                                        ->required(),
                                ])->columnSpan(1),
                        ]),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function importAction(): Action
    {
        return Action::make('import')
            ->label(__('translations.import'))
            ->action(function (): void {
                $data = $this->form->getState();
                $provider = ProviderRegistry::get($data['provider'] ?? 'xml');
                if (! $provider) {
                    Notification::make()
                        ->title(__('translations.provider_not_found'))
                        ->danger()
                        ->send();
                    return;
                }
                $path = $data['file'];
                if (! $path) {
                    Notification::make()
                        ->title(__('translations.file_missing'))
                        ->danger()
                        ->send();
                    return;
                }
                $abs = Storage::disk(SecureStorage::disk())->path($path);
                $res = $provider->import($abs, ['only' => $data['only'] ?? 'all', 'download_images' => (bool) ($data['downloadImages'] ?? true)]);
                
                Notification::make()
                    ->title(__('translations.import_finished'))
                    ->body(__('messages.created') . ': ' . ($res['categories']['created'] + $res['products']['created']))
                    ->success()
                    ->send();
            });
    }

    public function exportAction(): Action
    {
        return Action::make('export')
            ->label(__('translations.export'))
            ->action(function (): void {
                $data = $this->form->getState();
                $provider = ProviderRegistry::get($data['provider'] ?? 'xml');
                if (! $provider) {
                    Notification::make()
                        ->title(__('translations.provider_not_found'))
                        ->danger()
                        ->send();
                    return;
                }
                $targetPath = $data['exportPath'] ?? 'catalog-export.xml';
                $provider->export(
                    Storage::disk(SecureStorage::disk())->path($targetPath),
                    ['only' => $data['only'] ?? 'all']
                );
                Notification::make()
                    ->title(__('translations.export_finished'))
                    ->success()
                    ->send();
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->importAction(),
            $this->exportAction(),
        ];
    }
}