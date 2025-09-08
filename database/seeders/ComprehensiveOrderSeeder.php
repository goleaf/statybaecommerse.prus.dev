<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Partner;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use App\Services\DocumentService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ComprehensiveOrderSeeder extends Seeder
{
    private array $orderStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    private array $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
    private array $paymentMethods = ['credit_card', 'paypal', 'bank_transfer', 'cash_on_delivery', 'stripe', 'mollie'];
    private array $currencies = ['EUR', 'USD', 'GBP'];
    private array $shippingCarriers = ['DPD', 'Omniva', 'LP Express', 'UPS', 'FedEx', 'DHL'];
    private array $shippingServices = ['Standard', 'Express', 'Next Day', 'Economy', 'Premium'];

    public function run(): void
    {
        $this->command->info('Starting comprehensive order seeding...');

        // Ensure we have required data
        $this->ensureRequiredData();

        // Generate orders for current and last month
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $this->command->info('Generating orders for current month...');
        $this->generateOrdersForPeriod($currentMonth, $currentMonth->copy()->endOfMonth(), 500);

        $this->command->info('Generating orders for last month...');
        $this->generateOrdersForPeriod($lastMonth, $lastMonth->copy()->endOfMonth(), 500);

        $this->command->info('Comprehensive order seeding completed!');
    }

    private function ensureRequiredData(): void
    {
        // Create users if needed
        if (User::count() < 50) {
            $this->command->info('Creating additional users...');
            User::factory(50)->create();
        }

        // Create products if needed
        if (Product::count() < 20) {
            $this->command->info('Creating additional products...');
            Product::factory(20)->create();
        }

        // Create currencies if needed
        $this->ensureCurrencies();

        // Create zones if needed
        if (Zone::count() === 0) {
            $this->command->info('Creating zones...');
            $this->createZones();
        }

        // Skip channels and partners as they don't exist in current schema

        // Ensure document templates exist
        if (DocumentTemplate::count() === 0) {
            $this->command->info('Creating document templates...');
            $this->call(DocumentTemplateSeeder::class);
        }
    }

    private function ensureCurrencies(): void
    {
        $currencies = [
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'exchange_rate' => 1.0],
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'exchange_rate' => 1.1],
            ['name' => 'British Pound', 'code' => 'GBP', 'symbol' => '£', 'exchange_rate' => 0.85],
        ];

        foreach ($currencies as $currencyData) {
            \App\Models\Currency::firstOrCreate(
                ['code' => $currencyData['code']],
                array_merge($currencyData, ['is_enabled' => true])
            );
        }
    }

    private function createZones(): void
    {
        $eurCurrency = \App\Models\Currency::where('code', 'EUR')->first();
        $usdCurrency = \App\Models\Currency::where('code', 'USD')->first();
        $gbpCurrency = \App\Models\Currency::where('code', 'GBP')->first();

        $zones = [
            [
                'name' => 'European Union',
                'slug' => 'european-union',
                'code' => 'EU',
                'is_enabled' => true,
                'is_default' => true,
                'currency_id' => $eurCurrency->id,
                'tax_rate' => 21.0,
                'shipping_rate' => 5.99,
                'sort_order' => 1,
                'metadata' => ['region' => 'europe'],
            ],
            [
                'name' => 'North America',
                'slug' => 'north-america',
                'code' => 'NA',
                'is_enabled' => true,
                'is_default' => false,
                'currency_id' => $usdCurrency->id,
                'tax_rate' => 8.5,
                'shipping_rate' => 15.99,
                'sort_order' => 2,
                'metadata' => ['region' => 'north_america'],
            ],
            [
                'name' => 'United Kingdom',
                'slug' => 'united-kingdom',
                'code' => 'UK',
                'is_enabled' => true,
                'is_default' => false,
                'currency_id' => $gbpCurrency->id,
                'tax_rate' => 20.0,
                'shipping_rate' => 8.99,
                'sort_order' => 3,
                'metadata' => ['region' => 'uk'],
            ],
        ];

        foreach ($zones as $zoneData) {
            Zone::firstOrCreate(
                ['code' => $zoneData['code']],
                $zoneData
            );
        }
    }

    private function generateOrdersForPeriod(Carbon $startDate, Carbon $endDate, int $count): void
    {
        $users = User::all();
        $products = Product::all();
        $zones = Zone::all();

        $invoiceTemplate = DocumentTemplate::where('type', 'invoice')->first();
        $receiptTemplate = DocumentTemplate::where('type', 'receipt')->first();

        DB::transaction(function () use ($startDate, $endDate, $count, $users, $products, $zones, $invoiceTemplate, $receiptTemplate) {
            for ($i = 0; $i < $count; $i++) {
                // Random date within the period
                $orderDate = Carbon::createFromTimestamp(
                    fake()->numberBetween($startDate->timestamp, $endDate->timestamp)
                );

                // Create order
                $order = $this->createOrder($orderDate, $users, $zones);

                // Create order items
                $this->createOrderItems($order, $products);

                // Create shipping information
                $this->createOrderShipping($order);

                // Generate documents
                $this->generateOrderDocuments($order, $invoiceTemplate, $receiptTemplate);

                if (($i + 1) % 50 === 0) {
                    $this->command->info('Generated ' . ($i + 1) . ' orders...');
                }
            }
        });
    }

    private function createOrder(Carbon $orderDate, $users, $zones): Order
    {
        $status = fake()->randomElement($this->orderStatuses);
        $paymentStatus = $this->getPaymentStatusForOrderStatus($status);
        $currency = fake()->randomElement($this->currencies);

        // Calculate amounts
        $subtotal = fake()->randomFloat(2, 10, 500);
        $taxRate = 0.21;  // 21% VAT
        $taxAmount = round($subtotal * $taxRate, 2);
        $shippingAmount = fake()->randomFloat(2, 0, 25);
        $discountAmount = fake()->optional(0.3)->randomFloat(2, 0, $subtotal * 0.2) ?? 0;
        $total = $subtotal + $taxAmount + $shippingAmount - $discountAmount;

        $order = Order::create([
            'number' => $this->generateOrderNumber(),
            'user_id' => $users->random()->id,
            'status' => $status,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'currency' => $currency,
            'billing_address' => json_encode($this->generateAddress()),
            'shipping_address' => fake()->boolean(80) ? json_encode($this->generateAddress()) : null,
            'notes' => fake()->optional(0.3)->sentence(),
            'shipped_at' => $this->getShippedDate($status, $orderDate),
            'delivered_at' => $this->getDeliveredDate($status, $orderDate),
            'zone_id' => null,  // Skip zone_id for now as it references sh_zones table
            'payment_status' => $paymentStatus,
            'payment_method' => fake()->randomElement($this->paymentMethods),
            'payment_reference' => fake()->optional(0.8)->uuid(),
            'tracking_number' => $this->generateTrackingNumber('DPD'),
            'estimated_delivery' => $this->getShippedDate($status, $orderDate)?->addDays(fake()->numberBetween(1, 7)),
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'metadata' => json_encode($this->generateTimeline($status, $orderDate)),
            'locale' => 'lt',
            'weight' => fake()->randomFloat(2, 0.5, 25.0),
            'fulfillment_status' => $this->getFulfillmentStatus($status),
            'created_at' => $orderDate,
            'updated_at' => $orderDate->copy()->addMinutes(fake()->numberBetween(1, 1440)),
        ]);

        return $order;
    }

    private function createOrderItems(Order $order, $products): void
    {
        $itemCount = fake()->numberBetween(1, 5);
        $usedProducts = [];

        for ($i = 0; $i < $itemCount; $i++) {
            // Avoid duplicate products in same order
            do {
                $product = $products->random();
            } while (in_array($product->id, $usedProducts));

            $usedProducts[] = $product->id;

            $quantity = fake()->numberBetween(1, 3);
            $unitPrice = fake()->randomFloat(2, 5, 200);
            $total = $quantity * $unitPrice;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'variant_id' => null,  // Assuming no variants for now
                'product_name' => $product->name,
                'product_sku' => $product->sku ?? fake()->unique()->bothify('SKU-####-????'),
                'quantity' => $quantity,
                'price' => $unitPrice,
                'total' => $total,
            ]);
        }
    }

    private function createOrderShipping(Order $order): void
    {
        if (!in_array($order->status, ['shipped', 'delivered'])) {
            return;
        }

        $carrier = fake()->randomElement($this->shippingCarriers);
        $service = fake()->randomElement($this->shippingServices);

        OrderShipping::create([
            'order_id' => $order->id,
            'carrier_name' => $carrier,
            'service' => $service,
            'tracking_number' => $this->generateTrackingNumber($carrier),
            'tracking_url' => $this->generateTrackingUrl($carrier),
            'shipped_at' => $order->shipped_at,
            'estimated_delivery' => $order->shipped_at?->addDays(fake()->numberBetween(1, 7)),
            'delivered_at' => $order->delivered_at,
            'weight' => fake()->randomFloat(3, 0.1, 10),
            'dimensions' => [
                'length' => fake()->numberBetween(10, 50),
                'width' => fake()->numberBetween(10, 50),
                'height' => fake()->numberBetween(5, 30),
            ],
            'cost' => floatval($order->shipping_amount ?? 0),
            'metadata' => [
                'pickup_location' => fake()->address(),
                'delivery_instructions' => fake()->optional(0.3)->sentence(),
            ],
        ]);
    }

    private function generateOrderDocuments(Order $order, ?DocumentTemplate $invoiceTemplate, ?DocumentTemplate $receiptTemplate): void
    {
        if (!$invoiceTemplate || !$receiptTemplate) {
            return;
        }

        try {
            // Generate invoice for all orders except cancelled
            if ($order->status !== 'cancelled') {
                $invoiceVariables = $this->extractOrderVariables($order, 'invoice');

                Document::create([
                    'document_template_id' => $invoiceTemplate->id,
                    'title' => "Sąskaita faktūra #{$order->number}",
                    'content' => $this->processTemplate($invoiceTemplate->content, $invoiceVariables),
                    'variables' => $invoiceVariables,
                    'status' => 'published',
                    'format' => 'pdf',
                    'file_path' => "documents/invoices/invoice-{$order->number}.pdf",
                    'documentable_type' => Order::class,
                    'documentable_id' => $order->id,
                    'created_by' => 1,  // Admin user
                    'generated_at' => $order->created_at->addMinutes(fake()->numberBetween(5, 60)),
                ]);
            }

            // Generate receipt for paid orders
            if (in_array($order->payment_status, ['paid'])) {
                $receiptVariables = $this->extractOrderVariables($order, 'receipt');

                Document::create([
                    'document_template_id' => $receiptTemplate->id,
                    'title' => "Kvitas #{$order->number}",
                    'content' => $this->processTemplate($receiptTemplate->content, $receiptVariables),
                    'variables' => $receiptVariables,
                    'status' => 'published',
                    'format' => 'pdf',
                    'file_path' => "documents/receipts/receipt-{$order->number}.pdf",
                    'documentable_type' => Order::class,
                    'documentable_id' => $order->id,
                    'created_by' => 1,  // Admin user
                    'generated_at' => $order->created_at->addMinutes(fake()->numberBetween(10, 120)),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to generate documents for order {$order->number}: " . $e->getMessage());
        }
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . date('Y') . '-' . str_pad((string) fake()->numberBetween(1000, 99999), 5, '0', STR_PAD_LEFT);
        } while (Order::where('number', $number)->exists());

        return $number;
    }

    private function generateAddress(): array
    {
        $lithuanianCounties = [
            'Alytaus apskritis', 'Kauno apskritis', 'Klaipėdos apskritis',
            'Marijampolės apskritis', 'Panevėžio apskritis', 'Šiaulių apskritis',
            'Tauragės apskritis', 'Telšių apskritis', 'Utenos apskritis', 'Vilniaus apskritis'
        ];

        return [
            'first_name' => fake('lt_LT')->firstName(),
            'last_name' => fake('lt_LT')->lastName(),
            'company' => fake()->optional(0.3)->company(),
            'address_line_1' => fake('lt_LT')->streetAddress(),
            'address_line_2' => fake()->optional(0.2)->secondaryAddress(),
            'city' => fake('lt_LT')->city(),
            'state' => fake()->randomElement($lithuanianCounties),
            'postal_code' => fake('lt_LT')->postcode(),
            'country' => 'LT',
            'phone' => fake('lt_LT')->phoneNumber(),
            'email' => fake()->email(),
        ];
    }

    private function getPaymentStatusForOrderStatus(string $orderStatus): string
    {
        return match ($orderStatus) {
            'pending' => fake()->randomElement(['pending', 'failed']),
            'processing', 'shipped', 'delivered' => 'paid',
            'cancelled' => fake()->randomElement(['pending', 'failed', 'refunded']),
            default => 'pending',
        };
    }

    private function getFulfillmentStatus(string $orderStatus): string
    {
        return match ($orderStatus) {
            'pending' => 'unfulfilled',
            'processing' => 'partial',
            'shipped' => 'fulfilled',
            'delivered' => 'fulfilled',
            'cancelled' => 'unfulfilled',
            default => 'unfulfilled',
        };
    }

    private function getShippedDate(string $status, Carbon $orderDate): ?Carbon
    {
        if (!in_array($status, ['shipped', 'delivered'])) {
            return null;
        }

        return $orderDate->copy()->addDays(fake()->numberBetween(1, 5));
    }

    private function getDeliveredDate(string $status, Carbon $orderDate): ?Carbon
    {
        if ($status !== 'delivered') {
            return null;
        }

        $shippedDate = $this->getShippedDate($status, $orderDate);
        return $shippedDate?->addDays(fake()->numberBetween(1, 7));
    }

    private function generateTimeline(string $status, Carbon $orderDate): array
    {
        $timeline = [
            [
                'status' => 'pending',
                'timestamp' => $orderDate->toISOString(),
                'note' => 'Užsakymas sukurtas',
            ],
        ];

        if (in_array($status, ['processing', 'shipped', 'delivered'])) {
            $timeline[] = [
                'status' => 'processing',
                'timestamp' => $orderDate->copy()->addHours(fake()->numberBetween(1, 24))->toISOString(),
                'note' => 'Užsakymas apdorojamas',
            ];
        }

        if (in_array($status, ['shipped', 'delivered'])) {
            $timeline[] = [
                'status' => 'shipped',
                'timestamp' => $orderDate->copy()->addDays(fake()->numberBetween(1, 5))->toISOString(),
                'note' => 'Užsakymas išsiųstas',
            ];
        }

        if ($status === 'delivered') {
            $timeline[] = [
                'status' => 'delivered',
                'timestamp' => $orderDate->copy()->addDays(fake()->numberBetween(3, 10))->toISOString(),
                'note' => 'Užsakymas pristatytas',
            ];
        }

        if ($status === 'cancelled') {
            $timeline[] = [
                'status' => 'cancelled',
                'timestamp' => $orderDate->copy()->addHours(fake()->numberBetween(1, 48))->toISOString(),
                'note' => 'Užsakymas atšauktas',
            ];
        }

        return $timeline;
    }

    private function generateTrackingNumber(string $carrier): string
    {
        return match ($carrier) {
            'DPD' => fake()->numerify('##.###.###.##'),
            'Omniva' => fake()->bothify('OM########LT'),
            'LP Express' => fake()->numerify('LP########'),
            'UPS' => fake()->bothify('1Z###A##########'),
            'FedEx' => fake()->numerify('####.####.####'),
            'DHL' => fake()->numerify('##########'),
            default => fake()->bothify('TRK########'),
        };
    }

    private function generateTrackingUrl(string $carrier): string
    {
        $trackingNumber = fake()->bothify('########');

        return match ($carrier) {
            'DPD' => "https://www.dpd.com/lt/tracking?trackingNumber={$trackingNumber}",
            'Omniva' => "https://www.omniva.lt/tracking?id={$trackingNumber}",
            'LP Express' => "https://www.lpexpress.lt/tracking/{$trackingNumber}",
            'UPS' => "https://www.ups.com/track?tracknum={$trackingNumber}",
            'FedEx' => "https://www.fedex.com/tracking/?trknbr={$trackingNumber}",
            'DHL' => "https://www.dhl.com/tracking/{$trackingNumber}",
            default => "https://tracking.example.com/{$trackingNumber}",
        };
    }

    private function extractOrderVariables(Order $order, string $documentType): array
    {
        $user = $order->user;
        $billingAddress = is_string($order->billing_address) ? json_decode($order->billing_address, true) : $order->billing_address;
        $shippingAddress = is_string($order->shipping_address) ? json_decode($order->shipping_address, true) : $order->shipping_address;
        $shippingAddress = $shippingAddress ?? $billingAddress;

        $baseVariables = [
            '$COMPANY_NAME' => 'Statyba E-commerce',
            '$COMPANY_ADDRESS' => 'Vilniaus g. 123, Vilnius, Lietuva',
            '$COMPANY_PHONE' => '+370 600 12345',
            '$COMPANY_EMAIL' => 'info@statybaecommerce.lt',
            '$COMPANY_WEBSITE' => 'https://statybaecommerce.lt',
            '$ORDER_NUMBER' => $order->number,
            '$ORDER_DATE' => $order->created_at->format('Y-m-d'),
            '$ORDER_TOTAL' => number_format(floatval($order->total ?? 0), 2) . ' €',
            '$ORDER_SUBTOTAL' => number_format(floatval($order->subtotal ?? 0), 2) . ' €',
            '$ORDER_TAX' => number_format(floatval($order->tax_amount ?? 0), 2) . ' €',
            '$ORDER_SHIPPING' => number_format(floatval($order->shipping_amount ?? 0), 2) . ' €',
            '$ORDER_DISCOUNT' => number_format(floatval($order->discount_amount ?? 0), 2) . ' €',
            '$CUSTOMER_NAME' => $user ? "{$user->first_name} {$user->last_name}" : 'Svečias',
            '$CUSTOMER_EMAIL' => $user?->email ?? $billingAddress['email'] ?? '',
            '$BILLING_ADDRESS' => $this->formatAddress($billingAddress),
            '$SHIPPING_ADDRESS' => $this->formatAddress($shippingAddress),
            '$CURRENT_DATE' => now()->format('Y-m-d'),
            '$PAYMENT_METHOD' => $this->translatePaymentMethod($order->payment_method),
            '$PAYMENT_STATUS' => $this->translatePaymentStatus($order->payment_status),
        ];

        if ($documentType === 'invoice') {
            $baseVariables['$DOCUMENT_TYPE'] = 'Sąskaita faktūra';
            $baseVariables['$INVOICE_NUMBER'] = $order->number;
        } elseif ($documentType === 'receipt') {
            $baseVariables['$DOCUMENT_TYPE'] = 'Kvitas';
            $baseVariables['$RECEIPT_NUMBER'] = $order->number;
        }

        return $baseVariables;
    }

    private function formatAddress(?array $address): string
    {
        if (!$address) {
            return '';
        }

        $parts = [];

        if (!empty($address['first_name']) || !empty($address['last_name'])) {
            $parts[] = trim(($address['first_name'] ?? '') . ' ' . ($address['last_name'] ?? ''));
        }

        if (!empty($address['company'])) {
            $parts[] = $address['company'];
        }

        if (!empty($address['address_line_1'])) {
            $parts[] = $address['address_line_1'];
        }

        if (!empty($address['address_line_2'])) {
            $parts[] = $address['address_line_2'];
        }

        $cityLine = [];
        if (!empty($address['postal_code'])) {
            $cityLine[] = $address['postal_code'];
        }
        if (!empty($address['city'])) {
            $cityLine[] = $address['city'];
        }
        if (!empty($cityLine)) {
            $parts[] = implode(' ', $cityLine);
        }

        if (!empty($address['country'])) {
            $parts[] = $address['country'];
        }

        return implode("\n", $parts);
    }

    private function translatePaymentMethod(string $method): string
    {
        return match ($method) {
            'credit_card' => 'Kredito kortelė',
            'paypal' => 'PayPal',
            'bank_transfer' => 'Banko pavedimas',
            'cash_on_delivery' => 'Atsiskaitymas pristatymo metu',
            'stripe' => 'Stripe',
            'mollie' => 'Mollie',
            default => ucfirst($method),
        };
    }

    private function translatePaymentStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'Laukiama apmokėjimo',
            'paid' => 'Apmokėta',
            'failed' => 'Apmokėjimas nepavyko',
            'refunded' => 'Grąžinta',
            default => ucfirst($status),
        };
    }

    private function processTemplate(string $content, array $variables): string
    {
        foreach ($variables as $variable => $value) {
            $content = str_replace($variable, (string) $value, $content);
        }

        return $content;
    }
}
