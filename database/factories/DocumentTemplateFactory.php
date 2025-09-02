<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\DocumentTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class DocumentTemplateFactory extends Factory
{
    protected $model = DocumentTemplate::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'content' => $this->generateSampleContent(),
            'variables' => $this->generateSampleVariables(),
            'type' => fake()->randomElement(['invoice', 'receipt', 'contract', 'agreement', 'catalog', 'report', 'certificate', 'document']),
            'category' => fake()->randomElement(['sales', 'marketing', 'legal', 'finance', 'operations', 'customer_service']),
            'settings' => [
                'page_size' => 'A4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20],
            ],
            'is_active' => true,
        ];
    }

    private function generateSampleContent(): string
    {
        return '<html>
<head>
    <title>$DOCUMENT_TITLE</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .content { margin: 20px 0; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>$COMPANY_NAME</h1>
        <p>$COMPANY_ADDRESS</p>
    </div>
    
    <div class="content">
        <h2>$DOCUMENT_TYPE</h2>
        <p><strong>Date:</strong> $CURRENT_DATE</p>
        <p><strong>Customer:</strong> $CUSTOMER_NAME</p>
        <p><strong>Email:</strong> $CUSTOMER_EMAIL</p>
        
        <h3>Details</h3>
        <p>$CONTENT_DETAILS</p>
        
        <p><strong>Total Amount:</strong> $TOTAL_AMOUNT</p>
    </div>
    
    <div class="footer">
        <p>Generated on $GENERATION_DATE</p>
        <p>$COMPANY_NAME - $COMPANY_WEBSITE</p>
    </div>
</body>
</html>';
    }

    private function generateSampleVariables(): array
    {
        return [
            '$DOCUMENT_TITLE',
            '$COMPANY_NAME',
            '$COMPANY_ADDRESS',
            '$COMPANY_WEBSITE',
            '$DOCUMENT_TYPE',
            '$CURRENT_DATE',
            '$GENERATION_DATE',
            '$CUSTOMER_NAME',
            '$CUSTOMER_EMAIL',
            '$CONTENT_DETAILS',
            '$TOTAL_AMOUNT',
        ];
    }

    public function invoice(): static
    {
        return $this->state([
            'type' => 'invoice',
            'category' => 'sales',
            'content' => $this->generateInvoiceContent(),
        ]);
    }

    public function receipt(): static
    {
        return $this->state([
            'type' => 'receipt',
            'category' => 'sales',
            'content' => $this->generateReceiptContent(),
        ]);
    }

    private function generateInvoiceContent(): string
    {
        return '<html>
<head>
    <title>Invoice - $INVOICE_NUMBER</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-header { text-align: center; margin-bottom: 30px; }
        .invoice-details { margin: 20px 0; }
        .invoice-items { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1>INVOICE</h1>
        <p>Invoice Number: $INVOICE_NUMBER</p>
        <p>Date: $INVOICE_DATE</p>
    </div>
    
    <div class="invoice-details">
        <h3>Bill To:</h3>
        <p>$CUSTOMER_NAME</p>
        <p>$CUSTOMER_ADDRESS</p>
        <p>$CUSTOMER_EMAIL</p>
    </div>
    
    <div class="invoice-items">
        <h3>Items:</h3>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                $INVOICE_ITEMS
            </tbody>
        </table>
        
        <p class="total">Total: $INVOICE_TOTAL</p>
    </div>
</body>
</html>';
    }

    private function generateReceiptContent(): string
    {
        return '<html>
<head>
    <title>Receipt - $RECEIPT_NUMBER</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; text-align: center; }
        .receipt { max-width: 300px; margin: 0 auto; }
        .items { text-align: left; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="receipt">
        <h2>$COMPANY_NAME</h2>
        <p>Receipt #$RECEIPT_NUMBER</p>
        <p>$RECEIPT_DATE</p>
        
        <div class="items">
            $RECEIPT_ITEMS
        </div>
        
        <p><strong>Total: $RECEIPT_TOTAL</strong></p>
        <p>Thank you for your purchase!</p>
    </div>
</body>
</html>';
    }
}
