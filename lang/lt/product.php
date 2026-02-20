<?php

declare(strict_types=1);

return [
    'variants' => [
        'fields' => [
            'apply_to_sale_items' => 'Taikyti akciniams variantams',
            'change_reason'       => 'Pakeitimo priežastis',
            'price_type'          => 'Kainos tipas',
            'sale_end_date'       => 'Akcijos pabaigos data',
            'sale_start_date'     => 'Akcijos pradžios data',
            'set_sale_period'     => 'Nustatyti akcijos laikotarpį',
            'update_type'         => 'Atnaujinimo tipas',
            'update_value'        => 'Atnaujinimo reikšmė',
        ],
        'help' => [
            'update_value' => 'Reikšmė, naudojama naujoms pasirinktų variantų kainoms apskaičiuoti.',
        ],
        'placeholders' => [
            'change_reason' => 'Pasirenkama šio masinio atnaujinimo priežastis...',
        ],
        'price_types' => [
            'regular'     => 'Įprasta kaina',
            'wholesale'   => 'Didmeninė kaina',
            'member'      => 'Nario kaina',
            'promotional' => 'Akcijos kaina',
        ],
        'update_types' => [
            'fixed_amount' => 'Pridėti fiksuotą sumą',
            'percentage'   => 'Keisti procentais',
            'multiply_by'  => 'Dauginti iš reikšmės',
            'set_to'       => 'Nustatyti tikslią reikšmę',
        ],
        'defaults' => [
            'bulk_price_update_reason' => 'Masinis kainos atnaujinimas',
        ],
        'notifications' => [
            'bulk_update_success'      => 'Masinis kainų atnaujinimas baigtas',
            'bulk_update_success_body' => 'Atnaujinta variantų: :updated. Praleista variantų: :skipped.',
        ],
        'stats' => [
            'all_variants'          => 'Visi variantai',
            'all_variants_stock'    => 'Bendras visų variantų likutis',
            'available_stock'       => 'Prieinamas likutis',
            'average_price'         => 'Vidutinė kaina',
            'between_50_100_euros'  => 'Tarp 50 € ir 100 €',
            'discounted_variants'   => 'Nukainoti variantai',
            'from_sales'            => 'Iš pardavimų',
            'highest_price'         => 'Didžiausia kaina',
            'low_stock_alerts'      => 'Mažo likučio įspėjimai',
            'lowest_price'          => 'Mažiausia kaina',
            'most_affordable'       => 'Prieinamiausia',
            'most_expensive'        => 'Brangiausia',
            'need_restocking'       => 'Reikia papildyti',
            'on_sale'               => 'Su nuolaida',
            'out_of_stock'          => 'Nėra likučio',
            'pending_orders'        => 'Laukiantys užsakymai',
            'price_range_50_100'    => 'Kainų rėžis 50 €-100 €',
            'price_range_under_50'  => 'Kainų rėžis iki 50 €',
            'ready_for_sale'        => 'Paruošta pardavimui',
            'reserved_stock'        => 'Rezervuotas likutis',
            'sold_stock'            => 'Parduotas likutis',
            'stock_value'           => 'Likučio vertė',
            'total_inventory_value' => 'Bendra atsargų vertė',
            'total_revenue'         => 'Bendros pajamos',
            'total_sold'            => 'Iš viso parduota',
            'total_stock'           => 'Iš viso likutis',
            'unavailable_variants'  => 'Neprieinami variantai',
            'under_50_euros'        => 'Iki 50 €',
        ],
        'messages' => [
            'select_variant' => 'Pasirinkite variantą',
        ],
    ],
];
