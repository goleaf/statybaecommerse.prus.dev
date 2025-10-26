<?php

declare(strict_types=1);

return [
    'navigation_label'   => 'Produkto istorija',
    'plural_model_label' => 'Produkto istorijos',
    'model_label'        => 'Produkto istorija',
    'actions'            => [
        'created'          => 'Sukurta',
        'updated'          => 'Atnaujinta',
        'deleted'          => 'Ištrinta',
        'restored'         => 'Atstatyta',
        'price_changed'    => 'Kaina pakeista',
        'stock_updated'    => 'Atsargos atnaujintos',
        'stock_changed'    => 'Atsargos atnaujintos',
        'status_changed'   => 'Statusas pakeistas',
        'category_changed' => 'Kategorija pakeista',
        'image_changed'    => 'Paveikslėlis pakeistas',
        'custom'           => 'Pasirinktinis veiksmas',
    ],
    'fields' => [
        'action'         => 'Veiksmas',
        'field_name'     => 'Lauko pavadinimas',
        'old_value'      => 'Sena reikšmė',
        'new_value'      => 'Nauja reikšmė',
        'price'          => 'Kaina',
        'sale_price'     => 'Nuolaidos kaina',
        'stock_quantity' => 'Atsargų kiekis',
        'status'         => 'Statusas',
        'is_visible'     => 'Matomumas',
        'description'    => 'Aprašymas',
        'name'           => 'Pavadinimas',
        'category'       => 'Kategorija',
        'image'          => 'Paveikslėlis',
        'metadata'       => 'Metaduomenys',
    ],
    'summaries' => [
        'created' => 'Sukurtas :field',
        'deleted' => ':field ištrintas',
        'updated' => ':field pakeistas iš :from į :to',
    ],
    'impact' => [
        'high'   => 'Didelės įtakos pakeitimas',
        'medium' => 'Vidutinės įtakos pakeitimas',
        'low'    => 'Mažos įtakos pakeitimas',
    ],
];
