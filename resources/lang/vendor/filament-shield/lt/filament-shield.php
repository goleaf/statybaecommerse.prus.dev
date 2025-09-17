<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */

    'column.name' => 'Pavadinimas',
    'column.guard_name' => 'Apsaugos pavadinimas',
    'column.team' => 'Komanda',
    'column.roles' => 'Vaidmenys',
    'column.permissions' => 'Leidimai',
    'column.updated_at' => 'Atnaujinta',

    /*
    |--------------------------------------------------------------------------
    | Form Fields
    |--------------------------------------------------------------------------
    */

    'field.name' => 'Pavadinimas',
    'field.guard_name' => 'Apsaugos pavadinimas',
    'field.permissions' => 'Leidimai',
    'field.team' => 'Komanda',
    'field.team.placeholder' => 'Pasirinkite komandą...',
    'field.select_all.name' => 'Pažymėti visus',
    'field.select_all.message' => 'Įjungia/išjungia visus leidimus šiam vaidmeniui',

    /*
    |--------------------------------------------------------------------------
    | Navigation & Resource
    |--------------------------------------------------------------------------
    */

    'nav.group' => 'Turinio valdymas',
    'nav.role.label' => 'Vaidmenys',
    'nav.role.icon' => 'heroicon-o-user-group',
    'resource.label.role' => 'Vaidmuo',
    'resource.label.roles' => 'Vaidmenys',

    /*
    |--------------------------------------------------------------------------
    | Section & Tabs
    |--------------------------------------------------------------------------
    */

    'section' => 'Objektai',
    'resources' => 'Resursai',
    'widgets' => 'Valdikliai',
    'pages' => 'Puslapiai',
    'custom' => 'Pritaikyti leidimai',

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    'forbidden' => 'Neturite leidimo prieiti',

    /*
    |--------------------------------------------------------------------------
    | Resource Permissions' Labels
    |--------------------------------------------------------------------------
    */

    'resource_permission_prefixes_labels' => [
        'view' => 'Peržiūrėti',
        'view_any' => 'Peržiūrėti bet kurį',
        'create' => 'Sukurti',
        'update' => 'Atnaujinti',
        'delete' => 'Ištrinti',
        'delete_any' => 'Ištrinti bet kurį',
        'force_delete' => 'Priversti ištrinti',
        'force_delete_any' => 'Priversti ištrinti bet kurį',
        'restore' => 'Atkurti',
        'reorder' => 'Perrikiuoti',
        'restore_any' => 'Atkurti bet kurį',
        'replicate' => 'Dubliuoti',
    ],
];

