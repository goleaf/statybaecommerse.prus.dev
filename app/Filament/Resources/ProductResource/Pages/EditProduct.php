<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Support\Products\ProductPublicationGuard;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        if ($record instanceof Product) {
            ProductPublicationGuard::ensureEditPublishability($data, $record);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_frontend')
                ->label(__('admin.actions.view') . ' ' . __('messages.frontend'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => $this->resolveFrontendProductUrl())
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->hasFrontendProductRoute()),
            Actions\DeleteAction::make(),
        ];
    }

    private function hasFrontendProductRoute(): bool
    {
        return Route::has('frontend.products.show')
            || Route::has('frontend.products.show')
            || Route::has('products.show');
    }

    private function resolveFrontendProductUrl(): string
    {
        $record = $this->getRecord();

        if (! $record instanceof Product) {
            return '#';
        }

        $productRouteKey = (string) $record->getRouteKey();

        if (Route::has('frontend.products.show')) {
            return route('frontend.products.show', [
                'product' => $productRouteKey,
            ]);
        }

        if (Route::has('frontend.products.show')) {
            return route('frontend.products.show', ['product' => $productRouteKey]);
        }

        if (Route::has('products.show')) {
            return route('products.show', ['product' => $productRouteKey]);
        }

        return '#';
    }
}
