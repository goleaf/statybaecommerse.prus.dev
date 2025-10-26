<?php

declare(strict_types=1);

return [
    'title'  => 'Vartotojai',
    'plural' => 'Vartotojai',
    'single' => 'Vartotojas',
    'fields' => [
        'name'               => 'Vardas',
        'first_name'         => 'Vardas',
        'last_name'          => 'Pavardė',
        'email'              => 'El. paštas',
        'phone_number'       => 'Telefono numeris',
        'avatar'             => 'Nuotrauka',
        'gender'             => 'Lytis',
        'birth_date'         => 'Gimimo data',
        'company'            => 'Įmonė',
        'job_title'          => 'Pareigos',
        'bio'                => 'Apie mane',
        'website'            => 'Svetainė',
        'preferred_locale'   => 'Kalbos nustatymas',
        'timezone'           => 'Laiko juosta',
        'roles'              => 'Rolės',
        'is_active'          => 'Aktyvus',
        'is_verified'        => 'Patvirtintas',
        'accepts_marketing'  => 'Sutinka su rinkodara',
        'email_verified'     => 'El. paštas patvirtintas',
        'locale'             => 'Kalba',
        'created_at'         => 'Sukurta',
        'permissions_matrix' => 'Teisių matrica',
    ],
    'sections' => [
        'basic_info'  => 'Pagrindinė informacija',
        'profile'     => 'Profilis',
        'permissions' => 'Teisės ir prieigos',
    ],
    'gender' => [
        'male'   => 'Vyras',
        'female' => 'Moteris',
        'other'  => 'Kita',
    ],
    'filters' => [
        'locale'        => 'Kalba',
        'created_from'  => 'Sukurta nuo',
        'created_until' => 'Sukurta iki',
    ],
    'actions' => [
        'activate'   => 'Aktyvuoti',
        'deactivate' => 'Deaktyvuoti',
    ],
    'notifications' => [
        'activated'   => 'Vartotojai aktyvuoti',
        'deactivated' => 'Vartotojai deaktyvuoti',
    ],
    'permissions' => [
        'helper_text' => 'Pasirinkite, kokius veiksmus šis vartotojas gali atlikti. Palikite eilutę tuščią, jei norite paveldėti numatytas rolių teises.',
        'rows'        => [
            'products'   => 'Produktai',
            'categories' => 'Kategorijos',
            'brands'     => 'Prekių ženklai',
            'orders'     => 'Užsakymai',
            'users'      => 'Vartotojai',
        ],
        'columns' => [
            'view_any' => 'Sąrašas',
            'view'     => 'Peržiūrėti',
            'create'   => 'Kurti',
            'update'   => 'Atnaujinti',
            'delete'   => 'Ištrinti',
        ],
    ],
];
