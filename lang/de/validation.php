<?php

declare(strict_types=1);

$base = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/validation.php');

return array_replace_recursive($base, [
    'accepted' => 'Das :attribute-Attribut muss akzeptiert werden.',
    'exists'   => 'Der ausgewählte :attribute ist ungültig.',
    'required' => 'Das Feld :attribute ist erforderlich.',
    'max'      => [
        'string' => ':attribute darf nicht länger als :max Zeichen sein.',
    ],
    'attributes' => [
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
