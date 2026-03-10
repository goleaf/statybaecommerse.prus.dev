<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\File;
use App\Models\Order;
use App\Models\OrderInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderInvoice>
 */
final class OrderInvoiceFactory extends Factory
{
    protected $model = OrderInvoice::class;

    public function definition(): array
    {
        $fullNumber = 'INV-' . $this->faker->numerify('######');

        return [
            'order_id'            => Order::factory(),
            'file_id'             => null,
            'external_invoice_id' => (string) $this->faker->numberBetween(1000, 999999),
            'invoice_series'      => 'INV',
            'invoice_number'      => (string) $this->faker->numberBetween(1, 99999),
            'full_number'         => $fullNumber,
            'invoice_type'        => 'sf',
            'status'              => OrderInvoice::STATUS_READY,
            'is_current'          => true,
            'generation_mode'     => OrderInvoice::MODE_AUTO,
            'provider_payload'    => ['factory' => true],
            'error_message'       => null,
            'generated_at'        => now(),
            'failed_at'           => null,
        ];
    }

    public function failed(): static
    {
        return $this->state([
            'status'        => OrderInvoice::STATUS_FAILED,
            'error_message' => 'Factory failure',
            'failed_at'     => now(),
            'generated_at'  => null,
            'file_id'       => null,
        ]);
    }

    public function withFile(): static
    {
        return $this->state([
            'file_id' => File::factory()->document(),
        ]);
    }
}
