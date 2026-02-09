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
use Illuminate\Support\Facades\Storage;
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
        $processedContent = $content;
        foreach ($variables as $key => $value) {
            // Handle different value types
            if (is_array($value)) {
                $value = implode(', ', $value);
            } elseif (is_object($value)) {
                if ($value instanceof Stringable || method_exists($value, '__toString')) {
                    $value = (string) $value;
                } else {
                    $value = get_debug_type($value);
                }
            } elseif (is_bool($value)) {
                $value = $value ? __('messages.yes') : __('messages.no');
            }

            assert(is_scalar($value) || $value === null);
            $processedContent = str_replace($key, (string) $value, $processedContent);
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
        return array_map(function ($value) {
            if (is_string($value)) {
                return strip_tags($value);
            }

            return $value;
        }, $variables);
    }
}
