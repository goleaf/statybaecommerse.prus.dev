<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\DocumentTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentTemplate>
 */
final class DocumentTemplateFactory extends Factory
{
    protected $model = DocumentTemplate::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true) . ' Template';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'content' => $this->generateTemplateContent(),
            'variables' => $this->generateVariables(),
            'type' => $this->faker->randomElement(['invoice', 'receipt', 'contract', 'agreement', 'catalog', 'report', 'certificate']),
            'category' => $this->faker->randomElement(['sales', 'marketing', 'legal', 'finance', 'operations', 'customer_service']),
            'settings' => [
                'page_size' => $this->faker->randomElement(['A4', 'Letter', 'Legal']),
                'orientation' => $this->faker->randomElement(['portrait', 'landscape']),
                'margins' => $this->faker->randomElement(['10mm', '15mm', '20mm']),
                'font_family' => $this->faker->randomElement(['Arial', 'Helvetica', 'Times New Roman']),
                'font_size' => $this->faker->randomElement(['10pt', '11pt', '12pt']),
            ],
            'is_active' => $this->faker->boolean(80),  // 80% chance of being active
        ];
    }

    /**
     * Generate template content with placeholders.
     */
    private function generateTemplateContent(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <title>{{DOCUMENT_TITLE}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .content { margin: 20px 0; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{COMPANY_NAME}}</h1>
        <p>{{COMPANY_ADDRESS}}</p>
    </div>
    
    <div class="content">
        <h2>{{DOCUMENT_TITLE}}</h2>
        <p><strong>Date:</strong> {{DOCUMENT_DATE}}</p>
        <p><strong>Customer:</strong> {{CUSTOMER_NAME}}</p>
        
        <div class="details">
            {{DOCUMENT_CONTENT}}
        </div>
        
        <div class="totals">
            <p><strong>Total:</strong> {{TOTAL_AMOUNT}}</p>
        </div>
    </div>
    
    <div class="footer">
        <p>Generated on {{GENERATION_DATE}}</p>
        <p>{{COMPANY_NAME}} - All rights reserved</p>
    </div>
</body>
</html>';
    }

    /**
     * Generate common template variables.
     */
    private function generateVariables(): array
    {
        return [
            'COMPANY_NAME',
            'COMPANY_ADDRESS',
            'DOCUMENT_TITLE',
            'DOCUMENT_DATE',
            'CUSTOMER_NAME',
            'CUSTOMER_EMAIL',
            'DOCUMENT_CONTENT',
            'TOTAL_AMOUNT',
            'GENERATION_DATE',
        ];
    }

    /**
     * Indicate that the template is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the template is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the template is for invoices.
     */
    public function invoice(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Invoice Template',
            'slug' => 'invoice-template',
            'type' => 'invoice',
            'category' => 'sales',
            'variables' => [
                'INVOICE_NUMBER',
                'INVOICE_DATE',
                'DUE_DATE',
                'CUSTOMER_NAME',
                'CUSTOMER_ADDRESS',
                'CUSTOMER_EMAIL',
                'ITEMS_TABLE',
                'SUBTOTAL',
                'TAX_AMOUNT',
                'TOTAL_AMOUNT',
                'PAYMENT_TERMS',
                'COMPANY_NAME',
                'COMPANY_ADDRESS',
                'COMPANY_TAX_ID',
            ],
            'content' => $this->generateInvoiceTemplate(),
        ]);
    }

    /**
     * Indicate that the template is for receipts.
     */
    public function receipt(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Receipt Template',
            'slug' => 'receipt-template',
            'type' => 'receipt',
            'category' => 'sales',
            'variables' => [
                'RECEIPT_NUMBER',
                'PURCHASE_DATE',
                'CUSTOMER_NAME',
                'ITEMS_TABLE',
                'PAYMENT_METHOD',
                'AMOUNT_PAID',
                'CHANGE_GIVEN',
                'COMPANY_NAME',
                'COMPANY_ADDRESS',
            ],
            'content' => $this->generateReceiptTemplate(),
        ]);
    }

    /**
     * Indicate that the template is for contracts.
     */
    public function contract(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Contract Template',
            'slug' => 'contract-template',
            'type' => 'contract',
            'category' => 'legal',
            'variables' => [
                'CONTRACT_NUMBER',
                'CONTRACT_DATE',
                'EFFECTIVE_DATE',
                'EXPIRY_DATE',
                'PARTY_A',
                'PARTY_B',
                'CONTRACT_TERMS',
                'CONTRACT_VALUE',
                'SIGNATURES',
            ],
            'content' => $this->generateContractTemplate(),
        ]);
    }

    /**
     * Generate invoice template content.
     */
    private function generateInvoiceTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{INVOICE_NUMBER}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .invoice-details { margin: 20px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .totals { text-align: right; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>{{COMPANY_NAME}}</h1>
            <p>{{COMPANY_ADDRESS}}</p>
            <p>Tax ID: {{COMPANY_TAX_ID}}</p>
        </div>
        <div>
            <h2>INVOICE</h2>
            <p><strong>Invoice #:</strong> {{INVOICE_NUMBER}}</p>
            <p><strong>Date:</strong> {{INVOICE_DATE}}</p>
            <p><strong>Due Date:</strong> {{DUE_DATE}}</p>
        </div>
    </div>
    
    <div class="invoice-details">
        <h3>Bill To:</h3>
        <p>{{CUSTOMER_NAME}}</p>
        <p>{{CUSTOMER_ADDRESS}}</p>
        <p>{{CUSTOMER_EMAIL}}</p>
    </div>
    
    {{ITEMS_TABLE}}
    
    <div class="totals">
        <p><strong>Subtotal:</strong> {{SUBTOTAL}}</p>
        <p><strong>Tax:</strong> {{TAX_AMOUNT}}</p>
        <p><strong>Total:</strong> {{TOTAL_AMOUNT}}</p>
    </div>
    
    <div class="payment-terms">
        <h3>Payment Terms:</h3>
        <p>{{PAYMENT_TERMS}}</p>
    </div>
</body>
</html>';
    }

    /**
     * Generate receipt template content.
     */
    private function generateReceiptTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <title>Receipt {{RECEIPT_NUMBER}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; text-align: center; }
        .receipt { max-width: 300px; margin: 0 auto; }
        .items { margin: 20px 0; }
        .total { font-weight: bold; font-size: 18px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="receipt">
        <h2>{{COMPANY_NAME}}</h2>
        <p>{{COMPANY_ADDRESS}}</p>
        
        <hr>
        
        <p><strong>Receipt #:</strong> {{RECEIPT_NUMBER}}</p>
        <p><strong>Date:</strong> {{PURCHASE_DATE}}</p>
        <p><strong>Customer:</strong> {{CUSTOMER_NAME}}</p>
        
        <hr>
        
        <div class="items">
            {{ITEMS_TABLE}}
        </div>
        
        <hr>
        
        <div class="total">
            <p>Total: {{AMOUNT_PAID}}</p>
            <p>Payment Method: {{PAYMENT_METHOD}}</p>
            <p>Change: {{CHANGE_GIVEN}}</p>
        </div>
        
        <hr>
        
        <p>Thank you for your business!</p>
    </div>
</body>
</html>';
    }

    /**
     * Generate contract template content.
     */
    private function generateContractTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <title>Contract {{CONTRACT_NUMBER}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 40px; }
        .parties { margin: 30px 0; }
        .terms { margin: 30px 0; }
        .signatures { margin-top: 50px; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRACT AGREEMENT</h1>
        <p>Contract Number: {{CONTRACT_NUMBER}}</p>
        <p>Date: {{CONTRACT_DATE}}</p>
    </div>
    
    <div class="parties">
        <p>This agreement is entered into between:</p>
        <p><strong>Party A:</strong> {{PARTY_A}}</p>
        <p><strong>Party B:</strong> {{PARTY_B}}</p>
    </div>
    
    <div class="terms">
        <h3>Terms and Conditions:</h3>
        {{CONTRACT_TERMS}}
        
        <p><strong>Contract Value:</strong> {{CONTRACT_VALUE}}</p>
        <p><strong>Effective Date:</strong> {{EFFECTIVE_DATE}}</p>
        <p><strong>Expiry Date:</strong> {{EXPIRY_DATE}}</p>
    </div>
    
    <div class="signatures">
        {{SIGNATURES}}
    </div>
</body>
</html>';
    }

    /**
     * Set custom variables for the template.
     */
    public function withVariables(array $variables): static
    {
        return $this->state(fn(array $attributes) => [
            'variables' => $variables,
        ]);
    }

    /**
     * Set custom settings for the template.
     */
    public function withSettings(array $settings): static
    {
        return $this->state(fn(array $attributes) => [
            'settings' => array_merge($attributes['settings'] ?? [], $settings),
        ]);
    }
}
