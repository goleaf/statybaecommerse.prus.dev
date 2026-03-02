<?php

declare(strict_types=1);

$base = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');

return array_replace_recursive($base, [
    'accepted' => 'Das :attribute-Attribut muss akzeptiert werden.',
    'email'    => 'Das Feld :attribute muss eine gültige E-Mail-Adresse sein.',
    'exists'   => 'Der ausgewählte :attribute ist ungültig.',
    'lowercase' => 'Das Feld :attribute darf nur Kleinbuchstaben enthalten.',
    'required' => 'Das Feld :attribute ist erforderlich.',
    'confirmed' => 'Die Bestätigung von :attribute stimmt nicht überein.',
    'regex'     => 'Das Format von :attribute ist ungültig.',
    'max'      => [
        'string' => ':attribute darf nicht länger als :max Zeichen sein.',
    ],
    'min'      => [
        'string' => ':attribute muss mindestens :min Zeichen enthalten.',
    ],
    'unique'   => ':attribute ist bereits vergeben.',
    'attributes' => [
        'first_name'            => 'Vorname',
        'last_name'             => 'Nachname',
        'email'                 => 'E-Mail-Adresse',
        'password'              => 'Passwort',
        'password_confirmation' => 'Passwortbestätigung',
        'loginForm' => [
            'email'           => 'E-Mail-Adresse',
            'password'        => 'Passwort',
            'captchaToken'    => 'Sicherheitstoken',
            'captchaResponse' => 'Sicherheitsantwort',
        ],
        'registrationForm' => [
            'first_name'            => 'Vorname',
            'last_name'             => 'Nachname',
            'email'                 => 'E-Mail-Adresse',
            'password'              => 'Passwort',
            'password_confirmation' => 'Passwortbestätigung',
        ],
        'selectedShippingOption' => 'Versandart',
        'selectedPaymentMethod'  => 'Zahlungsmethode',
        'billing'                => [
            'first_name'  => 'Rechnungs-Vorname',
            'last_name'   => 'Rechnungs-Nachname',
            'email'       => 'Rechnungs-E-Mail-Adresse',
            'phone'       => 'Rechnungs-Telefonnummer',
            'address'     => 'Rechnungsadresse',
            'city'        => 'Rechnungsstadt',
            'postal_code' => 'Rechnungs-Postleitzahl',
            'country'     => 'Rechnungsland',
        ],
        'shipping' => [
            'first_name'  => 'Liefer-Vorname',
            'last_name'   => 'Liefer-Nachname',
            'address'     => 'Lieferadresse',
            'city'        => 'Lieferstadt',
            'postal_code' => 'Liefer-Postleitzahl',
            'country'     => 'Lieferland',
        ],
    ],
]);
