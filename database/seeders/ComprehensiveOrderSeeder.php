<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

final class ComprehensiveOrderSeeder extends BaseSeeder
{
    /**
     * Maintain the next sequential order number across seeding passes.
     */
    private int $nextOrderSequence = 1;

    private array $shippingCarriers = ['DPD', 'Omniva', 'LP Express', 'UPS', 'FedEx', 'DHL'];

    private array $shippingServices = ['Standard', 'Express', 'Next Day', 'Economy', 'Premium'];

    private array $countrySeeds = [
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
    ];

    public function run(): void
    {
        $this->writeMessage('Starting comprehensive order seeding...');

        // 1. Clean slate
        $this->truncateTables();

        // 2. Ensure dependencies
        $this->ensureRequiredData();

        // 3. Create/Get Clients
        $clients = $this->ensureClients();

        // 4. Generate Orders
        $this->generateOrdersForClients($clients);

        $this->writeMessage('Comprehensive order seeding completed!');
    }

    private function truncateTables(): void
    {
        $this->writeMessage('Truncating order tables...');

        Schema::disableForeignKeyConstraints();

        Order::truncate();
        OrderItem::truncate();
        OrderShipping::truncate();

        Schema::enableForeignKeyConstraints();
    }

    private function createServicesForOrder(Order $order): void
    {
        $serviceCount = rand(0, 2);
        if ($serviceCount === 0) {
            return;
        }

        $services = \App\Models\Service::all();
        if ($services->isEmpty()) {
            return;
        }

        $picked = $services->random(min($serviceCount, $services->count()));

        foreach ($picked as $service) {
            $order->services()->attach($service->id, [
                'quantity'   => rand(1, 3),
                'price'      => $service->price ?? rand(50, 500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function ensureRequiredData(): void
    {
        // Products
        if (Product::count() < 20) {
            $this->writeMessage('Creating products...');
            Product::factory(20)->create();
        }

        // Services
        $this->ensureServices();

        // Currencies
        $this->ensureCurrencies();

        // Countries
        $this->ensureCountries();

    }

    private function ensureServices(): void
    {
        if (\App\Models\Service::count() > 0) {
            return;
        }

        $services = [
            ['name' => 'Fasado remontas', 'price' => 150.00],
            ['name' => 'Stogo dengimas', 'price' => 200.00],
            ['name' => 'Vidaus apdaila', 'price' => 100.00],
            ['name' => 'Elektros instaliacija', 'price' => 80.00],
            ['name' => 'Santechnikos darbai', 'price' => 90.00],
        ];

        foreach ($services as $service) {
            \App\Models\Service::create([
                'name'      => $service['name'],
                'price'     => $service['price'],
                'is_active' => true,
            ]);
        }
    }

    private function ensureClients(): \Illuminate\Support\Collection
    {
        $this->writeMessage('Ensuring client users exist...');

        $clients = collect();

        for ($i = 1; $i <= 10; $i++) {
            $email = "client{$i}@statyba.lt";

            $client = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'              => "Client {$i}",
                    'first_name'        => 'Client',
                    'last_name'         => (string) $i,
                    'password'          => '$2y$12$UsingHashForSpeedAndSecurity.............', // "password"
                    'email_verified_at' => now(),
                    'is_active'         => true,
                    'is_admin'          => false,
                ]
            );

            $clients->push($client);
        }

        return $clients;
    }

    private function generateOrdersForClients($clients): void
    {
        $this->writeMessage('Generating orders for clients...');

        $products = Product::all();
        $countries = Country::query()->get();

        // Reset sequence since we truncated
        $this->nextOrderSequence = 1;

        foreach ($clients as $client) {
            // Generate 3-5 orders per client
            $orderCount = rand(3, 5);

            for ($i = 0; $i < $orderCount; $i++) {
                $this->createOrderForClient($client, $products, $countries);
            }
        }
    }

    private function createOrderForClient(User $client, $products, $countries): void
    {
        $country = $countries->random();
        $orderDate = Carbon::now()->subDays(rand(0, 90)); // Orders from last 3 months

        try {
            /** @var Order $order */
            $order = Order::factory()
                ->for($client)
                ->state([
                    'number'           => $this->nextOrderNumber(),
                    'created_at'       => $orderDate,
                    'updated_at'       => $orderDate->copy()->addMinutes(rand(10, 1000)),
                    'status'           => fake()->randomElement(OrderStatus::cases()),
                    'payment_method'   => fake()->randomElement(PaymentMethod::cases()),
                    'currency'         => 'EUR',
                    'locale'           => 'lt',
                    'country_id'       => $country->id,
                    'billing_address'  => $this->addressForCountry($country, $client),
                    'shipping_address' => $this->addressForCountry($country, $client),
                ])
                ->create();

            // Create Order Items
            $itemCount = rand(1, 5);
            $selectedProducts = $products->random($itemCount);

            foreach ($selectedProducts as $product) {
                OrderItem::factory()
                    ->for($order)
                    ->for($product)
                    ->create();
            }

            // Create Services
            $this->createServicesForOrder($order);

            // Create Shipping
            OrderShipping::factory()
                ->for($order)
                ->state([
                    'carrier' => fake()->randomElement($this->shippingCarriers),
                    'service' => fake()->randomElement($this->shippingServices),
                ])
                ->create();

        } catch (Exception $e) {
            Log::warning('Order creation failed: ' . $e->getMessage());
        }
    }

    private function nextOrderNumber(): string
    {
        $number = sprintf('ORD-%06d', $this->nextOrderSequence);
        $this->nextOrderSequence++;

        return $number;
    }

    private function ensureCurrencies(): void
    {
        $currenciesData = [
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'exchange_rate' => 1.0],
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
        foreach ($this->countrySeeds as $seed) {
            Country::query()->updateOrCreate(
                ['cca2' => $seed['cca2']],
                $seed
            );
        }
    }

    private function addressForCountry(Country $country, User $user): array
    {
        return [
            'first_name'     => $user->first_name,
            'last_name'      => $user->last_name,
            'company'        => fake()->optional(0.3)->company(),
            'address_line_1' => fake('lt_LT')->streetAddress(),
            'address_line_2' => fake()->optional(0.2)->secondaryAddress(),
            'city'           => fake('lt_LT')->city(),
            'state'          => 'Vilniaus apskritis',
            'postal_code'    => fake('lt_LT')->postcode(),
            'country'        => $country->cca2,
            'phone'          => '+370' . fake()->numberBetween(60000000, 69999999),
            'email'          => $user->email,
        ];
    }

    private function writeMessage(string $message): void
    {
        if ($this->command instanceof \Illuminate\Console\Command) {
            $this->command->info($message);

            return;
        }
        Log::info($message);
    }
}
