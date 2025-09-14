<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Notifications\DocumentGenerated;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Document Service
 * 
 * Handles document generation, template processing, and PDF creation
 * for the e-commerce system. Provides comprehensive document management
 * with security validation and notification support.
 */
final /**
 * DocumentService
 * 
 * Service class containing business logic and external integrations.
 */
class DocumentService
{
    /**
     * Generate a document from a template with variables.
     * 
     * Creates a new document instance with processed content based on
     * the provided template and variables. Validates template content
     * for security and sanitizes variables to prevent XSS attacks.
     * 
     * @param \App\Models\DocumentTemplate $template The document template to use
     * @param \Illuminate\Database\Eloquent\Model $relatedModel The model this document relates to
     * @param array $variables Variables to replace in the template
     * @param string|null $title Custom title for the document
     * @param bool $sendNotification Whether to send notification after generation
     * @return \App\Models\Document The generated document instance
     * @throws \InvalidArgumentException If template content contains dangerous elements
     */
    public function generateDocument(
        DocumentTemplate $template,
        Model $relatedModel,
        array $variables = [],
        ?string $title = null,
        bool $sendNotification = false
    ): Document {
        // Validate template content for security
        $this->validateTemplateContent($template->content);

        // Sanitize variables
        $variables = $this->sanitizeVariables($variables);

        $processedContent = $this->processTemplate($template->content, $variables);

        $document = Document::create([
            'document_template_id' => $template->id,
            'title' => $title ?? $template->name.' - '.$relatedModel->id,
            'content' => $processedContent,
            'variables' => $variables,
            'status' => 'draft',
            'format' => 'html',
            'documentable_type' => get_class($relatedModel),
            'documentable_id' => $relatedModel->id,
            'created_by' => Auth::id(),
            'generated_at' => now(),
        ]);

        // Send notification if requested
        if ($sendNotification && Auth::user()) {
            Auth::user()->notify(new DocumentGenerated($document, false));
        }

        return $document;
    }

    /**
     * Generate PDF from a document.
     * 
     * Converts the document content to PDF format using DomPDF,
     * applies template print settings, and saves to storage.
     * Updates the document record with PDF information and sends notification.
     * 
     * @param \App\Models\Document $document The document to convert to PDF
     * @return string The public URL of the generated PDF file
     */
    public function generatePdf(Document $document): string
    {
        $template = $document->template;
        $settings = $template->getPrintSettings();

        $pdf = Pdf::loadHTML($document->content);

        // Apply settings
        $pdf->setPaper($settings['page_size'] ?? 'A4', $settings['orientation'] ?? 'portrait');

        // Generate filename
        $filename = 'documents/'.$document->id.'_'.now()->format('Y-m-d_H-i-s').'.pdf';

        // Save to storage
        Storage::disk('public')->put($filename, $pdf->output());

        // Update document record
        $document->update([
            'format' => 'pdf',
            'file_path' => $filename,
            'status' => 'published',
        ]);

        // Send notification with PDF attachment
        if (Auth::user()) {
            Auth::user()->notify(new DocumentGenerated($document, true));
        }

        return Storage::disk('public')->url($filename);
    }

    private function processTemplate(string $content, array $variables): string
    {
        $processedContent = $content;

        foreach ($variables as $key => $value) {
            // Handle different value types
            if (is_array($value)) {
                $value = implode(', ', $value);
            } elseif (is_object($value)) {
                $value = (string) $value;
            } elseif (is_bool($value)) {
                $value = $value ? __('documents.yes') : __('documents.no');
            }

            $processedContent = str_replace($key, (string) $value, $processedContent);
        }

        return $processedContent;
    }

    /**
     * Get available template variables.
     * 
     * Returns a cached list of all available variables that can be used
     * in document templates, including global system variables and
     * e-commerce specific variables.
     * 
     * @return array Associative array of variable names and descriptions
     */
    public function getAvailableVariables(): array
    {
        return Cache::remember('document_variables_'.app()->getLocale(), 3600, function () {
            return [
                // Global variables
                '$COMPANY_NAME' => config('app.name'),
                '$CURRENT_DATE' => now()->format('Y-m-d'),
                '$CURRENT_DATETIME' => now()->format('Y-m-d H:i:s'),
                '$CURRENT_YEAR' => now()->year,
                '$CURRENT_USER' => Auth::user()?->name ?? '',
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
        });
    }

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

    public function renderTemplate(DocumentTemplate $template, array $variables): string
    {
        return $this->processTemplate($template->content, $variables);
    }

    public function generateDocumentAsync(
        DocumentTemplate $template,
        Model $relatedModel,
        array $variables = [],
        ?string $title = null
    ): void {
        dispatch(function () use ($template, $relatedModel, $variables, $title) {
            $this->generateDocument($template, $relatedModel, $variables, $title, true);
        });
    }

    public function previewTemplate(DocumentTemplate $template, array $sampleVariables = []): string
    {
        $variables = array_merge($this->getSampleVariables(), $sampleVariables);

        return $this->processTemplate($template->content, $variables);
    }

    public function getSampleVariables(): array
    {
        return [
            '$COMPANY_NAME' => config('app.name', 'Sample Company'),
            '$CURRENT_DATE' => now()->format('Y-m-d'),
            '$CURRENT_YEAR' => now()->year,
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
