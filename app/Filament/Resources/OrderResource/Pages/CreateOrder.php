<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Contracts\DocumentServiceContract;
use App\Filament\Resources\OrderResource;
use App\Models\DocumentTemplate;
use App\Models\Order;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $serviceAttachments = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
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
        $this->generateDocuments($record);
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

    private function generateDocuments(Order $order): void
    {
        $templates = DocumentTemplate::query()
            ->active()
            ->orderedByName()
            ->get();

        if ($templates->isEmpty()) {
            return;
        }

        $documentService = app(DocumentServiceContract::class);

        $variables = array_merge(
            $documentService->extractVariablesFromModel($order),
            $order->getDocumentVariables()
        );

        foreach ($templates as $template) {
            $document = $documentService->generateDocument(
                template: $template,
                relatedModel: $order,
                variables: $variables
            );

            $documentService->generatePdf($document);
        }
    }
}
