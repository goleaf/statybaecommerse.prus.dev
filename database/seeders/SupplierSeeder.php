<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Supplier;

final class SupplierSeeder extends BaseSeeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name'           => 'Vilniaus Statybos Tiekimas',
                'company_code'   => '305998761',
                'code'           => 'vilniaus-statybos-tiekimas',
                'vat_code'       => 'LT305998761',
                'contact_person' => 'Mantas Petrauskas',
                'contact_email'  => 'tiekimas@vstiekimas.lt',
                'contact_phone'  => '+37061234567',
                'website'        => 'https://vstiekimas.lt',
                'address'        => 'Ukmerges g. 120',
                'city'           => 'Vilnius',
                'postal_code'    => 'LT-08100',
                'country'        => 'Lietuva',
                'notes'          => 'Pagrindinis sausuju misiniu tiekejas.',
                'is_enabled'     => true,
            ],
            [
                'name'           => 'Kauno Inzineriniai Resursai',
                'company_code'   => '304123456',
                'code'           => 'kauno-inzineriniai-resursai',
                'vat_code'       => 'LT304123456',
                'contact_person' => 'Gintare Jankauskiene',
                'contact_email'  => 'pardavimai@kir.lt',
                'contact_phone'  => '+37064567890',
                'website'        => 'https://kir.lt',
                'address'        => 'Pramones pr. 42',
                'city'           => 'Kaunas',
                'postal_code'    => 'LT-51280',
                'country'        => 'Lietuva',
                'notes'          => 'Dirba su regioniniais B2B uzsakymais.',
                'is_enabled'     => true,
            ],
            [
                'name'           => 'Baltic Timber Group',
                'company_code'   => '302765432',
                'code'           => 'baltic-timber-group',
                'vat_code'       => 'LT302765432',
                'contact_person' => 'Rokas Vaitkus',
                'contact_email'  => 'info@baltictimbergroup.lt',
                'contact_phone'  => '+37069911223',
                'website'        => 'https://baltictimbergroup.lt',
                'address'        => 'Silutes pl. 18',
                'city'           => 'Klaipeda',
                'postal_code'    => 'LT-91111',
                'country'        => 'Lietuva',
                'notes'          => 'Medienos ir konstrukciju tiekimas.',
                'is_enabled'     => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::query()->updateOrCreate(
                ['company_code' => $supplier['company_code']],
                $supplier,
            );
        }

        $this->command?->info('Suppliers seeded successfully.');
    }
}
