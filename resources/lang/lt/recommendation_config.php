<?php

declare(strict_types=1);

return [
    // Skilties pavadinimai palaiko aiškią formos struktūrą.
    'sections' => [
        'basic_info'    => 'Pagrindinė informacija',
        'parameters'    => 'Parametrai',
        'flags'         => 'Būsenos',
        'relationships' => 'Ryšiai',
    ],
    // Laukų pavadinimai, rodomi Filament ištekliuje.
    'fields' => [
        'name'        => 'Pavadinimas',
        'type'        => 'Tipas',
        'description' => 'Aprašymas',
        'min_score'   => 'Minimalus balas',
        'max_results' => 'Didžiausias rezultatas',
        'decay_factor'=> 'Mažėjimo koeficientas',
        'priority'    => 'Prioritetas',
        'cache_ttl'   => 'Talpyklos galiojimas',
        'sort_order'  => 'Rikiavimo tvarka',
        'is_active'   => 'Aktyvi',
        'is_default'  => 'Numatytoji',
        'products'    => 'Produktai',
        'categories'  => 'Kategorijos',
        'created_at'  => 'Sukurta',
    ],
    // Veiksmų etiketės, naudojamos antraštės mygtukuose ir masiniuose veiksmuose.
    'actions' => [
        'toggle_active'       => 'Perjungti aktyvumą',
        'set_default'         => 'Nustatyti kaip numatytąją',
        'activate_selected'   => 'Aktyvuoti pasirinktus',
        'deactivate_selected' => 'Deaktyvuoti pasirinktus',
    ],
];
