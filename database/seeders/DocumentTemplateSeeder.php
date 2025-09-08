<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

final class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Invoice Template',
                'slug' => 'invoice-template',
                'description' => 'Standard invoice template for orders',
                'type' => 'invoice',
                'category' => 'sales',
                'content' => $this->getInvoiceTemplate(),
                'variables' => [
                    '$COMPANY_NAME', '$COMPANY_ADDRESS', '$COMPANY_PHONE', '$COMPANY_EMAIL',
                    '$ORDER_NUMBER', '$ORDER_DATE', '$ORDER_TOTAL', '$ORDER_SUBTOTAL',
                    '$ORDER_TAX', '$ORDER_SHIPPING', '$CUSTOMER_NAME', '$CUSTOMER_EMAIL',
                    '$BILLING_ADDRESS', '$SHIPPING_ADDRESS'
                ],
                'settings' => [
                    'page_size' => 'A4',
                    'orientation' => 'portrait',
                    'margins' => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Receipt Template',
                'slug' => 'receipt-template',
                'description' => 'Simple receipt template for purchases',
                'type' => 'receipt',
                'category' => 'sales',
                'content' => $this->getReceiptTemplate(),
                'variables' => [
                    '$COMPANY_NAME', '$ORDER_NUMBER', '$ORDER_DATE', '$ORDER_TOTAL',
                    '$CUSTOMER_NAME', '$CURRENT_DATE'
                ],
                'settings' => [
                    'page_size' => 'A4',
                    'orientation' => 'portrait',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Product Catalog',
                'slug' => 'product-catalog',
                'description' => 'Product catalog template with details',
                'type' => 'catalog',
                'category' => 'marketing',
                'content' => $this->getProductCatalogTemplate(),
                'variables' => [
                    '$COMPANY_NAME', '$PRODUCT_NAME', '$PRODUCT_SKU', '$PRODUCT_PRICE',
                    '$PRODUCT_DESCRIPTION', '$PRODUCT_BRAND', '$PRODUCT_CATEGORY',
                    '$CURRENT_DATE'
                ],
                'settings' => [
                    'page_size' => 'A4',
                    'orientation' => 'portrait',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Service Agreement',
                'slug' => 'service-agreement',
                'description' => 'Standard service agreement template',
                'type' => 'agreement',
                'category' => 'legal',
                'content' => $this->getServiceAgreementTemplate(),
                'variables' => [
                    '$COMPANY_NAME', '$COMPANY_ADDRESS', '$CUSTOMER_NAME',
                    '$CUSTOMER_EMAIL', '$CURRENT_DATE', '$CURRENT_YEAR'
                ],
                'settings' => [
                    'page_size' => 'A4',
                    'orientation' => 'portrait',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Order Confirmation',
                'slug' => 'order-confirmation',
                'description' => 'Order confirmation document',
                'type' => 'document',
                'category' => 'customer_service',
                'content' => $this->getOrderConfirmationTemplate(),
                'variables' => [
                    '$COMPANY_NAME', '$ORDER_NUMBER', '$ORDER_DATE', '$ORDER_TOTAL',
                    '$CUSTOMER_NAME', '$CUSTOMER_EMAIL', '$ORDER_STATUS'
                ],
                'settings' => [
                    'page_size' => 'A4',
                    'orientation' => 'portrait',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            DocumentTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }

    private function getInvoiceTemplate(): string
    {
        return '
<div style="max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #333; margin-bottom: 5px;">$COMPANY_NAME</h1>
        <p style="margin: 0; color: #666;">$COMPANY_ADDRESS</p>
        <p style="margin: 0; color: #666;">Phone: $COMPANY_PHONE | Email: $COMPANY_EMAIL</p>
    </div>
    
    <div style="border-bottom: 2px solid #333; margin-bottom: 20px;">
        <h2 style="color: #333; text-align: center;">INVOICE</h2>
    </div>
    
    <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
        <div>
            <h3 style="color: #333; margin-bottom: 10px;">Bill To:</h3>
            <p style="margin: 5px 0;"><strong>$CUSTOMER_NAME</strong></p>
            <p style="margin: 5px 0;">$CUSTOMER_EMAIL</p>
            <p style="margin: 5px 0;">$BILLING_ADDRESS</p>
        </div>
        <div style="text-align: right;">
            <p style="margin: 5px 0;"><strong>Invoice #:</strong> $ORDER_NUMBER</p>
            <p style="margin: 5px 0;"><strong>Date:</strong> $ORDER_DATE</p>
        </div>
    </div>
    
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
        <tr style="background-color: #f5f5f5;">
            <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Description</th>
            <th style="border: 1px solid #ddd; padding: 12px; text-align: right;">Amount</th>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 12px;">Order Subtotal</td>
            <td style="border: 1px solid #ddd; padding: 12px; text-align: right;">$ORDER_SUBTOTAL</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 12px;">Tax</td>
            <td style="border: 1px solid #ddd; padding: 12px; text-align: right;">$ORDER_TAX</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 12px;">Shipping</td>
            <td style="border: 1px solid #ddd; padding: 12px; text-align: right;">$ORDER_SHIPPING</td>
        </tr>
        <tr style="background-color: #f5f5f5; font-weight: bold;">
            <td style="border: 1px solid #ddd; padding: 12px;">Total</td>
            <td style="border: 1px solid #ddd; padding: 12px; text-align: right;">$ORDER_TOTAL</td>
        </tr>
    </table>
    
    <div style="margin-top: 40px; text-align: center; color: #666;">
        <p>Thank you for your business!</p>
    </div>
</div>';
    }

    private function getReceiptTemplate(): string
    {
        return '
<div style="max-width: 400px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif; text-align: center;">
    <h2 style="margin-bottom: 20px;">$COMPANY_NAME</h2>
    <h3 style="margin-bottom: 20px;">RECEIPT</h3>
    
    <div style="text-align: left; margin-bottom: 20px;">
        <p><strong>Receipt #:</strong> $ORDER_NUMBER</p>
        <p><strong>Date:</strong> $ORDER_DATE</p>
        <p><strong>Customer:</strong> $CUSTOMER_NAME</p>
    </div>
    
    <div style="border-top: 1px solid #333; border-bottom: 1px solid #333; padding: 10px 0; margin: 20px 0;">
        <p style="font-size: 18px; font-weight: bold;">Total: $ORDER_TOTAL</p>
    </div>
    
    <p style="margin-top: 30px; font-size: 12px; color: #666;">
        Thank you for your purchase!<br>
        Generated on $CURRENT_DATE
    </p>
</div>';
    }

    private function getProductCatalogTemplate(): string
    {
        return '
<div style="max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #333;">$COMPANY_NAME</h1>
        <h2 style="color: #666;">Product Catalog</h2>
        <p style="color: #666;">Generated on $CURRENT_DATE</p>
    </div>
    
    <div style="border: 1px solid #ddd; padding: 20px; margin-bottom: 20px;">
        <h3 style="color: #333; margin-bottom: 15px;">$PRODUCT_NAME</h3>
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span><strong>SKU:</strong> $PRODUCT_SKU</span>
            <span><strong>Price:</strong> $PRODUCT_PRICE</span>
        </div>
        <div style="margin-bottom: 10px;">
            <span><strong>Brand:</strong> $PRODUCT_BRAND</span>
            <span style="margin-left: 20px;"><strong>Category:</strong> $PRODUCT_CATEGORY</span>
        </div>
        <div style="margin-top: 15px;">
            <h4 style="color: #333;">Description:</h4>
            <p style="color: #666; line-height: 1.5;">$PRODUCT_DESCRIPTION</p>
        </div>
    </div>
</div>';
    }

    private function getServiceAgreementTemplate(): string
    {
        return '
<div style="max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #333;">SERVICE AGREEMENT</h1>
    </div>
    
    <p style="margin-bottom: 20px;">
        This Service Agreement ("Agreement") is entered into on $CURRENT_DATE between:
    </p>
    
    <div style="margin-bottom: 30px;">
        <p><strong>Service Provider:</strong><br>
        $COMPANY_NAME<br>
        $COMPANY_ADDRESS</p>
        
        <p><strong>Client:</strong><br>
        $CUSTOMER_NAME<br>
        $CUSTOMER_EMAIL</p>
    </div>
    
    <h3 style="color: #333;">Terms and Conditions</h3>
    <p style="line-height: 1.6; margin-bottom: 15px;">
        1. This agreement shall remain in effect from the date of signing until terminated by either party.
    </p>
    <p style="line-height: 1.6; margin-bottom: 15px;">
        2. Services will be provided as agreed upon between both parties.
    </p>
    <p style="line-height: 1.6; margin-bottom: 15px;">
        3. Payment terms will be as specified in separate invoices.
    </p>
    
    <div style="margin-top: 50px;">
        <p>Date: $CURRENT_DATE</p>
        <p>© $CURRENT_YEAR $COMPANY_NAME. All rights reserved.</p>
    </div>
</div>';
    }

    private function getOrderConfirmationTemplate(): string
    {
        return '
<div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #333;">$COMPANY_NAME</h1>
        <h2 style="color: #4CAF50;">Order Confirmation</h2>
    </div>
    
    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <p style="margin: 0; font-size: 16px;">
            <strong>Dear $CUSTOMER_NAME,</strong>
        </p>
        <p style="margin: 10px 0 0 0;">
            Thank you for your order! We have received your order and it is being processed.
        </p>
    </div>
    
    <div style="margin-bottom: 30px;">
        <h3 style="color: #333;">Order Details</h3>
        <p><strong>Order Number:</strong> $ORDER_NUMBER</p>
        <p><strong>Order Date:</strong> $ORDER_DATE</p>
        <p><strong>Status:</strong> $ORDER_STATUS</p>
        <p><strong>Total:</strong> $ORDER_TOTAL</p>
    </div>
    
    <div style="margin-bottom: 30px;">
        <h3 style="color: #333;">Customer Information</h3>
        <p><strong>Name:</strong> $CUSTOMER_NAME</p>
        <p><strong>Email:</strong> $CUSTOMER_EMAIL</p>
    </div>
    
    <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="color: #666;">
            If you have any questions about your order, please contact us.<br>
            Thank you for choosing $COMPANY_NAME!
        </p>
    </div>
</div>';
    }
}
