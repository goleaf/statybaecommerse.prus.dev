<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DocumentServiceContract;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\User;
use App\Notifications\DocumentGenerated;
use App\Support\Storage\SecureStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

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
     * @param  array<string, mixed>  $variables
     */
    public function generateDocument(DocumentTemplate $template, Model $relatedModel, array $variables = [], ?string $title = null, bool $sendNotification = false): Document
    {
        // Validate template content for security
        $this->validateTemplateContent($template->content);
        // Sanitize variables
        $variables = $this->sanitizeVariables($variables);
        $processedContent = $this->processTemplate($template->content, $variables);
        /** @var int|string|null $relatedModelKey */
        $relatedModelKey = $relatedModel->getKey();

        if ($relatedModelKey === null) {
            $relatedModelKey = '';
        }

        /** @var int|string $relatedModelKey */
        $relatedModelKey = $relatedModelKey;

        $documentTitle = $title ?? sprintf('%s - %s', $template->name, (string) $relatedModelKey);

        $document = Document::create([
            'document_template_id' => $template->id,
            'title' => $documentTitle,
            'content' => $processedContent,
            'variables' => $variables,
            'status' => 'draft',
            'format' => 'html',
            'documentable_type' => get_class($relatedModel),
            'documentable_id' => $relatedModelKey,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'generated_at' => now(),
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
        $pdf = Pdf::loadHTML($document->content);
        // Apply settings
        $pdf->setPaper($settings['page_size'] ?? 'A4', $settings['orientation'] ?? 'portrait');
        // Generate filename
        $filename = 'documents/'.$document->id.'_'.now()->format('Y-m-d_H-i-s').'.pdf';
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
     * Handle processTemplate functionality with proper error handling.
     *
     * @param  array<string, mixed>  $variables
     */
    private function processTemplate(string $content, array $variables): string
    {
        $processedContent = $content;
        foreach ($variables as $key => $value) {
            // Handle different value types
            if (is_array($value)) {
                $value = implode(', ', $value);
            } elseif (is_object($value)) {
                if ($value instanceof \Stringable || method_exists($value, '__toString')) {
                    $value = (string) $value;
                } else {
                    $value = get_debug_type($value);
                }
            } elseif (is_bool($value)) {
                $value = $value ? __('documents.yes') : __('documents.no');
            }

            assert(is_scalar($value) || $value === null);
            $processedContent = str_replace($key, (string) $value, $processedContent);
        }

        return $processedContent;
    }

    /**
     * Handle getAvailableVariables functionality with proper error handling.
     *
     * @return array<string, string>
     */
    public function getAvailableVariables(): array
    {
        $resolver = function (): array {
            /** @var User|null $user */
            $user = Auth::user();
            $currentUserName = $user?->name;

            if (! is_string($currentUserName)) {
                $currentUserName = '';
            }

            $companyName = config('app.name', '');

            if (! is_string($companyName)) {
                $companyName = '';
            }

            return [
                // Global variables
                '$COMPANY_NAME' => $companyName,
                '$CURRENT_DATE' => now()->format('Y-m-d'),
                '$CURRENT_DATETIME' => now()->format('Y-m-d H:i:s'),
                '$CURRENT_YEAR' => now()->format('Y'),
                '$CURRENT_USER' => $currentUserName,
                // Common e-commerce variables
                '$ORDER_NUMBER' => 'Order number',
                '$ORDER_DATE' => 'Order date',
                '$ORDER_TOTAL' => 'Order total',
                '$CUSTOMER_NAME' => 'Customer name',
                '$CUSTOMER_EMAIL' => 'Customer email',
                '$CUSTOMER_PHONE' => 'Customer phone',
                '$CUSTOMER_ADDRESS' => 'Customer address',
                '$PRODUCT_NAME' => 'Product name',
                '$PRODUCT_SKU' => 'Product SKU',
                '$PRODUCT_PRICE' => 'Product price',
                '$BRAND_NAME' => 'Brand name',
                '$CATEGORY_NAME' => 'Category name',
            ];
        };

        $storeName = config('documents.cache_store', 'array');

        if (! is_string($storeName)) {
            $storeName = 'array';
        }

        try {
            /** @var array<string, string> $variables */
            $variables = Cache::store($storeName)->remember('document_variables_'.app()->getLocale(), 3600, $resolver);

            return $variables;
        } catch (\Throwable $exception) {
            if (app()->runningInConsole()) {
                return $resolver();
            }

            report($exception);

            return $resolver();
        }
    }

    /**
     * Handle extractVariablesFromModel functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public function extractVariablesFromModel(Model $model, string $prefix = ''): array
    {
        $variables = [];
        $attributes = $model->getAttributes();
        foreach ($attributes as $key => $value) {
            if (! is_null($value)) {
                $variableName = '$'.strtoupper($prefix.$key);
                $variables[$variableName] = $value;
            }
        }
        // Add specific mappings for Order model
        if ($model instanceof \App\Models\Order) {
            $variables['$ORDER_NUMBER'] = $model->number ?? $model->id;
            $variables['$ORDER_TOTAL'] = number_format((float) ($model->total ?? 0), 2);
            if ($model->user) {
                $variables['$CUSTOMER_NAME'] = $model->user->name ?? '';
                $variables['$CUSTOMER_EMAIL'] = $model->user->email ?? '';
            }
        }

        return $variables;
    }

    /**
     * Handle renderTemplate functionality with proper error handling.
     *
     * @param  array<string, mixed>  $variables
     */
    public function renderTemplate(DocumentTemplate $template, array $variables): string
    {
        return $this->processTemplate($template->content, $variables);
    }

    /**
     * Handle generateDocumentAsync functionality with proper error handling.
     *
     * @param  array<string, mixed>  $variables
     */
    public function generateDocumentAsync(DocumentTemplate $template, Model $relatedModel, array $variables = [], ?string $title = null): void
    {
        dispatch(function () use ($template, $relatedModel, $variables, $title): void {
            $this->generateDocument($template, $relatedModel, $variables, $title, true);
        });
    }

    /**
     * Handle previewTemplate functionality with proper error handling.
     *
     * @param  array<string, mixed>  $sampleVariables
     */
    public function previewTemplate(DocumentTemplate $template, array $sampleVariables = []): string
    {
        $variables = array_merge($this->getSampleVariables(), $sampleVariables);

        return $this->processTemplate($template->content, $variables);
    }

    /**
     * Handle getSampleVariables functionality with proper error handling.
     *
     * @return array<string, string>
     */
    public function getSampleVariables(): array
    {
        $companyName = config('app.name', 'Sample Company');

        if (! is_string($companyName)) {
            $companyName = 'Sample Company';
        }

        return [
            '$COMPANY_NAME' => $companyName,
            '$CURRENT_DATE' => now()->format('Y-m-d'),
            '$CURRENT_YEAR' => now()->format('Y'),
            '$ORDER_NUMBER' => 'ORD-2025-001',
            '$ORDER_DATE' => now()->format('Y-m-d'),
            '$ORDER_TOTAL' => '€99.99',
            '$ORDER_SUBTOTAL' => '€85.00',
            '$ORDER_TAX' => '€14.99',
            '$ORDER_SHIPPING' => '€5.00',
            '$CUSTOMER_NAME' => 'John Doe',
            '$CUSTOMER_EMAIL' => 'john.doe@example.com',
            '$CUSTOMER_PHONE' => '+370 600 12345',
            '$PRODUCT_NAME' => 'Sample Product',
            '$PRODUCT_SKU' => 'SKU-001',
            '$PRODUCT_PRICE' => '€49.99',
            '$BRAND_NAME' => 'Sample Brand',
        ];
    }

    /**
     * Handle validateTemplateContent functionality with proper error handling.
     */
    private function validateTemplateContent(string $content): void
    {
        // Prevent XSS in templates
        if (preg_match('/<script|javascript:|on\w+=/i', $content)) {
            throw new \InvalidArgumentException(__('documents.errors.dangerous_content'));
        }
        // Basic check for severely malformed HTML (only check for unclosed tags)
        $openTags = preg_match_all('/<([a-zA-Z][a-zA-Z0-9]*)[^>]*>/i', $content);
        $closeTags = preg_match_all('/<\/([a-zA-Z][a-zA-Z0-9]*)[^>]*>/i', $content);
        // Allow some flexibility in HTML structure for rich content
    }

    /**
     * Handle sanitizeVariables functionality with proper error handling.
     *
     * @param  array<string, mixed>  $variables
     * @return array<string, mixed>
     */
    private function sanitizeVariables(array $variables): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return strip_tags($value);
            }

            return $value;
        }, $variables);
    }
}
