<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create invoice template
        DocumentTemplate::create([
            'name' => 'Invoice Template',
            'slug' => 'invoice-template',
            'description' => 'Standard invoice template for billing',
            'content' => '<h1>Invoice #{{invoice_number}}</h1><p>Date: {{invoice_date}}</p><p>Customer: {{customer_name}}</p><p>Amount: €{{total_amount}}</p>',
            'variables' => ['invoice_number', 'invoice_date', 'customer_name', 'total_amount'],
            'type' => 'invoice',
            'category' => 'business',
            'settings' => [
                'page_size' => 'A4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20],
            ],
            'is_active' => true,
        ]);

        // Create quote template
        DocumentTemplate::create([
            'name' => 'Quote Template',
            'slug' => 'quote-template',
            'description' => 'Professional quote template',
            'content' => '<h1>Quote #{{quote_number}}</h1><p>Valid until: {{valid_until}}</p><p>Customer: {{customer_name}}</p><p>Total: €{{total_amount}}</p>',
            'variables' => ['quote_number', 'valid_until', 'customer_name', 'total_amount'],
            'type' => 'quote',
            'category' => 'business',
            'settings' => [
                'page_size' => 'A4',
                'orientation' => 'portrait',
            ],
            'is_active' => true,
        ]);

        // Create receipt template
        DocumentTemplate::create([
            'name' => 'Receipt Template',
            'slug' => 'receipt-template',
            'description' => 'Simple receipt template',
            'content' => '<h1>Receipt</h1><p>Date: {{receipt_date}}</p><p>Amount: €{{amount}}</p><p>Payment method: {{payment_method}}</p>',
            'variables' => ['receipt_date', 'amount', 'payment_method'],
            'type' => 'receipt',
            'category' => 'financial',
            'settings' => [
                'page_size' => 'A4',
                'orientation' => 'portrait',
            ],
            'is_active' => true,
        ]);

        // Create additional templates
        DocumentTemplate::factory(15)->create();
    }
}
