<?php

return [
    // Navigation
    'navigation' => [
        'orders' => 'Užsakymai',
    ],

    // Models
    'models' => [
        'order' => 'Užsakymas',
        'orders' => 'Užsakymai',
    ],

    // Fields
    'fields' => [
        'order_number' => 'Užsakymo numeris',
        'customer' => 'Klientas',
        'customer_name' => 'Kliento vardas',
        'status' => 'Būsena',
        'payment_status' => 'Mokėjimo būsena',
        'payment_method' => 'Mokėjimo būdas',
        'payment_reference' => 'Mokėjimo nuoroda',
        'subtotal' => 'Tarpinė suma',
        'tax_amount' => 'Mokesčių suma',
        'shipping_amount' => 'Pristatymo suma',
        'discount_amount' => 'Nuolaidos suma',
        'total' => 'Bendra suma',
        'items_count' => 'Prekių skaičius',
        'billing_address' => 'Atsiskaitymo adresas',
        'shipping_address' => 'Pristatymo adresas',
        'tracking_number' => 'Sekimo numeris',
        'shipped_at' => 'Išsiųsta',
        'delivered_at' => 'Pristatyta',
        'notes' => 'Pastabos',
        'internal_notes' => 'Vidinės pastabos',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
    ],

    // Sections
    'sections' => [
        'order_details' => 'Užsakymo detalės',
        'customer_information' => 'Kliento informacija',
        'billing_information' => 'Atsiskaitymo informacija',
        'shipping_information' => 'Pristatymo informacija',
        'order_shipping' => 'Užsakymo pristatymas',
    ],

    // Statuses
    'status' => [
        'pending' => 'Laukiantis',
        'processing' => 'Apdorojamas',
        'shipped' => 'Išsiųstas',
        'delivered' => 'Pristatytas',
        'cancelled' => 'Atšauktas',
        'refunded' => 'Grąžintas',
    ],

    'statuses' => [
        'pending' => 'Laukiantis',
        'processing' => 'Apdorojamas',
        'shipped' => 'Išsiųstas',
        'delivered' => 'Pristatytas',
        'cancelled' => 'Atšauktas',
        'refunded' => 'Grąžintas',
    ],

    // Payment statuses
    'payment_status' => [
        'pending' => 'Laukiantis',
        'paid' => 'Apmokėtas',
        'failed' => 'Nepavyko',
        'refunded' => 'Grąžintas',
    ],

    'payment_statuses' => [
        'pending' => 'Laukiantis',
        'paid' => 'Apmokėtas',
        'failed' => 'Nepavyko',
        'refunded' => 'Grąžintas',
    ],

    // Payment methods
    'payment_methods' => [
        'credit_card' => 'Kredito kortelė',
        'bank_transfer' => 'Banko pavedimas',
        'cash_on_delivery' => 'Atsiskaitymas grynaisiais',
        'paypal' => 'PayPal',
        'stripe' => 'Stripe',
        'apple_pay' => 'Apple Pay',
        'google_pay' => 'Google Pay',
    ],

    // Actions
    'actions' => [
        'create' => 'Sukurti',
        'view' => 'Peržiūrėti',
        'edit' => 'Redaguoti',
        'delete' => 'Ištrinti',
    ],

    'mark_processing' => 'Pažymėti kaip apdorojamą',
    'mark_shipped' => 'Pažymėti kaip išsiųstą',
    'mark_delivered' => 'Pažymėti kaip pristatytą',
    'cancel_order' => 'Atšaukti užsakymą',
    'refund_order' => 'Grąžinti užsakymą',

    // Bulk actions
    'bulk_mark_processing' => 'Pažymėti kaip apdorojamus',
    'bulk_mark_shipped' => 'Pažymėti kaip išsiųstus',
    'bulk_mark_delivered' => 'Pažymėti kaip pristatytus',
    'bulk_cancel' => 'Atšaukti užsakymus',
    'export' => 'Eksportuoti',

    // Notifications
    'processing_success' => 'Užsakymas sėkmingai pažymėtas kaip apdorojamas',
    'shipped_successfully' => 'Užsakymas sėkmingai pažymėtas kaip išsiųstas',
    'delivered_successfully' => 'Užsakymas sėkmingai pažymėtas kaip pristatytas',
    'cancelled_successfully' => 'Užsakymas sėkmingai atšauktas',
    'refunded_successfully' => 'Užsakymas sėkmingai grąžintas',
    'bulk_processing_success' => 'Užsakymai sėkmingai pažymėti kaip apdorojami',
    'bulk_shipped_success' => 'Užsakymai sėkmingai pažymėti kaip išsiųsti',
    'bulk_delivered_success' => 'Užsakymai sėkmingai pažymėti kaip pristatyti',
    'bulk_cancelled_success' => 'Užsakymai sėkmingai atšaukti',
    'export_success' => 'Užsakymai sėkmingai eksportuoti',

    // Filters
    'is_paid' => 'Apmokėta',
    'total_from' => 'Suma nuo',
    'total_until' => 'Suma iki',

    // Help text
    'number_help' => 'Unikalus užsakymo numeris',
];
