<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

final class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedInvoiceTemplates();
        $this->seedQuoteTemplates();
        $this->seedReceiptTemplates();
        $this->seedAdditionalTemplates();
    }

    private function seedInvoiceTemplates(): void
    {
        $data = DocumentTemplate::factory()
            ->invoice()
            ->state([
                'slug' => 'invoice-template',
                'name' => 'Invoice Template',
                'description' => 'Standard invoice template for billing',
                'content' => '<h1>Invoice #{{invoice_number}}</h1><p>Date: {{invoice_date}}</p><p>Customer: {{customer_name}}</p><p>Amount: €{{total_amount}}</p>',
                'variables' => ['invoice_number', 'invoice_date', 'customer_name', 'total_amount'],
                'settings' => [
                    'page_size' => 'A4',
                    'orientation' => 'portrait',
                    'margins' => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20],
                ],
                'is_active' => true,
            ])
            ->make()
            ->toArray();

        DocumentTemplate::query()->updateOrCreate(['slug' => 'invoice-template'], $data);
    }

    private function seedQuoteTemplates(): void
    {
        $data = DocumentTemplate::factory()
            ->quote()
            ->state([
                'slug' => 'quote-template',
                'name' => 'Quote Template',
                'description' => 'Professional quote template',
                'content' => '<h1>Quote #{{quote_number}}</h1><p>Valid until: {{valid_until}}</p><p>Customer: {{customer_name}}</p><p>Total: €{{total_amount}}</p>',
                'variables' => ['quote_number', 'valid_until', 'customer_name', 'total_amount'],
                'settings' => [
                    'page_size' => 'A4',
                    'orientation' => 'portrait',
                ],
                'is_active' => true,
            ])
            ->make()
            ->toArray();

        DocumentTemplate::query()->updateOrCreate(['slug' => 'quote-template'], $data);
    }

    private function seedReceiptTemplates(): void
    {
        $data = DocumentTemplate::factory()
            ->receipt()
            ->state([
                'slug' => 'receipt-template',
                'name' => 'Receipt Template',
                'description' => 'Simple receipt template',
                'content' => '<h1>Receipt</h1><p>Date: {{receipt_date}}</p><p>Amount: €{{amount}}</p><p>Payment method: {{payment_method}}</p>',
                'variables' => ['receipt_date', 'amount', 'payment_method'],
                'settings' => [
                    'page_size' => 'A4',
                    'orientation' => 'portrait',
                ],
                'is_active' => true,
            ])
            ->make()
            ->toArray();

        DocumentTemplate::query()->updateOrCreate(['slug' => 'receipt-template'], $data);
    }

    private function seedAdditionalTemplates(): void
    {
        DocumentTemplate::factory()->count(15)->create();
    }
}
