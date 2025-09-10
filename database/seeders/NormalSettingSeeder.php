<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NormalSetting;
use Illuminate\Database\Seeder;

final class NormalSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'group' => 'general',
                'key' => 'site_name',
                'type' => 'text',
                'value' => 'Statyba E‑Commerce',
                'description' => 'Public site display name',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => ['required','string','max:255'],
                'sort_order' => 0,
            ],
            [
                'group' => 'general',
                'key' => 'default_locale',
                'type' => 'text',
                'value' => 'lt',
                'description' => 'Default locale code',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => ['required','string','max:10'],
                'sort_order' => 1,
            ],
            [
                'group' => 'ecommerce',
                'key' => 'default_currency',
                'type' => 'text',
                'value' => 'EUR',
                'description' => 'Default currency code',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => ['required','string','size:3'],
                'sort_order' => 0,
            ],
            [
                'group' => 'shipping',
                'key' => 'free_shipping_threshold',
                'type' => 'number',
                'value' => 100,
                'description' => 'Order total to qualify for free shipping',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => ['numeric','min:0'],
                'sort_order' => 0,
            ],
            [
                'group' => 'security',
                'key' => 'maintenance_mode',
                'type' => 'boolean',
                'value' => false,
                'description' => 'Enable maintenance mode',
                'is_public' => false,
                'is_encrypted' => false,
                'validation_rules' => ['boolean'],
                'sort_order' => 0,
            ],
        ];

        foreach ($settings as $data) {
            /** @var array{group:string,key:string} $data */
            NormalSetting::query()->updateOrCreate(
                ['group' => $data['group'], 'key' => $data['key']],
                $data,
            );
        }
    }
}

