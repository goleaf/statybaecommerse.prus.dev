<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DocumentServiceContract;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use App\Notifications\DocumentGenerated;
use App\Support\Storage\SecureStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Stringable;

/**
 * DocumentService
 *
 * Service class containing DocumentService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class DocumentService implements DocumentServiceContract
{
    /**
     * Handle generateDocument functionality with proper error handling.
     *
     * @param array<string, mixed> $variables
     */
    public function generateDocument(DocumentTemplate $template, Model $relatedModel, array $variables = [], ?string $title = null, bool $sendNotification = false): Document
    {
        // Validate template content for security
        $this->validateTemplateContent($template->content);
        // Merge and sanitize variables
        $variables = $this->buildVariables($relatedModel, $variables);
        $variables = $this->sanitizeVariables($variables);
        $variables = $this->expandVariableKeys($variables);

        $content = $this->applyModelSpecificContent($template->content, $relatedModel);
        $processedContent = $this->processTemplate($content, $variables);
        /** @var int|string|null $relatedModelKey */
        $relatedModelKey = $relatedModel->getKey();

        if ($relatedModelKey === null) {
            $relatedModelKey = '';
        }

        /** @var int|string $relatedModelKey */
        $relatedModelKey = $relatedModelKey;

        $documentTitle = $title ?? sprintf('%s - %s', $template->name, (string) $relatedModelKey);
        $creatorId = Auth::user() instanceof User ? Auth::id() : null;

        $document = Document::create([
            'document_template_id' => $template->id,
            'title'                => $documentTitle,
            'content'              => $processedContent,
            'variables'            => $variables,
            'status'               => 'draft',
            'format'               => 'html',
            'documentable_type'    => get_class($relatedModel),
            'documentable_id'      => $relatedModelKey,
            'created_by'           => $creatorId,
            'updated_by'           => $creatorId,
            'generated_at'         => now(),
        ]);
        // Send notification if requested
        if ($sendNotification && Auth::user()) {
            Auth::user()->notify(new DocumentGenerated($document, false));
        }

        return $document;
    }

    /**
     * Handle generatePdf functionality with proper error handling.
     */
    public function generatePdf(Document $document): string
    {
        $template = $document->template;

        if (! $template instanceof DocumentTemplate) {
            throw new RuntimeException(__('documents.errors.missing_template'));
        }

        $settings = $template->getPrintSettings();

        // Normalize HTML to ensure UTF-8 support (Lithuanian characters) and proper font loading
        $htmlContent = $this->normalizeHtmlForPdf($document->content);

        $pdf = Pdf::loadHTML($htmlContent);
        // Apply settings
        $pdf->setPaper($settings['page_size'] ?? 'A4', $settings['orientation'] ?? 'portrait');
        // Generate filename
        $filename = 'documents/' . $document->id . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        // Save to storage
        $disk = SecureStorage::disk();
        Storage::disk($disk)->put($filename, $pdf->output());
        // Update document record
        $document->update(['format' => 'pdf', 'file_path' => $filename, 'status' => 'published']);
        // Send notification with PDF attachment
        if (Auth::user()) {
            Auth::user()->notify(new DocumentGenerated($document, true));
        }

        return SecureStorage::temporarySignedUrl($filename, now()->addMinutes((int) config('media-security.url_lifetime', 30)), true);
    }

    /**
     * Extract variables from a model for template substitution.
     *
     * @return array<string, mixed>
     */
    public function extractVariablesFromModel(Model $model, string $prefix = ''): array
    {
        $variables = [];
        $data = $model->toArray();

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_scalar($value) || $value === null) {
                $variables[$fullKey] = $value;
            }
        }

        return $variables;
    }

    /**
     * Get available variables for document templates.
     *
     * @return array<string, mixed>
     */
    public function getAvailableVariables(): array
    {
        return [
            // These will be merged with global config variables
            // Add specific service-level variables here if needed
        ];
    }

    private function normalizeHtmlForPdf(string $content): string
    {
        // If content already has a full HTML structure, return as is (assuming the template author handled it)
        // or potentially inject the charset/font if strictly needed.
        // For safety/simplicity, if we detect an html tag, we trust the template.
        if (stripos($content, '<html') !== false) {
            return $content;
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
        }
    </style>
</head>
<body>
    {$content}
</body>
</html>
HTML;
    }

    /**
     * Handle processTemplate functionality with proper error handling.
     *
     * @param array<string, mixed> $variables
     */
    private function processTemplate(string $content, array $variables): string
    {
        $processedContent = preg_replace_callback('/\{\{\s*([^}]+?)\s*\}\}/', function (array $matches) use ($variables): string {
            $key = trim($matches[1]);

            if ($key === '') {
                return $matches[0];
            }

            if (array_key_exists($key, $variables)) {
                return $this->stringifyVariable($variables[$key]);
            }

            $lowerKey = strtolower($key);
            if (array_key_exists($lowerKey, $variables)) {
                return $this->stringifyVariable($variables[$lowerKey]);
            }

            return $matches[0];
        }, $content) ?? $content;

        foreach ($variables as $key => $value) {
            if (! is_string($key) || $key === '' || $key[0] !== '$') {
                continue;
            }

            $processedContent = preg_replace(
                '/(?<![A-Za-z0-9_])' . preg_quote($key, '/') . '(?![A-Za-z0-9_])/',
                $this->stringifyVariable($value),
                $processedContent
            ) ?? $processedContent;
        }

        return $processedContent;
    }

    // ... (rest of methods)

    /**
     * Handle validateTemplateContent functionality with proper error handling.
     */
    private function validateTemplateContent(string $content): void
    {
        // Prevent XSS in templates
        // Tighten the event-attribute detection to avoid false positives like "content=" in meta tags.
        $hasScriptTag = preg_match('/<\s*script\b/i', $content);
        $hasJavascriptScheme = preg_match('/\bjavascript\s*:/i', $content);
        $hasInlineEventHandler = preg_match('/\bon[a-z]+\s*=\s*["\']?/i', $content);

        if ($hasScriptTag || $hasJavascriptScheme || $hasInlineEventHandler) {
            throw new InvalidArgumentException(__('documents.errors.dangerous_content'));
        }

        // Basic check for severely malformed HTML (count open/close tags)
        $openTags = preg_match_all('/<([a-zA-Z][a-zA-Z0-9]*)[^>]*>/i', $content, $openMatches);
        $closeTags = preg_match_all('/<\/([a-zA-Z][a-zA-Z0-9]*)[^>]*>/i', $content, $closeMatches);

        // This is a very rough check and might trigger on valid non-HTML content that looks like tags.
        // For a document template system, we generally expect valid HTML fragments.
        // We only warn if there's a significant mismatch in structure that might break PDF generation.
        // For now, we'll trust DomPDF to handle minor malformations, but we could enforce stricter rules here.
    }

    /**
     * Handle sanitizeVariables functionality with proper error handling.
     *
     * @param  array<string, mixed> $variables
     * @return array<string, mixed>
     */
    private function sanitizeVariables(array $variables): array
    {
        $sanitized = [];

        foreach ($variables as $key => $value) {
            if ($value instanceof Htmlable) {
                $sanitized[$key] = $value;
                continue;
            }

            if (is_string($value)) {
                $sanitized[$key] = strip_tags($value);
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    /**
     * Merge global, model-derived, and caller-provided variables for template rendering.
     *
     * @param array<string, mixed> $variables
     * @return array<string, mixed>
     */
    private function buildVariables(Model $relatedModel, array $variables): array
    {
        $globalVariables = $this->buildGlobalRuntimeVariables();

        $modelVariables = [];
        if (method_exists($relatedModel, 'getDocumentVariables')) {
            /** @var array<string, mixed> $modelVariables */
            $modelVariables = (array) $relatedModel->getDocumentVariables();
        }

        $specializedVariables = $relatedModel instanceof Order
            ? $this->buildOrderTemplateVariables($relatedModel)
            : [];

        return array_merge($globalVariables, $modelVariables, $specializedVariables, $variables);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGlobalRuntimeVariables(): array
    {
        $dateFormat = (string) config('datetime.formats.date', 'Y-m-d');
        $dateTimeFormat = (string) config('datetime.formats.datetime_full', 'Y-m-d H:i:s');

        $companyName = function_exists('app_setting')
            ? (app_setting('company_name') ?: config('app.company_name') ?: config('app.name'))
            : (config('app.company_name') ?: config('app.name'));
        $companyAddress = function_exists('app_setting')
            ? (app_setting('company_address') ?? config('app.company_address', ''))
            : config('app.company_address', '');
        $companyPhone = function_exists('app_setting')
            ? (app_setting('company_phone') ?? config('app.company_phone', ''))
            : config('app.company_phone', '');
        $companyEmail = function_exists('app_setting')
            ? (app_setting('company_email') ?? config('app.company_email') ?? config('mail.from.address', ''))
            : (config('app.company_email') ?: config('mail.from.address', ''));
        $companyVat = function_exists('app_setting')
            ? (app_setting('company_vat') ?? config('app.company_vat', ''))
            : config('app.company_vat', '');
        $storeCurrency = function_exists('app_setting')
            ? (app_setting('currency_code') ?? config('app.currency', 'EUR'))
            : config('app.currency', 'EUR');

        return [
            '$COMPANY_NAME' => (string) $companyName,
            '$COMPANY_ADDRESS' => (string) $companyAddress,
            '$COMPANY_PHONE' => (string) $companyPhone,
            '$COMPANY_EMAIL' => (string) $companyEmail,
            '$COMPANY_WEBSITE' => (string) config('app.url'),
            '$COMPANY_VAT' => (string) $companyVat,
            '$CURRENT_DATE' => now()->format($dateFormat),
            '$CURRENT_DATETIME' => now()->format($dateTimeFormat),
            '$CURRENT_YEAR' => (string) now()->year,
            '$CURRENT_MONTH' => now()->format('F'),
            '$CURRENT_DAY' => now()->format('d'),
            '$STORE_CURRENCY' => (string) $storeCurrency,
            '$STORE_LOCALE' => (string) app()->getLocale(),
            '$STORE_TIMEZONE' => (string) config('app.timezone'),
        ];
    }

    /**
     * Expand variable keys to support multiple placeholder styles.
     *
     * @param array<string, mixed> $variables
     * @return array<string, mixed>
     */
    private function expandVariableKeys(array $variables): array
    {
        $expanded = [];

        foreach ($variables as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            $this->storeExpandedVariable($expanded, $key, $value);

            $plainKey = ltrim($key, '$');
            $this->storeExpandedVariable($expanded, $plainKey, $value);
            $this->storeExpandedVariable($expanded, strtolower($plainKey), $value);
            $this->storeExpandedVariable($expanded, strtoupper($plainKey), $value);
            $this->storeExpandedVariable($expanded, '$' . strtoupper($plainKey), $value);

            if (! str_contains($plainKey, '.')) {
                $snakeKey = Str::snake($plainKey);
                $this->storeExpandedVariable($expanded, $snakeKey, $value);
                $this->storeExpandedVariable($expanded, strtoupper($snakeKey), $value);
                $this->storeExpandedVariable($expanded, '$' . strtoupper($snakeKey), $value);
            }
        }

        return $expanded;
    }

    /**
     * @param array<string, mixed> $expanded
     */
    private function storeExpandedVariable(array &$expanded, string $key, mixed $value): void
    {
        if ($key === '' || array_key_exists($key, $expanded)) {
            return;
        }

        $expanded[$key] = $value;
    }

    private function stringifyVariable(mixed $value): string
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        if (is_array($value)) {
            $value = implode(', ', array_map(static function ($item): string {
                return is_scalar($item) || $item === null ? (string) ($item ?? '') : get_debug_type($item);
            }, $value));
        } elseif (is_object($value)) {
            if ($value instanceof \BackedEnum) {
                $value = $value->value;
            } elseif ($value instanceof Stringable || method_exists($value, '__toString')) {
                $value = (string) $value;
            } else {
                $value = get_debug_type($value);
            }
        } elseif (is_bool($value)) {
            $value = $value ? __('messages.yes') : __('messages.no');
        } elseif ($value === null) {
            $value = '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function applyModelSpecificContent(string $content, Model $relatedModel): string
    {
        if ($relatedModel instanceof Order) {
            return $this->injectOrderItemRows($content, $relatedModel);
        }

        return $content;
    }

    /**
     * Get a unified collection of line items (products and services) for an order.
     *
     * @return Collection<int, array{name: string, sku: string, quantity: int|float, unit_price: float, total: float, type: string}>
     */
    private function getUnifiedLineItems(Order $order): Collection
    {
        $order->loadMissing(['items', 'services']);
        $lines = collect();

        // 1. Process Products (OrderItems)
        foreach ($order->items as $item) {
            $lines->push([
                'name' => (string) ($item->name ?? ''),
                'sku' => (string) ($item->sku ?? ''),
                'quantity' => $item->quantity,
                'unit_price' => (float) ($item->unit_price ?? $item->price ?? 0),
                'total' => (float) ($item->total ?? 0),
                'type' => 'product',
            ]);
        }

        // 2. Process Services
        foreach ($order->services as $service) {
            $quantity = (float) ($service->pivot->quantity ?? 1);
            $price = (float) ($service->pivot->price ?? $service->price ?? 0);

            $lines->push([
                'name' => (string) ($service->name ?? ''),
                'sku' => '',
                'quantity' => $quantity,
                'unit_price' => $price,
                'total' => $quantity * $price,
                'type' => 'service',
            ]);
        }

        return $lines;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOrderTemplateVariables(Order $order): array
    {
        $order->loadMissing(['items', 'user', 'shippingOption']);

        $companyName = (string) (function_exists('app_setting')
            ? (app_setting('company_name') ?: config('app.company_name') ?: config('app.name'))
            : (config('app.company_name') ?: config('app.name')));
        $companyAddress = (string) (function_exists('app_setting')
            ? (app_setting('company_address') ?? config('app.company_address', ''))
            : config('app.company_address', ''));
        $companyPhone = (string) (function_exists('app_setting')
            ? (app_setting('company_phone') ?? config('app.company_phone', ''))
            : config('app.company_phone', ''));
        $companyEmail = (string) (function_exists('app_setting')
            ? (app_setting('company_email') ?? config('app.company_email') ?? config('mail.from.address', ''))
            : (config('app.company_email') ?: config('mail.from.address', '')));
        $companyCode = (string) (function_exists('app_setting')
            ? (app_setting('company_code') ?? config('app.company_code', ''))
            : config('app.company_code', ''));
        $companyVat = (string) (function_exists('app_setting')
            ? (app_setting('company_vat') ?? config('app.company_vat', ''))
            : config('app.company_vat', ''));

        $billingAddress = is_array($order->billing_address) ? $order->billing_address : [];
        $shippingAddress = is_array($order->shipping_address) ? $order->shipping_address : [];

        if ($billingAddress === [] && $shippingAddress !== []) {
            $billingAddress = $shippingAddress;
        }
        if ($shippingAddress === [] && $billingAddress !== []) {
            $shippingAddress = $billingAddress;
        }

        $buyerName = $this->resolveContactName($billingAddress, $order->user);
        $buyerAddress = $this->formatAddress($billingAddress);
        $buyerPhone = $this->resolveAddressValue($billingAddress, ['phone', 'phone_number'], $order->user?->phone ?? $order->user?->phone_number ?? '');
        $buyerEmail = $this->resolveAddressValue($billingAddress, ['email'], $order->user?->email ?? '');
        $buyerCompanyCode = $this->resolveAddressValue($billingAddress, ['company_code', 'company_id']);
        $buyerVatCode = $this->resolveAddressValue($billingAddress, ['vat_code', 'company_vat', 'vat']);
        $buyerCompany = $this->resolveAddressValue($billingAddress, ['company', 'company_name']);
        $customerFirstName = $this->resolveAddressValue($billingAddress, ['first_name'], $order->user?->first_name ?? '');
        $customerLastName = $this->resolveAddressValue($billingAddress, ['last_name'], $order->user?->last_name ?? '');
        $customerName = trim($customerFirstName . ' ' . $customerLastName);
        if ($customerName === '') {
            $customerName = $buyerName;
        }
        $customerEmail = $buyerEmail;
        $customerPhone = $buyerPhone;

        $date = $order->created_at ?? now();
        $dateFormat = (string) config('datetime.formats.date', 'Y-m-d');
        $timeFormat = (string) config('datetime.formats.time', 'H:i');

        $subtotal = (float) ($order->subtotal ?? 0);
        $taxAmount = (float) ($order->tax_amount ?? 0);
        $shippingAmount = (float) ($order->shipping_amount ?? 0);
        $discountAmount = (float) ($order->discount_amount ?? 0);
        $totalAmount = (float) ($order->total ?? ($subtotal + $taxAmount));
        $vatRate = $this->resolveVatRate($subtotal, $taxAmount);
        $billingCity = $this->resolveAddressValue($billingAddress, ['city']);
        $billingCountry = $this->resolveAddressValue($billingAddress, ['country', 'country_name', 'country_code']);
        $billingPostalCode = $this->resolveAddressValue($billingAddress, ['zip', 'postal_code']);
        $shippingCity = $this->resolveAddressValue($shippingAddress, ['city']);
        $shippingCountry = $this->resolveAddressValue($shippingAddress, ['country', 'country_name', 'country_code']);
        $shippingPostalCode = $this->resolveAddressValue($shippingAddress, ['zip', 'postal_code']);
        $shippingAddressFormatted = $this->formatAddress($shippingAddress);

        $orderStatus = $order->status;
        $orderStatusValue = $orderStatus instanceof OrderStatus
            ? $orderStatus->value
            : (is_string($orderStatus) ? $orderStatus : '');
        $orderStatusLabel = $orderStatus instanceof OrderStatus
            ? $orderStatus->label()
            : (OrderStatus::tryFrom($orderStatusValue)?->label() ?? $orderStatusValue);

        $variables = [
            'order_number' => $order->number ?? $order->id,
            'order_date' => $date->format($dateFormat),
            'order_total' => $this->formatMoney($totalAmount),
            'order_subtotal' => $this->formatMoney($subtotal),
            'order_tax' => $this->formatMoney($taxAmount),
            'order_shipping' => $this->formatMoney($shippingAmount),
            'order_discount' => $this->formatMoney($discountAmount),
            'order_status' => $orderStatusValue,
            'order_status_label' => $orderStatusLabel,
            'order_payment_method' => $order->payment_method instanceof PaymentMethod ? $order->payment_method->value : (string) ($order->payment_method ?? ''),
            'order_shipping_method' => (string) ($order->shippingOption?->name ?? ''),
            'invoice_number' => $order->number ?? $order->id,
            'invoice_date' => $date->format($dateFormat),
            'receipt_number' => $order->number ?? $order->id,
            'receipt_date' => $date->format($dateFormat),
            'receipt_time' => $date->format($timeFormat),
            'payment_method' => $order->payment_method instanceof PaymentMethod ? $order->payment_method->value : (string) ($order->payment_method ?? ''),
            'payment_due_date' => '',
            'issuer_name' => Auth::user()?->name ?? $companyName,
            'cashier_name' => Auth::user()?->name ?? '',
            'subtotal' => $this->formatMoney($subtotal),
            'vat_amount' => $this->formatMoney($taxAmount),
            'total_amount' => $this->formatMoney($totalAmount),
            'amount_paid' => $this->formatMoney($totalAmount),
            'change_amount' => $this->formatMoney(0),
            'seller_name' => $companyName,
            'seller_address' => $companyAddress,
            'seller_company_code' => $companyCode,
            'seller_vat_code' => $companyVat,
            'seller_phone' => $companyPhone,
            'seller_email' => $companyEmail,
            'buyer_name' => $buyerName,
            'buyer_address' => $buyerAddress,
            'buyer_company_code' => $buyerCompanyCode,
            'buyer_vat_code' => $buyerVatCode,
            'buyer_phone' => $buyerPhone,
            'buyer_email' => $buyerEmail,
            'customer_name' => $customerName,
            'customer_first_name' => $customerFirstName,
            'customer_last_name' => $customerLastName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'customer_company' => $buyerCompany,
            'billing_address' => $buyerAddress,
            'billing_city' => $billingCity,
            'billing_country' => $billingCountry,
            'billing_postal_code' => $billingPostalCode,
            'shipping_address' => $shippingAddressFormatted,
            'shipping_city' => $shippingCity,
            'shipping_country' => $shippingCountry,
            'shipping_postal_code' => $shippingPostalCode,
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'company_phone' => $companyPhone,
            'company_email' => $companyEmail,
            'company_code' => $companyCode,
            'vat_code' => $companyVat,
        ];

        $unifiedItems = $this->getUnifiedLineItems($order);
        foreach ($unifiedItems as $index => $item) {
            $position = $index + 1;
            $variables["item_name_{$position}"] = $item['name'];
            $variables["item_sku_{$position}"] = $item['sku'];
            $variables["quantity_{$position}"] = (string) $item['quantity'];
            $variables["unit_price_{$position}"] = $this->formatMoney($item['unit_price']);
            $variables["vat_rate_{$position}"] = $this->formatRate($vatRate);
            $variables["total_{$position}"] = $this->formatMoney($item['total']);
            $variables["item_total_{$position}"] = $this->formatMoney($item['total']);
        }

        return $variables;
    }

    private function formatMoney(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, '.', '');
    }

    private function formatRate(float $value): string
    {
        $formatted = number_format($value, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function resolveVatRate(float $subtotal, float $taxAmount): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        return round(($taxAmount / $subtotal) * 100, 2);
    }

    private function resolveContactName(array $address, ?User $user = null): string
    {
        $firstName = $address['first_name'] ?? '';
        $lastName = $address['last_name'] ?? '';
        $company = $address['company'] ?? $address['company_name'] ?? '';

        $name = trim((string) $firstName . ' ' . (string) $lastName);

        if ($name !== '') {
            return $name;
        }

        if (is_string($company) && $company !== '') {
            return $company;
        }

        if ($user instanceof User) {
            $userName = $user->name ?? trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));

            if (is_string($userName) && $userName !== '') {
                return $userName;
            }
        }

        return '';
    }

    private function resolveAddressValue(array $address, array $keys, ?string $fallback = null): string
    {
        foreach ($keys as $key) {
            if (isset($address[$key]) && is_scalar($address[$key])) {
                $value = (string) $address[$key];
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return (string) ($fallback ?? '');
    }

    private function formatAddress(array $address): string
    {
        if ($address === []) {
            return '';
        }

        $street = $address['street'] ?? $address['address_line_1'] ?? $address['street_address'] ?? '';
        $streetPlus = $address['street_address_plus'] ?? $address['address_line_2'] ?? '';
        $city = $address['city'] ?? '';
        $zip = $address['zip'] ?? $address['postal_code'] ?? '';
        $country = $address['country'] ?? $address['country_name'] ?? $address['country_code'] ?? '';

        $parts = array_filter([
            trim((string) $street . ($streetPlus !== '' ? ' ' . $streetPlus : '')),
            (string) $city,
            (string) $zip,
            (string) $country,
        ], static fn (string $value): bool => trim($value) !== '');

        return implode(', ', $parts);
    }

    private function injectOrderItemRows(string $content, Order $order): string
    {
        $lines = $this->getUnifiedLineItems($order);

        if ($lines->count() <= 1) {
            return $content;
        }

        $subtotal = (float) ($order->subtotal ?? 0);
        $taxAmount = (float) ($order->tax_amount ?? 0);
        $vatRate = $this->resolveVatRate($subtotal, $taxAmount);

        $content = $this->injectInvoiceRows($content, $lines, $vatRate);
        $content = $this->injectReceiptRows($content, $lines);

        return $content;
    }

    private function injectInvoiceRows(string $content, Collection $items, float $vatRate): string
    {
        $needles = [
            '<!-- Papildomos eilutės -->',
            '<!-- Papildomos eilutes -->',
            '<!-- Additional rows here -->',
        ];

        $needle = null;
        foreach ($needles as $candidate) {
            if (str_contains($content, $candidate)) {
                $needle = $candidate;
                break;
            }
        }

        if ($needle === null) {
            return $content;
        }

        $rows = [];
        foreach ($items->slice(1)->values() as $index => $item) {
            $position = $index + 2;
            $unitPrice = (float) $item['unit_price'];
            $total = (float) $item['total'];

            $rows[] = sprintf(
                '<tr><td>%d</td><td>%s</td><td class="text-right">%s</td><td class="text-right">%s</td><td class="text-right">%s</td><td class="text-right">%s</td></tr>',
                $position,
                $this->escapeHtml((string) ($item['name'] ?? '')),
                $this->escapeHtml((string) ($item['quantity'] ?? 0)),
                $this->escapeHtml($this->formatMoney($unitPrice)),
                $this->escapeHtml($this->formatRate($vatRate)),
                $this->escapeHtml($this->formatMoney($total))
            );
        }

        if ($rows === []) {
            return $content;
        }

        return str_replace($needle, implode("\n", $rows), $content);
    }

    private function injectReceiptRows(string $content, Collection $items): string
    {
        $needles = [
            '<!-- Papildomos prekės -->',
            '<!-- Papildomos prekes -->',
        ];

        $needle = null;
        foreach ($needles as $candidate) {
            if (str_contains($content, $candidate)) {
                $needle = $candidate;
                break;
            }
        }

        if ($needle === null) {
            return $content;
        }

        $rows = [];
        foreach ($items->slice(2)->values() as $item) {
            $unitPrice = (float) $item['unit_price'];
            $total = (float) $item['total'];

            $rows[] = sprintf(
                '<div class="item-row"><div class="item-name">%s</div><div class="item-details"><span>%s vnt. x %s €</span><span>%s €</span></div></div>',
                $this->escapeHtml((string) ($item['name'] ?? '')),
                $this->escapeHtml((string) ($item['quantity'] ?? 0)),
                $this->escapeHtml($this->formatMoney($unitPrice)),
                $this->escapeHtml($this->formatMoney($total))
            );
        }

        if ($rows === []) {
            return $content;
        }

        return str_replace($needle, implode("\n", $rows), $content);
    }

    private function injectProductRows(string $content, Collection $items, float $vatRate): string
    {
        $needles = [
            '<!-- Papildomos prekės -->',
            '<!-- Product rows here -->',
        ];

        $needle = null;
        foreach ($needles as $candidate) {
            if (str_contains($content, $candidate)) {
                $needle = $candidate;
                break;
            }
        }

        if ($needle === null) {
            return $content;
        }

        $rows = [];
        foreach ($items->slice(1)->values() as $index => $item) {
            $position = $index + 2;
            $unitPrice = (float) $item['unit_price'];
            $total = (float) $item['total'];

            $rows[] = sprintf(
                '<tr><td>%d</td><td>%s</td><td class="text-right">%s</td><td class="text-right">%s</td><td class="text-right">%s</td><td class="text-right">%s</td></tr>',
                $position,
                $this->escapeHtml((string) ($item['name'] ?? '')),
                $this->escapeHtml((string) ($item['quantity'] ?? 0)),
                $this->escapeHtml($this->formatMoney($unitPrice)),
                $this->escapeHtml($this->formatRate($vatRate)),
                $this->escapeHtml($this->formatMoney($total))
            );
        }

        return str_replace($needle, implode("\n", $rows), $content);
    }

    private function injectServiceRows(string $content, Collection $items, float $vatRate): string
    {
        $needles = [
            '<!-- Papildomos paslaugos -->',
            '<!-- Service rows here -->',
        ];

        $needle = null;
        foreach ($needles as $candidate) {
            if (str_contains($content, $candidate)) {
                $needle = $candidate;
                break;
            }
        }

        if ($needle === null) {
            return $content;
        }

        $rows = [];
        foreach ($items->slice(1)->values() as $index => $item) {
            $position = $index + 2;
            $unitPrice = (float) $item['unit_price'];
            $total = (float) $item['total'];

            $rows[] = sprintf(
                '<tr><td>%d</td><td>%s</td><td class="text-right">%s</td><td class="text-right">%s</td><td class="text-right">%s</td><td class="text-right">%s</td></tr>',
                $position,
                $this->escapeHtml((string) ($item['name'] ?? '')),
                $this->escapeHtml((string) ($item['quantity'] ?? 0)),
                $this->escapeHtml($this->formatMoney($unitPrice)),
                $this->escapeHtml($this->formatRate($vatRate)),
                $this->escapeHtml($this->formatMoney($total))
            );
        }

        return str_replace($needle, implode("\n", $rows), $content);
    }

    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
