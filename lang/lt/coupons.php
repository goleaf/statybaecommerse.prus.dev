<?php

declare(strict_types=1);

return array_replace_recursive(
    require __DIR__ . '/discount_codes.php',
    [
        'title'  => 'Kuponai',
        'plural' => 'Kuponai',
        'single' => 'Kuponas',
        'badges' => [
            'type'           => 'Tipas: :type',
            'customer_group' => 'Grupė: :group',
            'public_scope'   => 'Visiems klientams',
            'active'         => 'Aktyvus',
            'inactive'       => 'Neaktyvus',
            'used_of_limit'  => 'Panaudota: :count / :limit',
            'used'           => 'Panaudota: :count',
            'remaining'      => 'Liko: :count',
            'public'         => 'Viešas',
            'private'        => 'Privatus',
            'auto_apply'     => 'Automatinis taikymas',
            'manual_apply'   => 'Rankinis taikymas',
            'stackable'      => 'Galima derinti',
            'single_use'     => 'Nederinamas',
        ],
    ]
);
