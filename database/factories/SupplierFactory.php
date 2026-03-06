<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        $base = Str::slug($name);
        $companyCode = $this->faker->unique()->numerify('#########');

        if ($base === '') {
            $base = 'supplier';
        }

        return [
            'name'           => $name,
            'company_code'   => $companyCode,
            'code'           => $base . '-' . $this->faker->unique()->numerify('##'),
            'vat_code'       => 'LT' . $companyCode,
            'contact_person' => $this->faker->name(),
            'contact_email'  => $this->faker->unique()->safeEmail(),
            'contact_phone'  => $this->faker->phoneNumber(),
            'website'        => $this->faker->url(),
            'address'        => $this->faker->streetAddress(),
            'city'           => $this->faker->city(),
            'postal_code'    => $this->faker->postcode(),
            'country'        => $this->faker->country(),
            'notes'          => $this->faker->optional()->sentence(),
            'is_enabled'     => true,
        ];
    }
}
