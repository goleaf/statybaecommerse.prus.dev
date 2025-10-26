<?php

declare(strict_types=1);

return [
    'title'  => 'Administratoriai',
    'plural' => 'Administratoriai',
    'single' => 'Administratorius',
    'form'   => [
        'tabs' => [
            'basic_information' => 'Pagrindinė informacija',
            'account_details'   => 'Paskyros detalės',
        ],
        'sections' => [
            'basic_information' => 'Pagrindinė informacija',
            'account_details'   => 'Paskyros detalės',
            'roles_permissions' => 'Vaidmenys ir leidimai',
        ],
        'fields' => [
            'name'                  => 'Vardas',
            'email'                 => 'El. paštas',
            'password'              => 'Slaptažodis',
            'password_confirmation' => 'Patvirtinti slaptažodį',
            'email_verified_at'     => 'El. paštas patvirtintas',
            'created_at'            => 'Sukurta',
            'updated_at'            => 'Atnaujinta',
            'roles'                 => 'Vaidmenys',
            'audit_reason'          => 'Pakeitimo priežastis',
        ],
        'helpers' => [
            'roles'        => 'Pasirinkite administratoriaus vaidmenis šiam naudotojui.',
            'audit_reason' => 'Paaiškinkite, kodėl keičiami vaidmenys.',
        ],
    ],
    'filters' => [
        'email_verified' => 'El. paštas patvirtintas',
        'verified'       => 'Patvirtintas',
        'unverified'     => 'Nepatvirtintas',
        'created_at'     => 'Sukurta',
        'created_from'   => 'Sukurta nuo',
        'created_until'  => 'Sukurta iki',
        'recent'         => 'Nauji (30 dienų)',
    ],
    'actions' => [
        'verify_email'       => 'Patvirtinti el. paštą',
        'send_verification'  => 'Siųsti patvirtinimą',
        'verify_emails'      => 'Patvirtinti el. paštus',
        'send_verifications' => 'Siųsti patvirtinimus',
    ],
    'notifications' => [
        'email_verified_successfully'     => 'El. paštas sėkmingai patvirtintas',
        'verification_sent_successfully'  => 'Patvirtinimas sėkmingai išsiųstas',
        'emails_verified_successfully'    => 'El. paštai sėkmingai patvirtinti',
        'verifications_sent_successfully' => 'Patvirtinimai sėkmingai išsiųsti',
    ],
];
