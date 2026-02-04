<?php

declare(strict_types=1);

it('unit: has message label translations for core locales', function (): void {
    $translationsByLocale = [
        'en' => [
            'name'           => 'Name',
            'email'          => 'Email',
            'phone'          => 'Phone',
            'active'         => 'Active',
            'order_number'   => 'Order number',
            'customer'       => 'Customer',
            'status'         => 'Status',
            'total'          => 'Total',
            'payment_status' => 'Payment Status',
            'created_at'     => 'Created At',
        ],
        'lt' => [
            'name'           => 'Pavadinimas',
            'email'          => 'El. paštas',
            'phone'          => 'Telefonas',
            'active'         => 'Aktyvus',
            'order_number'   => 'Užsakymo numeris',
            'customer'       => 'Klientas',
            'status'         => 'Būsena',
            'total'          => 'Iš viso',
            'payment_status' => 'Mokėjimo būsena',
            'created_at'     => 'Sukurta',
        ],
        'de' => [
            'name'           => 'Name',
            'email'          => 'E-Mail',
            'phone'          => 'Telefon',
            'active'         => 'Aktiv',
            'order_number'   => 'Bestellnummer',
            'customer'       => 'Kunde',
            'status'         => 'Status',
            'total'          => 'Gesamt',
            'payment_status' => 'Zahlungsstatus',
            'created_at'     => 'Erstellt am',
        ],
        'ru' => [
            'name'           => 'Название',
            'email'          => 'Email',
            'phone'          => 'Телефон',
            'active'         => 'Активно',
            'order_number'   => 'Номер заказа',
            'customer'       => 'Клиент',
            'status'         => 'Статус',
            'total'          => 'Итого',
            'payment_status' => 'Статус платежа',
            'created_at'     => 'Создано',
        ],
    ];

    foreach ($translationsByLocale as $locale => $expected) {
        $path = lang_path("{$locale}/messages.php");

        expect(file_exists($path))->toBeTrue();

        $messages = require $path;

        expect($messages)->toBeArray();

        foreach ($expected as $key => $value) {
            expect($messages)->toHaveKey($key);
            expect($messages[$key])->toBe($value);
        }
    }
});
