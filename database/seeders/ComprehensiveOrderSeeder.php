<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Country;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

final class ComprehensiveOrderSeeder extends Seeder
{
    /**
     * Maintain the next sequential order number across seeding passes so reruns remain idempotent.
     */
    private int $nextOrderSequence = 1;

    private array $orderStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    private array $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];

    private array $paymentMethods = [
        // The list mirrors App\Enums\PaymentMethod so seed data respects enum casts on the Order model.
        'credit_card',
        'paypal',
        'bank_transfer',
        'cash_on_delivery',
        'stripe',
        'apple_pay',
        'google_pay',
    ];

    private array $currencies = ['EUR'];

    private array $shippingCarriers = ['DPD', 'Omniva', 'LP Express', 'UPS', 'FedEx', 'DHL'];

    private array $countrySeeds = [
        // Curated countries keep enum-bound factories deterministic and sidestep faker uniqueness collisions.
        [
            'name'               => 'Lithuania',
            'name_official'      => 'Republic of Lithuania',
            'cca2'               => 'LT',
            'cca3'               => 'LTU',
            'code'               => 'LTU',
            'iso_code'           => 'LTU',
            'currency_code'      => 'EUR',
            'currency_symbol'    => '€',
            'phone_code'         => '370',
            'phone_calling_code' => '370',
            'region'             => 'Europe',
            'subregion'          => 'Northern Europe',
            'timezone'           => 'Europe/Vilnius',
            'languages'          => ['lt' => 'Lithuanian'],
            'timezones'          => ['Europe/Vilnius' => 'Vilnius Time'],
            'is_active'          => true,
            'is_enabled'         => true,
            'is_eu_member'       => true,
            'requires_vat'       => true,
            'vat_rate'           => 21.00,
            'metadata'           => ['capital' => 'Vilnius'],
            'sort_order'         => 1,
        ],
        [
            'name'               => 'Latvia',
            'name_official'      => 'Republic of Latvia',
            'cca2'               => 'LV',
            'cca3'               => 'LVA',
            'code'               => 'LVA',
            'iso_code'           => 'LVA',
            'currency_code'      => 'EUR',
            'currency_symbol'    => '€',
            'phone_code'         => '371',
            'phone_calling_code' => '371',
            'region'             => 'Europe',
            'subregion'          => 'Northern Europe',
            'timezone'           => 'Europe/Riga',
            'languages'          => ['lv' => 'Latvian'],
            'timezones'          => ['Europe/Riga' => 'Riga Time'],
            'is_active'          => true,
            'is_enabled'         => true,
            'is_eu_member'       => true,
            'requires_vat'       => true,
            'vat_rate'           => 21.00,
            'metadata'           => ['capital' => 'Riga'],
            'sort_order'         => 2,
        ],
        [
            'name'               => 'Estonia',
            'name_official'      => 'Republic of Estonia',
            'cca2'               => 'EE',
            'cca3'               => 'EST',
            'code'               => 'EST',
            'iso_code'           => 'EST',
            'currency_code'      => 'EUR',
            'currency_symbol'    => '€',
            'phone_code'         => '372',
            'phone_calling_code' => '372',
            'region'             => 'Europe',
            'subregion'          => 'Northern Europe',
            'timezone'           => 'Europe/Tallinn',
            'languages'          => ['et' => 'Estonian'],
            'timezones'          => ['Europe/Tallinn' => 'Tallinn Time'],
            'is_active'          => true,
            'is_enabled'         => true,
            'is_eu_member'       => true,
            'requires_vat'       => true,
            'vat_rate'           => 20.00,
            'metadata'           => ['capital' => 'Tallinn'],
            'sort_order'         => 3,
        ],
    ];

    private array $shippingServices = ['Standard', 'Express', 'Next Day', 'Economy', 'Premium'];

    public function run(): void
    {
        // Gracefully announce the seeding phase even when the seeder is executed outside of Artisan. 
        $this->writeMessage('Starting comprehensive order seeding...');

        // Ensure we have required data
        $this->ensureRequiredData();

        // Prime the incremental order number counter using the existing records to avoid uniqueness conflicts.
        $this->initialiseOrderNumberSequence();

        // Generate orders for current and last month
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Trim the dataset dramatically during tests to keep the suite quick while preserving production richness otherwise.
        $orderCountPerPeriod = app()->runningUnitTests() ? 15 : 500;

        // Provide visibility for the currently seeded period for easier debugging in tests and locally.
        $this->writeMessage('Generating orders for current month...');
        $this->generateOrdersForPeriod($currentMonth, $currentMonth->copy()->endOfMonth(), $orderCountPerPeriod);

        // Mirror the message for the previous period so parallel runs stay understandable in logs.
        $this->writeMessage('Generating orders for last month...');
        $this->generateOrdersForPeriod($lastMonth, $lastMonth->copy()->endOfMonth(), $orderCountPerPeriod);

        // Final confirmation keeps CLI usage and programmatic usage consistent in their messaging.
        $this->writeMessage('Comprehensive order seeding completed!');
    }

    private function ensureRequiredData(): void
    {
        // Create users if needed
        if (User::count() < 50) {
            // Inform about factory backfills to understand why additional records appear in assertions.
            $this->writeMessage('Creating additional users...');
            User::factory(50)->create();
        }

        // Create products if needed
        if (Product::count() < 20) {
            // Mirror the user notification so products are tracked with the same verbosity level.
            $this->writeMessage('Creating additional products...');
            Product::factory(20)->create();
        }

        // Create currencies if needed
        $this->ensureCurrencies();

        // Populate a stable set of Baltic countries to keep related factories deterministic.
        $this->ensureCountries();

        // Skip channels and partners as they don't exist in current schema

        // Ensure document templates exist
        if (DocumentTemplate::count() === 0) {
            // Document template generation is announced to highlight indirect dependencies in tests.
            $this->writeMessage('Creating document templates...');
            $this->call(DocumentTemplateSeeder::class);
        }
    }

    private function ensureCurrencies(): void
    {
        $currenciesData = [
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'exchange_rate' => 1.0],
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'exchange_rate' => 1.1],
            ['name' => 'British Pound', 'code' => 'GBP', 'symbol' => '£', 'exchange_rate' => 0.85],
        ];

        foreach ($currenciesData as $data) {
            if (! \App\Models\Currency::where('code', $data['code'])->exists()) {
                \App\Models\Currency::factory()->create([
                    'code'          => $data['code'],
                    'name'          => $data['name'],
                    'symbol'        => $data['symbol'],
                    'exchange_rate' => $data['exchange_rate'],
                    'is_enabled'    => true,
                ]);
            }
        }
    }

    private function ensureCountries(): void
    {
        // Seed a predictable trio of countries so factories relying on them never violate unique constraints.
        foreach ($this->countrySeeds as $seed) {
            Country::query()->updateOrCreate(
                ['cca2' => $seed['cca2']],
                [
                    'name'               => $seed['name'],
                    'name_official'      => $seed['name_official'],
                    'cca3'               => $seed['cca3'],
                    'code'               => $seed['code'],
                    'iso_code'           => $seed['iso_code'],
                    'currency_code'      => $seed['currency_code'],
                    'currency_symbol'    => $seed['currency_symbol'],
                    'phone_code'         => $seed['phone_code'],
                    'phone_calling_code' => $seed['phone_calling_code'],
                    'region'             => $seed['region'],
                    'subregion'          => $seed['subregion'],
                    'timezone'           => $seed['timezone'],
                    'languages'          => $seed['languages'],
                    'timezones'          => $seed['timezones'],
                    'is_active'          => $seed['is_active'],
                    'is_enabled'         => $seed['is_enabled'],
                    'is_eu_member'       => $seed['is_eu_member'],
                    'requires_vat'       => $seed['requires_vat'],
                    'vat_rate'           => $seed['vat_rate'],
                    'metadata'           => $seed['metadata'],
                    'sort_order'         => $seed['sort_order'],
                ],
            );
        }
    }

    private function generateOrdersForPeriod(Carbon $startDate, Carbon $endDate, int $count): void
    {
        $users = User::all();
        $products = Product::all();
        $countries = Country::query()->get();
        $invoiceTemplate = DocumentTemplate::where('type', 'invoice')->first();
        $receiptTemplate = DocumentTemplate::where('type', 'receipt')->first();

        for ($i = 0; $i < $count; $i++) {
            // Random date within the period
            $orderDate = Carbon::createFromTimestamp(
                fake()->numberBetween($startDate->timestamp, $endDate->timestamp)
            );

            // Create order using factory
            $country = $countries->random();

            $order = Order::factory()
                ->for($users->random())
                ->state([
                    // Supply an explicit order number so rerunning the seeder never collides with persisted data.
                    'number'         => $this->nextOrderNumber(),
                    'created_at'     => $orderDate,
                    'updated_at'     => $orderDate->copy()->addMinutes(fake()->numberBetween(1, 1440)),
                    'status'         => fake()->randomElement($this->orderStatuses),
                    'payment_method' => fake()->randomElement($this->paymentMethods),
                    'currency'       => 'EUR',
                    'locale'         => 'lt',
                    'country_id'     => $country->id,
                    'billing_address' => $this->addressForCountry($country),
                    'shipping_address' => $this->addressForCountry($country),
                ])
                ->create();

            // Create order items using factory
            $itemCount = fake()->numberBetween(1, 5);
            $selectedProducts = $products->random($itemCount);

            foreach ($selectedProducts as $product) {
                OrderItem::factory()
                    ->for($order)
                    ->for($product)
                    ->create();
            }

            // Create shipping information using factory
            OrderShipping::factory()
                ->for($order)
                ->state([
                    'carrier' => fake()->randomElement($this->shippingCarriers),
                    'service' => fake()->randomElement($this->shippingServices),
                ])
                ->create();

            // Generate documents using factory
            $this->generateOrderDocuments($order, $invoiceTemplate, $receiptTemplate);

            if (($i + 1) % 50 === 0) {
                // The periodic progress update is useful for long seeding runs and should log safely anywhere.
                $this->writeMessage('Generated ' . ($i + 1) . ' orders...');
            }
        }
    }

    /**
     * Initialise the internal sequence counter after inspecting the database for the current maximum order number.
     */
    private function initialiseOrderNumberSequence(): void
    {
        $this->nextOrderSequence = $this->determineNextOrderSequence();
    }

    /**
     * Determine the next order sequence index while gracefully handling malformed legacy values.
     */
    private function determineNextOrderSequence(): int
    {
        $latestNumber = Order::query()
            ->whereNotNull('number')
            ->orderByDesc('number')
            ->value('number');

        if (! is_string($latestNumber)) {
            // No orders exist yet, so the very first seeded record should start at the canonical 1 suffix.
            return 1;
        }

        if (preg_match('/(\d+)$/', $latestNumber, $matches) === 1) {
            // Increment the trailing numeric segment while preserving leading zeros via sprintf later on.
            return ((int) $matches[1]) + 1;
        }

        // Fallback to a simple count-based offset whenever the stored number no longer matches the ORD-###### pattern.
        return Order::query()->count() + 1;
    }

    /**
     * Produce the next formatted order number and increment the counter for subsequent calls.
     */
    private function nextOrderNumber(): string
    {
        $currentSequence = $this->nextOrderSequence;
        $this->nextOrderSequence++;

        return sprintf('ORD-%06d', $currentSequence);
    }

    /**
     * Write a message either to the CLI output or to the application log when running silently.
     */
    private function writeMessage(string $message): void
    {
        // When the seeder is executed through Artisan the command property is populated and we can reuse it.
        if ($this->command instanceof \Illuminate\Console\Command) {
            $this->command->info($message);

            return;
        }

        // Fallback to the logger so the information is not lost during programmatic execution in tests.
        Log::info($message);
    }

    // Zones were removed from the project, so helper methods tied to zone creation were deleted.

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
                'order_id'           => $order->id,
                'product_id'         => $product->id,
                'product_variant_id' => null,  // Assuming no variants for now
                'name'               => $product->name,
                'sku'                => $product->sku ?? fake()->unique()->bothify('SKU-####-????'),
                'quantity'           => $quantity,
                'unit_price'         => $unitPrice,
                'total'              => $total,
            ]);
        }
    }

    private function createOrderShipping(Order $order): void
    {
        if (! in_array($order->status, ['shipped', 'delivered'])) {
            return;
        }

        $carrier = fake()->randomElement($this->shippingCarriers);
        $service = fake()->randomElement($this->shippingServices);

        OrderShipping::create([
            'order_id'           => $order->id,
            'carrier_name'       => $carrier,
            'service'            => $service,
            'tracking_number'    => $this->generateTrackingNumber($carrier),
            'tracking_url'       => $this->generateTrackingUrl($carrier),
            'shipped_at'         => $order->shipped_at,
            'estimated_delivery' => $order->shipped_at?->addDays(fake()->numberBetween(1, 7)),
            'delivered_at'       => $order->delivered_at,
            'weight'             => fake()->randomFloat(3, 0.1, 10),
            'dimensions'         => [
                'length' => fake()->numberBetween(10, 50),
                'width'  => fake()->numberBetween(10, 50),
                'height' => fake()->numberBetween(5, 30),
            ],
            'cost'     => floatval($order->shipping_amount ?? 0),
            'metadata' => [
                'pickup_location'       => fake()->address(),
                'delivery_instructions' => fake()->optional(0.3)->sentence(),
            ],
        ]);
    }

    private function generateOrderDocuments(Order $order, ?DocumentTemplate $invoiceTemplate, ?DocumentTemplate $receiptTemplate): void
    {
        if (! $invoiceTemplate || ! $receiptTemplate) {
            return;
        }

        try {
            // Generate invoice for all orders except cancelled using factory
            if ($order->status !== 'cancelled') {
                $invoiceVariables = $this->extractOrderVariables($order, 'invoice');

                Document::factory()
                    ->for($invoiceTemplate, 'documentTemplate')
                    ->state([
                        'title'             => "Sąskaita faktūra #{$order->number}",
                        'content'           => $this->processTemplate($invoiceTemplate->content, $invoiceVariables),
                        'variables'         => $invoiceVariables,
                        'status'            => 'published',
                        'format'            => 'pdf',
                        'file_path'         => "documents/invoices/invoice-{$order->number}.pdf",
                        'documentable_type' => Order::class,
                        'documentable_id'   => $order->id,
                        'created_by'        => 1,
                        'generated_at'      => $order->created_at->addMinutes(fake()->numberBetween(5, 60)),
                    ])
                    ->create();
            }

            // Generate receipt for paid orders using factory
            if (in_array($order->payment_status, ['paid'])) {
                $receiptVariables = $this->extractOrderVariables($order, 'receipt');

                Document::factory()
                    ->for($receiptTemplate, 'documentTemplate')
                    ->state([
                        'title'             => "Kvitas #{$order->number}",
                        'content'           => $this->processTemplate($receiptTemplate->content, $receiptVariables),
                        'variables'         => $receiptVariables,
                        'status'            => 'published',
                        'format'            => 'pdf',
                        'file_path'         => "documents/receipts/receipt-{$order->number}.pdf",
                        'documentable_type' => Order::class,
                        'documentable_id'   => $order->id,
                        'created_by'        => 1,
                        'generated_at'      => $order->created_at->addMinutes(fake()->numberBetween(10, 120)),
                    ])
                    ->create();
            }
        } catch (Exception $e) {
            Log::warning("Failed to generate documents for order {$order->number}: " . $e->getMessage());
        }
    }

    private function generateOrderNumber(): string
    {
        static $counter = 0;
        $counter++;

        do {
            $number = 'ORD-' . date('Y') . '-' . str_pad((string) (10000 + $counter), 5, '0', STR_PAD_LEFT);
        } while (Order::where('number', $number)->exists());

        return $number;
    }

    private function generateAddress(): array
    {
        $lithuanianCounties = [
            'Alytaus apskritis',
            'Kauno apskritis',
            'Klaipėdos apskritis',
            'Marijampolės apskritis',
            'Panevėžio apskritis',
            'Šiaulių apskritis',
            'Tauragės apskritis',
            'Telšių apskritis',
            'Utenos apskritis',
            'Vilniaus apskritis',
        ];

        return [
            'first_name'     => fake('lt_LT')->firstName(),
            'last_name'      => fake('lt_LT')->lastName(),
            'company'        => fake()->optional(0.3)->company(),
            'address_line_1' => fake('lt_LT')->streetAddress(),
            'address_line_2' => fake()->optional(0.2)->secondaryAddress(),
            'city'           => fake('lt_LT')->city(),
            'state'          => fake()->randomElement($lithuanianCounties),
            'postal_code'    => fake('lt_LT')->postcode(),
            'country'        => 'LT',
            'phone'          => fake('lt_LT')->phoneNumber(),
            'email'          => fake()->email(),
        ];
    }

    /**
     * Build an address snapshot aligned with the provided country metadata.
     */
    private function addressForCountry(Country $country): array
    {
        // Start from the localized Lithuanian template and adjust fields so downstream consumers pick up the right country.
        $address = $this->generateAddress();
        $address['country'] = $country->cca2;
        $address['state'] = $country->region ?? $address['state'];
        $address['city'] = $address['city'] ?? ($country->metadata['capital'] ?? $country->name);

        return $address;
    }

    private function getPaymentStatusForOrderStatus(string $orderStatus): string
    {
        return match ($orderStatus) {
            'pending' => fake()->randomElement(['pending', 'failed']),
            'processing', 'shipped', 'delivered' => 'paid',
            'cancelled' => fake()->randomElement(['pending', 'failed', 'refunded']),
            default     => 'pending',
        };
    }

    private function getFulfillmentStatus(string $orderStatus): string
    {
        return match ($orderStatus) {
            'pending'    => 'unfulfilled',
            'processing' => 'partial',
            'shipped'    => 'fulfilled',
            'delivered'  => 'fulfilled',
            'cancelled'  => 'unfulfilled',
            default      => 'unfulfilled',
        };
    }

    private function getShippedDate(string $status, Carbon $orderDate): ?Carbon
    {
        if (! in_array($status, ['shipped', 'delivered'])) {
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
                'status'    => 'pending',
                'timestamp' => $orderDate->toISOString(),
                'note'      => 'Užsakymas sukurtas',
            ],
        ];

        if (in_array($status, ['processing', 'shipped', 'delivered'])) {
            $timeline[] = [
                'status'    => 'processing',
                'timestamp' => $orderDate->copy()->addHours(fake()->numberBetween(1, 24))->toISOString(),
                'note'      => 'Užsakymas apdorojamas',
            ];
        }

        if (in_array($status, ['shipped', 'delivered'])) {
            $timeline[] = [
                'status'    => 'shipped',
                'timestamp' => $orderDate->copy()->addDays(fake()->numberBetween(1, 5))->toISOString(),
                'note'      => 'Užsakymas išsiųstas',
            ];
        }

        if ($status === 'delivered') {
            $timeline[] = [
                'status'    => 'delivered',
                'timestamp' => $orderDate->copy()->addDays(fake()->numberBetween(3, 10))->toISOString(),
                'note'      => 'Užsakymas pristatytas',
            ];
        }

        if ($status === 'cancelled') {
            $timeline[] = [
                'status'    => 'cancelled',
                'timestamp' => $orderDate->copy()->addHours(fake()->numberBetween(1, 48))->toISOString(),
                'note'      => 'Užsakymas atšauktas',
            ];
        }

        return $timeline;
    }

    private function generateTrackingNumber(string $carrier): string
    {
        return match ($carrier) {
            'DPD'        => fake()->numerify('##.###.###.##'),
            'Omniva'     => fake()->bothify('OM########LT'),
            'LP Express' => fake()->numerify('LP########'),
            'UPS'        => fake()->bothify('1Z###A##########'),
            'FedEx'      => fake()->numerify('####.####.####'),
            'DHL'        => fake()->numerify('##########'),
            default      => fake()->bothify('TRK########'),
        };
    }

    private function generateTrackingUrl(string $carrier): string
    {
        $trackingNumber = fake()->bothify('########');

        return match ($carrier) {
            'DPD'        => "https://www.dpd.com/lt/tracking?trackingNumber={$trackingNumber}",
            'Omniva'     => "https://www.omniva.lt/tracking?id={$trackingNumber}",
            'LP Express' => "https://www.lpexpress.lt/tracking/{$trackingNumber}",
            'UPS'        => "https://www.ups.com/track?tracknum={$trackingNumber}",
            'FedEx'      => "https://www.fedex.com/tracking/?trknbr={$trackingNumber}",
            'DHL'        => "https://www.dhl.com/tracking/{$trackingNumber}",
            default      => "https://tracking.example.com/{$trackingNumber}",
        };
    }

    private function extractOrderVariables(Order $order, string $documentType): array
    {
        $user = $order->user;
        $billingAddress = is_string($order->billing_address) ? json_decode($order->billing_address, true) : $order->billing_address;
        $shippingAddress = is_string($order->shipping_address) ? json_decode($order->shipping_address, true) : $order->shipping_address;
        $shippingAddress = $shippingAddress ?? $billingAddress;

        $baseVariables = [
            '$COMPANY_NAME'     => 'Statyba E-commerce',
            '$COMPANY_ADDRESS'  => 'Vilniaus g. 123, Vilnius, Lietuva',
            '$COMPANY_PHONE'    => '+370 600 12345',
            '$COMPANY_EMAIL'    => 'info@statybaecommerce.lt',
            '$COMPANY_WEBSITE'  => 'https://statybaecommerce.lt',
            '$ORDER_NUMBER'     => $order->number,
            '$ORDER_DATE'       => $order->created_at->format('Y-m-d'),
            '$ORDER_TOTAL'      => number_format(floatval($order->total ?? 0), 2) . ' €',
            '$ORDER_SUBTOTAL'   => number_format(floatval($order->subtotal ?? 0), 2) . ' €',
            '$ORDER_TAX'        => number_format(floatval($order->tax_amount ?? 0), 2) . ' €',
            '$ORDER_SHIPPING'   => number_format(floatval($order->shipping_amount ?? 0), 2) . ' €',
            '$ORDER_DISCOUNT'   => number_format(floatval($order->discount_amount ?? 0), 2) . ' €',
            '$CUSTOMER_NAME'    => $user ? "{$user->first_name} {$user->last_name}" : 'Svečias',
            '$CUSTOMER_EMAIL'   => $user?->email ?? $billingAddress['email'] ?? '',
            '$BILLING_ADDRESS'  => $this->formatAddress($billingAddress),
            '$SHIPPING_ADDRESS' => $this->formatAddress($shippingAddress),
            '$CURRENT_DATE'     => now()->format('Y-m-d'),
            '$PAYMENT_METHOD'   => $this->translatePaymentMethod($order->payment_method),
            '$PAYMENT_STATUS'   => $this->translatePaymentStatus($order->payment_status),
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
        if (! $address) {
            return '';
        }

        $parts = [];

        if (! empty($address['first_name']) || ! empty($address['last_name'])) {
            $parts[] = trim(($address['first_name'] ?? '') . ' ' . ($address['last_name'] ?? ''));
        }

        if (! empty($address['company'])) {
            $parts[] = $address['company'];
        }

        if (! empty($address['address_line_1'])) {
            $parts[] = $address['address_line_1'];
        }

        if (! empty($address['address_line_2'])) {
            $parts[] = $address['address_line_2'];
        }

        $cityLine = [];
        if (! empty($address['postal_code'])) {
            $cityLine[] = $address['postal_code'];
        }
        if (! empty($address['city'])) {
            $cityLine[] = $address['city'];
        }
        if (! empty($cityLine)) {
            $parts[] = implode(' ', $cityLine);
        }

        if (! empty($address['country'])) {
            $parts[] = $address['country'];
        }

        return implode("\n", $parts);
    }

    private function translatePaymentMethod(string|PaymentMethod $method): string
    {
        // Normalize to the raw backing value so enums and strings are handled uniformly.
        $value = $method instanceof PaymentMethod ? $method->value : $method;

        return match ($value) {
            'credit_card'      => 'Kredito kortelė',
            'paypal'           => 'PayPal',
            'bank_transfer'    => 'Banko pavedimas',
            'cash_on_delivery' => 'Atsiskaitymas pristatymo metu',
            'stripe'           => 'Stripe',
            'apple_pay'        => 'Apple Pay',
            'google_pay'       => 'Google Pay',
            default            => ucfirst($value),
        };
    }

    private function translatePaymentStatus(string|PaymentStatus $status): string
    {
        // Normalize enum instances so string comparisons below remain straightforward.
        $value = $status instanceof PaymentStatus ? $status->value : $status;

        return match ($value) {
            'pending'             => 'Laukiama apmokėjimo',
            'authorized'          => 'Autorizuota',
            'captured'            => 'Captuota',
            'settled'             => 'Atsiskaityta',
            'paid'                => 'Apmokėta',
            'partially_refunded'  => 'Iš dalies grąžinta',
            'refunded'            => 'Grąžinta',
            'failed'              => 'Apmokėjimas nepavyko',
            default               => ucfirst($value),
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
