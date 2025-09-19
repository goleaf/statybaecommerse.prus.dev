<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing templates and users
        $templates = DocumentTemplate::all();
        $users = User::all();
        $orders = Order::all();

        if ($templates->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No templates or users found. Skipping document seeding.');
            return;
        }

        $documents = [
            [
                'title' => 'Invoice #INV-001',
                'content' => $this->getInvoiceContent(),
                'variables' => [
                    'company_name' => 'Statybae Commerce',
                    'order_number' => 'ORD-001',
                    'order_date' => now()->format('Y-m-d'),
                    'customer_name' => 'John Doe',
                    'order_total' => '€299.99',
                ],
                'status' => 'published',
                'format' => 'pdf',
                'documentable_type' => Order::class,
                'documentable_id' => $orders->first()?->id ?? 1,
                'created_by' => $users->first()?->id ?? 1,
                'generated_at' => now(),
            ],
            [
                'title' => 'Receipt #RCP-001',
                'content' => $this->getReceiptContent(),
                'variables' => [
                    'company_name' => 'Statybae Commerce',
                    'order_number' => 'ORD-002',
                    'order_date' => now()->format('Y-m-d'),
                    'customer_name' => 'Jane Smith',
                    'order_total' => '€149.99',
                ],
                'status' => 'generated',
                'format' => 'pdf',
                'documentable_type' => Order::class,
                'documentable_id' => $orders->skip(1)->first()?->id ?? 2,
                'created_by' => $users->first()?->id ?? 1,
                'generated_at' => now()->subHours(2),
            ],
            [
                'title' => 'Draft Document #DRAFT-001',
                'content' => $this->getDraftContent(),
                'variables' => [
                    'company_name' => 'Statybae Commerce',
                    'document_type' => 'Contract',
                    'client_name' => 'ABC Company',
                ],
                'status' => 'draft',
                'format' => 'html',
                'documentable_type' => Order::class,
                'documentable_id' => $orders->skip(2)->first()?->id ?? 3,
                'created_by' => $users->first()?->id ?? 1,
            ],
            [
                'title' => 'Contract Template #CTR-001',
                'content' => $this->getContractContent(),
                'variables' => [
                    'company_name' => 'Statybae Commerce',
                    'client_name' => 'XYZ Corporation',
                    'contract_date' => now()->format('Y-m-d'),
                    'contract_value' => '€5,000.00',
                ],
                'status' => 'published',
                'format' => 'docx',
                'documentable_type' => Order::class,
                'documentable_id' => $orders->skip(3)->first()?->id ?? 4,
                'created_by' => $users->first()?->id ?? 1,
                'generated_at' => now()->subDays(1),
            ],
            [
                'title' => 'Report #RPT-001',
                'content' => $this->getReportContent(),
                'variables' => [
                    'company_name' => 'Statybae Commerce',
                    'report_period' => 'Q1 2024',
                    'total_sales' => '€25,000.00',
                    'total_orders' => '150',
                ],
                'status' => 'generated',
                'format' => 'pdf',
                'documentable_type' => Order::class,
                'documentable_id' => $orders->skip(4)->first()?->id ?? 5,
                'created_by' => $users->first()?->id ?? 1,
                'generated_at' => now()->subHours(5),
            ],
        ];

        foreach ($documents as $documentData) {
            $template = $templates->random();
            $user = $users->random();

            Document::create(array_merge($documentData, [
                'document_template_id' => $template->id,
                'created_by' => $user->id,
            ]));
        }

        $this->command->info('Created ' . count($documents) . ' documents.');
    }

    private function getInvoiceContent(): string
    {
        return '
        <div class="invoice">
            <h1>Invoice</h1>
            <div class="company-info">
                <h2>{{$COMPANY_NAME}}</h2>
                <p>Address: {{$COMPANY_ADDRESS}}</p>
                <p>Phone: {{$COMPANY_PHONE}}</p>
                <p>Email: {{$COMPANY_EMAIL}}</p>
            </div>
            <div class="invoice-details">
                <p><strong>Invoice Number:</strong> {{$ORDER_NUMBER}}</p>
                <p><strong>Date:</strong> {{$ORDER_DATE}}</p>
                <p><strong>Total:</strong> {{$ORDER_TOTAL}}</p>
            </div>
            <div class="customer-info">
                <h3>Bill To:</h3>
                <p>{{$CUSTOMER_NAME}}</p>
                <p>{{$CUSTOMER_EMAIL}}</p>
            </div>
        </div>';
    }

    private function getReceiptContent(): string
    {
        return '
        <div class="receipt">
            <h1>Receipt</h1>
            <div class="company-info">
                <h2>{{$COMPANY_NAME}}</h2>
            </div>
            <div class="receipt-details">
                <p><strong>Receipt Number:</strong> {{$ORDER_NUMBER}}</p>
                <p><strong>Date:</strong> {{$ORDER_DATE}}</p>
                <p><strong>Total:</strong> {{$ORDER_TOTAL}}</p>
            </div>
            <div class="customer-info">
                <p>Thank you for your purchase, {{$CUSTOMER_NAME}}!</p>
            </div>
        </div>';
    }

    private function getDraftContent(): string
    {
        return '
        <div class="draft-document">
            <h1>Draft Document</h1>
            <div class="document-info">
                <p><strong>Company:</strong> {{$COMPANY_NAME}}</p>
                <p><strong>Document Type:</strong> {{$DOCUMENT_TYPE}}</p>
                <p><strong>Client:</strong> {{$CLIENT_NAME}}</p>
            </div>
            <div class="content">
                <p>This is a draft document. Content will be finalized later.</p>
            </div>
        </div>';
    }

    private function getContractContent(): string
    {
        return '
        <div class="contract">
            <h1>Service Contract</h1>
            <div class="parties">
                <div class="company">
                    <h3>{{$COMPANY_NAME}}</h3>
                </div>
                <div class="client">
                    <h3>{{$CLIENT_NAME}}</h3>
                </div>
            </div>
            <div class="contract-details">
                <p><strong>Contract Date:</strong> {{$CONTRACT_DATE}}</p>
                <p><strong>Contract Value:</strong> {{$CONTRACT_VALUE}}</p>
            </div>
            <div class="terms">
                <h3>Terms and Conditions</h3>
                <p>This contract outlines the terms of service between the parties.</p>
            </div>
        </div>';
    }

    private function getReportContent(): string
    {
        return '
        <div class="report">
            <h1>Sales Report</h1>
            <div class="company-info">
                <h2>{{$COMPANY_NAME}}</h2>
            </div>
            <div class="report-details">
                <p><strong>Report Period:</strong> {{$REPORT_PERIOD}}</p>
                <p><strong>Total Sales:</strong> {{$TOTAL_SALES}}</p>
                <p><strong>Total Orders:</strong> {{$TOTAL_ORDERS}}</p>
            </div>
            <div class="summary">
                <h3>Summary</h3>
                <p>This report shows the sales performance for the specified period.</p>
            </div>
        </div>';
    }
}

