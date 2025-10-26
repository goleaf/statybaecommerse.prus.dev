<?php

declare(strict_types=1);

return [
    'title'      => 'Rolės',
    'plural'     => 'Rolės',
    'single'     => 'Rolė',
    'navigation' => 'Rolės',
    'fields'     => [
        'name'               => 'Pavadinimas',
        'guard_name'         => 'Sargyba',
        'permissions_matrix' => 'Leidimai',
        'permissions_count'  => 'Leidimų skaičius',
    ],
    'sections' => [
        'general'     => 'Rolės informacija',
        'permissions' => 'Leidimų matrica',
    ],
    'modules' => [
        'panel'      => 'Administratoriaus skydelis',
        'products'   => 'Produktai',
        'categories' => 'Kategorijos',
        'brands'     => 'Prekių ženklai',
        'orders'     => 'Užsakymai',
        'users'      => 'Vartotojai',
        'roles'      => 'Rolės',
    ],
    'abilities' => [
        'access'  => 'Prieiga',
        'viewAny' => 'Peržiūrėti visus',
        'view'    => 'Peržiūrėti',
        'create'  => 'Kurti',
        'update'  => 'Atnaujinti',
        'delete'  => 'Ištrinti',
    ],
];
