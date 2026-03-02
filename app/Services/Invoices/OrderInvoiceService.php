<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Models\AdminUser;
use App\Models\File;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\User;
use App\Notifications\AdminNotification;
use App\Support\Storage\SecureStorage;
use BackedEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final readonly class OrderInvoiceService
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_INVOICE_TYPES = ['sf', 'psf', 'isf', 'ipsf', 'ksf', 'kpsf'];

    public function __construct(private SaskaitaInvoiceClient $client) {}

    /**
     * @return array<string, string>
     */
    public static function invoiceTypeOptions(): array
    {
        return collect(self::ALLOWED_INVOICE_TYPES)
            ->mapWithKeys(static fn (string $type): array => [$type => __("enums.invoice_type.{$type}")])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function allowedInvoiceTypes(): array
    {
        return self::ALLOWED_INVOICE_TYPES;
    }

    /**
     * @throws Throwable
     */
    public function generateForOrder(
        Order $order,
        bool $force = false,
        string $mode = OrderInvoice::MODE_AUTO,
        ?string $invoiceType = null
    ): ?OrderInvoice {
        if (! $this->isEnabled()) {
            return null;
        }

        if (! $this->shouldGenerateForMode($order, $mode)) {
            return null;
        }

        $order->loadMissing(['items', 'services', 'user']);
        $requestedInvoiceType = $this->resolveInvoiceType($invoiceType);
        $preparedContext = $this->prepareInvoiceContext($order);
        $invoiceWasCreated = false;
        $invoice = null;

        try {
            /** @var OrderInvoice|null $invoice */
            $invoice = DB::transaction(function () use ($order, $force, $mode, &$invoiceWasCreated): ?OrderInvoice {
                $current = OrderInvoice::query()
                    ->where('order_id', $order->getKey())
                    ->where('is_current', true)
                    ->latest('id')
                    ->first();

                if (
                    ! $force
                    && $current instanceof OrderInvoice
                    && in_array($current->status, [OrderInvoice::STATUS_PENDING, OrderInvoice::STATUS_READY], true)
                ) {
                    return $current;
                }

                if ($current instanceof OrderInvoice) {
                    $current->forceFill(['is_current' => false])->save();
                }

                $invoice = OrderInvoice::query()->create([
                    'order_id'        => $order->getKey(),
                    'status'          => OrderInvoice::STATUS_PENDING,
                    'is_current'      => true,
                    'generation_mode' => $mode,
                ]);

                $invoiceWasCreated = true;

                return $invoice;
            });

            if (! $invoice instanceof OrderInvoice) {
                return null;
            }

            if (! $invoiceWasCreated) {
                return $invoice;
            }

            $payload = $this->buildInitiatePayload($order, $requestedInvoiceType, $preparedContext);
            $invoice->forceFill([
                'provider_payload' => [
                    'initiate_payload'       => $payload,
                    'requested_invoice_type' => $requestedInvoiceType,
                    'prepared_context'       => [
                        'persisted' => (bool) ($preparedContext['persisted'] ?? false),
                    ],
                    'attempted_at' => now()->toIso8601String(),
                ],
            ])->save();

            $pdfBinary = $this->client->initiateInvoice($payload);
            $invoiceData = $this->findInvoiceFromList($order, $this->orderMarker($order));
            $resolvedInvoiceData = is_array($invoiceData) ? $invoiceData : [];

            $externalInvoiceId = $this->nullableString($resolvedInvoiceData['id'] ?? $resolvedInvoiceData['invoice_id'] ?? null);
            $fullNumber = $this->nullableString($resolvedInvoiceData['full_number'] ?? null) ?? '';
            $providerInvoiceType = $this->nullableString($resolvedInvoiceData['type'] ?? null);
            $storedInvoiceType = $requestedInvoiceType;
            $filePath = $this->storePdf($order, $invoice, $externalInvoiceId, $fullNumber, $pdfBinary);
            $file = $this->storeFileRecord($order, $filePath, $pdfBinary, $externalInvoiceId, $fullNumber, $storedInvoiceType);

            $invoice->forceFill([
                'file_id'             => $file->getKey(),
                'external_invoice_id' => $externalInvoiceId,
                'invoice_series'      => $this->nullableString($resolvedInvoiceData['series'] ?? null),
                'invoice_number'      => $this->nullableString($resolvedInvoiceData['number'] ?? null),
                'full_number'         => $this->nullableString($resolvedInvoiceData['full_number'] ?? null),
                'invoice_type'        => $storedInvoiceType,
                'status'              => OrderInvoice::STATUS_READY,
                'provider_payload'    => [
                    'initiate_payload'       => $payload,
                    'invoice'                => $resolvedInvoiceData,
                    'requested_invoice_type' => $requestedInvoiceType,
                    'provider_invoice_type'  => $providerInvoiceType,
                    'prepared_context'       => [
                        'persisted' => (bool) ($preparedContext['persisted'] ?? false),
                    ],
                    'attempted_at' => now()->toIso8601String(),
                ],
                'error_message' => null,
                'generated_at'  => now(),
                'failed_at'     => null,
            ])->save();

            return $invoice->refresh();
        } catch (Throwable $exception) {
            if ($invoice instanceof OrderInvoice && $invoiceWasCreated) {
                $this->markInvoiceFailed($invoice, $exception);
            } else {
                $this->markLatestPendingInvoiceFailed($order, $exception);
            }

            $this->notifyAdminsAboutFailure($order, $exception);
            throw $exception;
        }
    }

    public function markCurrentInvoiceAsRefunded(Order $order): void
    {
        OrderInvoice::query()
            ->where('order_id', $order->getKey())
            ->where('is_current', true)
            ->whereIn('status', [OrderInvoice::STATUS_PENDING, OrderInvoice::STATUS_READY])
            ->update([
                'status'    => OrderInvoice::STATUS_REFUNDED,
                'failed_at' => null,
            ]);
    }

    public function shouldGenerateForPaymentStatus(Order $order): bool
    {
        $status = $this->normalizePaymentStatus($order->payment_status);

        return in_array($status, ['paid', 'captured', 'settled'], true);
    }

    public function shouldMarkRefunded(Order $order): bool
    {
        $status = $this->normalizePaymentStatus($order->payment_status);

        return in_array($status, ['partially_refunded', 'refunded'], true);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInitiatePayload(Order $order, string $invoiceType, array $preparedContext): array
    {
        /** @var array<string, mixed> $billing */
        $billing = is_array($preparedContext['billing'] ?? null) ? $preparedContext['billing'] : [];
        /** @var array<string, mixed> $shipping */
        $shipping = is_array($preparedContext['shipping'] ?? null) ? $preparedContext['shipping'] : [];
        $buyerName = trim((string) ($preparedContext['buyer_name'] ?? ''));
        $buyerEmail = trim((string) ($preparedContext['buyer_email'] ?? ''));

        if ($buyerName === '') {
            $buyerName = $this->resolveBuyerName($billing, $shipping, $order);
        }
        if ($buyerEmail === '') {
            $buyerEmail = $this->resolveBuyerEmail($billing, $shipping, $order);
        }

        if ($buyerEmail === '') {
            throw new RuntimeException(
                __('messages.invoice_missing_recipient_email_for_order', ['order' => (string) $order->number])
            );
        }

        if (filter_var($buyerEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException(
                __('messages.invoice_invalid_recipient_email_for_order', [
                    'order' => (string) $order->number,
                    'email' => $buyerEmail,
                ])
            );
        }

        $products = $this->buildProductsPayload($order);

        if ($products === []) {
            throw new RuntimeException(
                __('messages.invoice_missing_billable_items_for_order', ['order' => (string) $order->number])
            );
        }

        $companyName = $this->nullableString($billing['company'] ?? $billing['company_name'] ?? null);
        $companyCode = $this->nullableString($billing['company_code'] ?? $billing['company_vat'] ?? null);
        $vatCode = $this->nullableString($billing['vat_code'] ?? $billing['company_vat'] ?? null);
        $isJuridical = $companyName !== null || $companyCode !== null || $vatCode !== null;

        $billingName = $companyName ?? $buyerName;
        $deliveryName = $this->resolveAddressName($shipping)
            ?? $this->resolveAddressName($billing)
            ?? $buyerName;

        $marker = $this->orderMarker($order);
        $notes = trim($this->resolveNotes($order->notes));
        $notes = trim($notes === '' ? $marker : $notes . ' ' . $marker);

        $billingPayload = [
            'name'        => $billingName,
            'isJuridical' => $isJuridical,
        ];
        $this->addOptionalStringField($billingPayload, 'company_code', $companyCode);
        $this->addOptionalStringField($billingPayload, 'vat_code', $vatCode);
        $this->addOptionalStringField(
            $billingPayload,
            'address',
            $this->nullableString($billing['address_line_1'] ?? $billing['address'] ?? $billing['street'] ?? null)
        );
        $this->addOptionalStringField($billingPayload, 'city', $this->nullableString($billing['city'] ?? null));
        $this->addOptionalStringField(
            $billingPayload,
            'post',
            $this->nullableString($billing['postal_code'] ?? $billing['zip'] ?? null)
        );
        if ($vatCode !== null) {
            $billingPayload['isVatPayer'] = true;
        }

        $deliveryPayload = [
            'name' => $deliveryName,
        ];
        $this->addOptionalStringField(
            $deliveryPayload,
            'address',
            $this->nullableString($shipping['address_line_1'] ?? $shipping['address'] ?? $shipping['street'] ?? null)
        );
        $this->addOptionalStringField($deliveryPayload, 'city', $this->nullableString($shipping['city'] ?? null));
        $this->addOptionalStringField(
            $deliveryPayload,
            'post',
            $this->nullableString($shipping['postal_code'] ?? $shipping['zip'] ?? null)
        );

        $payerPayload = [
            'name'  => $buyerName,
            'email' => $buyerEmail,
        ];

        return [
            'api_token'      => $this->apiToken(),
            'invoice_type'   => $invoiceType,
            'notes'          => $notes,
            'total_chipping' => round(max((float) ($order->shipping_amount ?? 0), 0), 2),
            'total_discount' => round(max((float) ($order->discount_amount ?? 0), 0), 2),
            'total_amount'   => round(max((float) ($order->total ?? 0), 0), 2),
            'products'       => $products,
            'billing'        => $billingPayload,
            'delivery'       => $deliveryPayload,
            'payer'          => $payerPayload,
            'seller'         => [
                'website' => $this->resolveSellerWebsite(),
            ],
        ];
    }

    /**
     * @return array{
     *     billing: array<string, mixed>,
     *     shipping: array<string, mixed>,
     *     buyer_name: string,
     *     buyer_email: string,
     *     persisted: bool
     * }
     */
    private function prepareInvoiceContext(Order $order): array
    {
        $billing = $this->normalizeAddress($order->billing_address);
        $shipping = $this->normalizeAddress($order->shipping_address);

        $buyerName = $this->resolveBuyerName($billing, $shipping, $order);
        $buyerEmail = $this->resolveBuyerEmail($billing, $shipping, $order);

        $preparedBilling = $this->normalizeInvoiceAddress($billing, $shipping, $order, $buyerName, $buyerEmail);
        $preparedShipping = $this->normalizeInvoiceAddress($shipping, $billing, $order, $buyerName, $buyerEmail);
        $persisted = false;

        if ($this->addressesDiffer($billing, $preparedBilling) || $this->addressesDiffer($shipping, $preparedShipping)) {
            $order->forceFill([
                'billing_address'  => $preparedBilling,
                'shipping_address' => $preparedShipping,
            ])->save();

            $persisted = true;
        }

        return [
            'billing'     => $preparedBilling,
            'shipping'    => $preparedShipping,
            'buyer_name'  => $buyerName,
            'buyer_email' => $buyerEmail,
            'persisted'   => $persisted,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeInvoiceAddress(
        array $primary,
        array $fallback,
        Order $order,
        string $buyerName,
        string $buyerEmail
    ): array {
        $normalized = $this->cleanAddressForPersistence($primary);

        $firstName = $this->nullableString($primary['first_name'] ?? $fallback['first_name'] ?? $order->user?->first_name ?? null);
        $lastName = $this->nullableString($primary['last_name'] ?? $fallback['last_name'] ?? $order->user?->last_name ?? null);
        $fullName = $this->resolveAddressName($primary)
            ?? $this->resolveAddressName($fallback)
            ?? $this->nullableString($order->user?->name)
            ?? $buyerName;
        $email = $this->nullableString($primary['email'] ?? $fallback['email'] ?? null) ?? $buyerEmail;
        $phone = $this->nullableString($primary['phone'] ?? $fallback['phone'] ?? $order->user?->phone_number ?? $order->user?->phone ?? null);
        $address = $this->nullableString(
            $primary['address'] ?? $primary['street'] ?? $primary['address_line_1'] ?? $fallback['address'] ?? $fallback['street'] ?? $fallback['address_line_1'] ?? null
        );
        $city = $this->nullableString($primary['city'] ?? $fallback['city'] ?? null);
        $postalCode = $this->nullableString(
            $primary['postal_code'] ?? $primary['zip'] ?? $fallback['postal_code'] ?? $fallback['zip'] ?? null
        );

        $this->addOptionalStringField($normalized, 'first_name', $firstName);
        $this->addOptionalStringField($normalized, 'last_name', $lastName);
        $this->addOptionalStringField($normalized, 'name', $fullName);
        $this->addOptionalStringField($normalized, 'full_name', $fullName);
        $this->addOptionalStringField($normalized, 'email', $email);
        $this->addOptionalStringField($normalized, 'phone', $phone);
        $this->addOptionalStringField($normalized, 'address', $address);
        $this->addOptionalStringField($normalized, 'street', $address);
        $this->addOptionalStringField($normalized, 'address_line_1', $address);
        $this->addOptionalStringField($normalized, 'city', $city);
        $this->addOptionalStringField($normalized, 'postal_code', $postalCode);
        $this->addOptionalStringField($normalized, 'zip', $postalCode);

        $this->addOptionalStringField(
            $normalized,
            'company',
            $this->nullableString($primary['company'] ?? $fallback['company'] ?? $primary['company_name'] ?? $fallback['company_name'] ?? null)
        );
        $this->addOptionalStringField(
            $normalized,
            'company_name',
            $this->nullableString($primary['company_name'] ?? $fallback['company_name'] ?? $primary['company'] ?? $fallback['company'] ?? null)
        );
        $this->addOptionalStringField(
            $normalized,
            'company_code',
            $this->nullableString($primary['company_code'] ?? $fallback['company_code'] ?? null)
        );
        $this->addOptionalStringField(
            $normalized,
            'vat_code',
            $this->nullableString($primary['vat_code'] ?? $fallback['vat_code'] ?? $primary['company_vat'] ?? $fallback['company_vat'] ?? null)
        );
        $this->addOptionalStringField(
            $normalized,
            'company_vat',
            $this->nullableString($primary['company_vat'] ?? $fallback['company_vat'] ?? $primary['vat_code'] ?? $fallback['vat_code'] ?? null)
        );

        return $this->cleanAddressForPersistence($normalized);
    }

    /**
     * @return array<string, mixed>
     */
    private function cleanAddressForPersistence(array $address): array
    {
        $clean = [];

        foreach ($address as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if (! is_scalar($value)) {
                continue;
            }

            if (is_string($value)) {
                $normalized = trim($value);

                if ($normalized === '') {
                    continue;
                }

                $clean[$key] = $normalized;

                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    private function addressesDiffer(array $original, array $prepared): bool
    {
        $left = $this->cleanAddressForPersistence($original);
        $right = $this->cleanAddressForPersistence($prepared);

        $this->sortRecursive($left);
        $this->sortRecursive($right);

        return $left !== $right;
    }

    /**
     * @param array<string, mixed> $value
     */
    private function sortRecursive(array &$value): void
    {
        foreach ($value as &$child) {
            if (is_array($child)) {
                $this->sortRecursive($child);
            }
        }

        ksort($value);
    }

    /**
     * @return array<int, array{description: string, quantity: int|float, price: float}>
     */
    private function buildProductsPayload(Order $order): array
    {
        $itemRows = $order->items
            ->map(function ($item): array {
                $name = trim((string) ($item->name ?? ''));
                if ($name === '') {
                    $name = __('messages.invoice_order_item_fallback_name');
                }

                $quantity = max(1, (int) ($item->quantity ?? 1));
                $price = (float) ($item->unit_price ?? $item->price ?? 0);

                if ($price <= 0) {
                    $price = (float) ($item->total ?? 0) / $quantity;
                }

                return [
                    'description' => $name,
                    'quantity'    => $quantity,
                    'price'       => round(max($price, 0), 2),
                ];
            })
            ->values();

        $serviceRows = $order->services
            ->map(function ($service): array {
                $name = trim((string) ($service->name ?? ''));
                if ($name === '') {
                    $name = __('messages.invoice_order_item_fallback_name');
                }

                $quantityRaw = $service->pivot->quantity ?? 1;
                $quantity = is_numeric($quantityRaw) ? max((float) $quantityRaw, 1.0) : 1.0;
                $price = (float) ($service->pivot->price ?? $service->price ?? 0);

                return [
                    'description' => $name,
                    'quantity'    => fmod($quantity, 1.0) === 0.0 ? (int) $quantity : $quantity,
                    'price'       => round(max($price, 0), 2),
                ];
            })
            ->values();

        /** @var array<int, array{description: string, quantity: int|float, price: float}> $products */
        $products = $itemRows
            ->concat($serviceRows)
            ->filter(static fn (array $item): bool => $item['price'] > 0 && (float) $item['quantity'] > 0)
            ->values()
            ->all();

        if ($products === [] && (float) ($order->total ?? 0) > 0) {
            $products[] = [
                'description' => __('messages.invoice_order_item_fallback_name'),
                'quantity'    => 1,
                'price'       => round((float) $order->total, 2),
            ];
        }

        return $products;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findInvoiceFromList(Order $order, ?string $marker): ?array
    {
        $invoices = collect($this->client->listInvoices($this->apiToken()))
            ->filter(static fn ($row): bool => is_array($row))
            ->values();

        if ($invoices->isEmpty()) {
            return null;
        }

        $markerMatch = $invoices
            ->sortByDesc(
                fn (array $row): string => sprintf('%s|%s', (string) ($row['from_date'] ?? ''), (string) ($row['id'] ?? ''))
            )
            ->first(function (array $row) use ($marker): bool {
                if ($marker === null || $marker === '') {
                    return false;
                }

                $notes = (string) ($row['notes'] ?? '');

                return $notes !== '' && str_contains($notes, $marker);
            });

        if (is_array($markerMatch)) {
            return $markerMatch;
        }

        $total = number_format((float) ($order->total ?? 0), 2, '.', '');
        $billing = $this->normalizeAddress($order->billing_address);
        $shipping = $this->normalizeAddress($order->shipping_address);
        $candidateEmail = strtolower(trim((string) ($billing['email'] ?? $shipping['email'] ?? $order->user?->email ?? '')));

        $fallback = $invoices
            ->sortByDesc(
                fn (array $row): string => sprintf('%s|%s', (string) ($row['from_date'] ?? ''), (string) ($row['id'] ?? ''))
            )
            ->first(function (array $row) use ($candidateEmail, $total): bool {
                $rowTotal = number_format((float) ($row['total_amount'] ?? 0), 2, '.', '');
                $rowEmail = strtolower(trim((string) ($row['payer_email'] ?? '')));

                return $rowTotal === $total && ($candidateEmail === '' || $rowEmail === $candidateEmail);
            });

        return is_array($fallback) ? $fallback : null;
    }

    private function storePdf(
        Order $order,
        OrderInvoice $invoice,
        ?string $externalInvoiceId,
        ?string $fullNumber,
        string $binary
    ): string
    {
        $baseName = trim((string) ($fullNumber ?? ''));
        if ($baseName === '') {
            $baseName = trim((string) ($externalInvoiceId ?? ''));
        }
        if ($baseName === '') {
            $baseName = 'invoice';
        }

        $slug = Str::slug($baseName, '-');

        if ($slug === '') {
            $slug = 'invoice';
        }

        $orderNumber = Str::slug((string) ($order->number ?? $order->getKey()), '-');
        $invoiceKey = (int) $invoice->getKey();
        $uniqueSuffix = $invoiceKey > 0 ? (string) $invoiceKey : Str::lower(Str::random(8));

        $filename = sprintf('invoice-%s-%s-%s.pdf', $orderNumber, $slug, $uniqueSuffix);
        $path = sprintf('orders/%d/invoices/%s', $order->getKey(), $filename);

        Storage::disk(SecureStorage::disk())->put($path, $binary);

        return $path;
    }

    private function storeFileRecord(
        Order $order,
        string $path,
        string $binary,
        ?string $externalInvoiceId,
        ?string $fullNumber,
        ?string $invoiceType
    ): File {
        $filename = basename($path);
        $uploaderId = $this->resolveUploaderId($order);
        $metadata = array_filter([
            'source'              => 'saskaita',
            'external_invoice_id' => $externalInvoiceId,
            'full_number'         => $fullNumber,
            'invoice_type'        => $invoiceType,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        return File::query()->create([
            'name'          => $filename,
            'original_name' => $filename,
            'path'          => $path,
            'disk'          => SecureStorage::disk(),
            'mime_type'     => 'application/pdf',
            'size'          => strlen($binary),
            'hash'          => hash('sha256', $binary),
            'fileable_type' => Order::class,
            'fileable_id'   => $order->getKey(),
            'uploaded_by'   => $uploaderId,
            'metadata'      => $metadata,
        ]);
    }

    private function resolveUploaderId(Order $order): int
    {
        $userId = $order->user_id;
        if (is_numeric($userId) && (int) $userId > 0) {
            return (int) $userId;
        }

        $fallbackUserId = User::query()->withoutGlobalScopes()->value('id');
        if (is_numeric($fallbackUserId) && (int) $fallbackUserId > 0) {
            return (int) $fallbackUserId;
        }

        throw new RuntimeException(__('messages.invoice_unable_to_resolve_uploader_account'));
    }

    /**
     * @param  array<string, mixed>|string|null $address
     * @return array<string, mixed>
     */
    private function normalizeAddress(array|string|null $address): array
    {
        if (is_array($address)) {
            return $address;
        }

        if (is_string($address) && $address !== '') {
            $decoded = json_decode($address, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function resolveBuyerName(array $billing, array $shipping, Order $order): string
    {
        return $this->resolveAddressName($billing)
            ?? $this->resolveAddressName($shipping)
            ?? $this->nullableString($order->user?->name)
            ?? __('messages.invoice_default_buyer_name_for_order', ['order' => (string) $order->number]);
    }

    private function resolveBuyerEmail(array $billing, array $shipping, Order $order): string
    {
        return $this->nullableString($billing['email'] ?? null)
            ?? $this->nullableString($shipping['email'] ?? null)
            ?? $this->nullableString($order->user?->email)
            ?? '';
    }

    private function resolveAddressName(array $address): ?string
    {
        $fullName = $this->nullableString($address['full_name'] ?? $address['name'] ?? null);
        if ($fullName !== null) {
            return $fullName;
        }

        $firstName = $this->nullableString($address['first_name'] ?? null);
        $lastName = $this->nullableString($address['last_name'] ?? null);
        $combined = trim(implode(' ', array_filter([$firstName, $lastName], static fn (?string $part): bool => $part !== null)));

        return $combined !== '' ? $combined : null;
    }

    private function resolveNotes(mixed $notes): string
    {
        if (is_string($notes)) {
            return trim($notes);
        }

        if (is_array($notes)) {
            $locale = app()->getLocale();
            $localizedNote = $notes[$locale] ?? null;

            if (is_scalar($localizedNote) && trim((string) $localizedNote) !== '') {
                return trim((string) $localizedNote);
            }

            foreach ($notes as $note) {
                if (is_scalar($note) && trim((string) $note) !== '') {
                    return trim((string) $note);
                }
            }
        }

        if (is_scalar($notes)) {
            return trim((string) $notes);
        }

        return '';
    }

    private function notifyAdminsAboutFailure(Order $order, Throwable $exception): void
    {
        Log::error(__('messages.order_invoice_generation_failed_log'), [
            'order_id'     => $order->getKey(),
            'order_number' => $order->number,
            'message'      => $exception->getMessage(),
        ]);

        $title = __('messages.order_invoice_generation_failed_title');
        $body = __('messages.order_invoice_generation_failed_body', [
            'order' => (string) ($order->number ?? $order->getKey()),
            'error' => Str::limit($exception->getMessage(), 200),
        ]);

        AdminUser::query()
            ->withoutGlobalScopes()
            ->cursor()
            ->each(function (AdminUser $admin) use ($title, $body): void {
                try {
                    $admin->notify(new AdminNotification($title, $body, 'danger'));
                } catch (Throwable $notificationException) {
                    Log::warning(__('messages.order_invoice_generation_admin_notify_failed'), [
                        'admin_id' => $admin->getKey(),
                        'message'  => $notificationException->getMessage(),
                    ]);
                }
            });
    }

    private function markInvoiceFailed(OrderInvoice $invoice, Throwable $exception): void
    {
        $providerPayload = is_array($invoice->provider_payload) ? $invoice->provider_payload : [];
        $providerPayload['failure'] = [
            'failed_at' => now()->toIso8601String(),
            'message'   => Str::limit($exception->getMessage(), 1000),
        ];

        $invoice->forceFill([
            'status'           => OrderInvoice::STATUS_FAILED,
            'failed_at'        => now(),
            'error_message'    => Str::limit($exception->getMessage(), 1000),
            'provider_payload' => $providerPayload,
        ])->save();
    }

    private function markLatestPendingInvoiceFailed(Order $order, Throwable $exception): void
    {
        $invoice = OrderInvoice::query()
            ->where('order_id', $order->getKey())
            ->where('is_current', true)
            ->where('status', OrderInvoice::STATUS_PENDING)
            ->latest('id')
            ->first();

        if (! $invoice instanceof OrderInvoice) {
            return;
        }

        $this->markInvoiceFailed($invoice, $exception);
    }

    private function normalizePaymentStatus(mixed $status): string
    {
        if ($status instanceof BackedEnum) {
            return strtolower((string) $status->value);
        }

        return strtolower((string) $status);
    }

    private function shouldGenerateForMode(Order $order, string $mode): bool
    {
        if ($mode === OrderInvoice::MODE_MANUAL) {
            return true;
        }

        return $this->shouldGenerateForPaymentStatus($order);
    }

    private function orderMarker(Order $order): string
    {
        return sprintf('order_number:%s;order_id:%d', (string) $order->number, (int) $order->getKey());
    }

    private function apiToken(): string
    {
        $token = trim((string) config('invoices.api_token', ''));
        if ($token === '') {
            throw new RuntimeException(__('messages.invoice_api_token_not_configured'));
        }

        return $token;
    }

    private function isEnabled(): bool
    {
        return (bool) config('invoices.enabled', false);
    }

    private function resolveInvoiceType(?string $invoiceType): string
    {
        $candidate = strtolower(trim((string) ($invoiceType ?? '')));
        if (in_array($candidate, self::ALLOWED_INVOICE_TYPES, true)) {
            return $candidate;
        }

        $configured = strtolower(trim((string) config('invoices.default_invoice_type', 'sf')));

        return in_array($configured, self::ALLOWED_INVOICE_TYPES, true) ? $configured : 'sf';
    }

    private function resolveSellerWebsite(): string
    {
        $candidateUrls = [
            $this->nullableString(config('invoices.seller_website')),
            $this->nullableString(config('app.url')),
        ];

        foreach ($candidateUrls as $candidateUrl) {
            $normalized = $this->normalizeAbsoluteUrl($candidateUrl);
            if ($normalized !== null && $this->isProviderSafeWebsite($normalized)) {
                return $normalized;
            }
        }

        return 'https://example.com';
    }

    private function normalizeAbsoluteUrl(?string $candidate): ?string
    {
        if ($candidate === null) {
            return null;
        }

        $normalized = trim($candidate);
        if ($normalized === '') {
            return null;
        }

        if (! str_starts_with(strtolower($normalized), 'http://') && ! str_starts_with(strtolower($normalized), 'https://')) {
            $normalized = 'https://' . ltrim($normalized, '/');
        }

        return filter_var($normalized, FILTER_VALIDATE_URL) !== false ? $normalized : null;
    }

    private function isProviderSafeWebsite(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! is_string($host) || trim($host) === '') {
            return false;
        }

        $normalizedHost = strtolower(trim($host));

        if ($normalizedHost === 'localhost' || $normalizedHost === '127.0.0.1' || $normalizedHost === '::1') {
            return false;
        }

        return ! str_ends_with($normalizedHost, '.test')
            && ! str_ends_with($normalizedHost, '.local')
            && ! str_ends_with($normalizedHost, '.localhost');
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param array<string, mixed> $target
     */
    private function addOptionalStringField(array &$target, string $key, ?string $value): void
    {
        if ($value !== null && $value !== '') {
            $target[$key] = $value;
        }
    }
}
