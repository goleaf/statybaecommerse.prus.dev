<?php

declare(strict_types=1);

return [
    'title'  => 'Prekių užklausos',
    'plural' => 'Prekių užklausos',
    'single' => 'Prekių užklausa',
    'fields' => [
        'product'            => 'Prekė',
        'user'               => 'Vartotojas',
        'name'               => 'Vardas',
        'email'              => 'El. paštas',
        'phone'              => 'Telefonas',
        'message'            => 'Žinutė',
        'requested_quantity' => 'Pageidaujamas kiekis',
        'status'             => 'Būsena',
        'admin_notes'        => 'Administratoriaus pastabos',
        'responded_at'       => 'Atsakyta',
        'responded_by'       => 'Atsakė',
        'created_at'         => 'Sukurta',
    ],
    'status' => [
        'pending'     => 'Laukiama',
        'in_progress' => 'Vykdoma',
        'completed'   => 'Užbaigta',
        'cancelled'   => 'Atšaukta',
    ],
    'filters' => [
        'status'  => 'Būsena',
        'product' => 'Prekė',
        'user'    => 'Vartotojas',
    ],
];
