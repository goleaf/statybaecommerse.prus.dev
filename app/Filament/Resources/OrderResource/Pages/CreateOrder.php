<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $serviceAttachments = [];

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (UniqueConstraintViolationException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'orders.number')) {
                throw ValidationException::withMessages([
                    'data.number' => __('validation.unique', [
                        'attribute' => __('messages.order_number'),
                    ]),
                ]);
            }

            throw $exception;
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! is_numeric($data['user_id'] ?? null)) {
            $requestedUserId = request()->integer('user_id');

            if ($requestedUserId > 0) {
                $data['user_id'] = $requestedUserId;
            }
        }

        $this->serviceAttachments = $data['services'] ?? [];

        unset($data['services']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        if (! $record instanceof Order) {
            return;
        }

        $this->attachServices($record);
    }

    private function attachServices(Order $order): void
    {
        $payload = [];

        foreach ($this->serviceAttachments as $service) {
            $serviceId = (int) ($service['service_id'] ?? 0);

            if ($serviceId <= 0) {
                continue;
            }

            $payload[$serviceId] = [
                'quantity' => max(1, (int) ($service['quantity'] ?? 1)),
                'price'    => (float) ($service['price'] ?? 0),
            ];
        }

        if ($payload === []) {
            return;
        }

        $order->services()->syncWithoutDetaching($payload);
    }

    protected function getRedirectUrl(): string
    {
        $redirectUrl = request()->query('redirect');

        if (is_string($redirectUrl) && $redirectUrl !== '') {
            return $redirectUrl;
        }

        return parent::getRedirectUrl();
    }
}
