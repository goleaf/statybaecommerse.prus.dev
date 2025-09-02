<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

final class DocumentService
{
    public function generateDocument(
        DocumentTemplate $template,
        Model $relatedModel,
        array $variables = [],
        string $title = null
    ): Document {
        $processedContent = $this->processTemplate($template->content, $variables);
        
        $document = Document::create([
            'document_template_id' => $template->id,
            'title' => $title ?? $template->name . ' - ' . $relatedModel->id,
            'content' => $processedContent,
            'variables' => $variables,
            'status' => 'draft',
            'format' => 'html',
            'documentable_type' => get_class($relatedModel),
            'documentable_id' => $relatedModel->id,
            'created_by' => Auth::id(),
            'generated_at' => now(),
        ]);

        return $document;
    }

    public function generatePdf(Document $document): string
    {
        $template = $document->template;
        $settings = $template->getPrintSettings();
        
        $pdf = Pdf::loadHTML($document->content);
        
        // Apply settings
        $pdf->setPaper($settings['page_size'] ?? 'A4', $settings['orientation'] ?? 'portrait');
        
        // Generate filename
        $filename = 'documents/' . $document->id . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        // Save to storage
        Storage::disk('public')->put($filename, $pdf->output());
        
        // Update document record
        $document->update([
            'format' => 'pdf',
            'file_path' => $filename,
            'status' => 'published',
        ]);

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

    public function getAvailableVariables(): array
    {
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
    }

    public function extractVariablesFromModel(Model $model, string $prefix = ''): array
    {
        $variables = [];
        $attributes = $model->getAttributes();
        
        foreach ($attributes as $key => $value) {
            if (!is_null($value)) {
                $variableName = '$' . strtoupper($prefix . $key);
                $variables[$variableName] = $value;
            }
        }
        
        return $variables;
    }
}
