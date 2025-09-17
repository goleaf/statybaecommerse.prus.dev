<?php

declare(strict_types=1);

return [
    // Navigation
    'navigation' => [
        'orders' => 'Užsakymai',
        'order_management' => 'Užsakymų valdymas',
    ],
    
    // Models
    'models' => [
        'order' => 'Užsakymas',
        'orders' => 'Užsakymai',
        'order_item' => 'Užsakymo prekė',
        'order_items' => 'Užsakymo prekės',
    ],
    
    // Fields
    'fields' => [
        'order_number' => 'Užsakymo numeris',
        'customer' => 'Klientas',
        'customer_name' => 'Kliento vardas',
        'customer_email' => 'Kliento el. paštas',
        'customer_phone' => 'Kliento telefonas',
        'status' => 'Būsena',
        'payment_status' => 'Mokėjimo būsena',
        'shipping_status' => 'Pristatymo būsena',
        'total' => 'Bendra suma',
        'subtotal' => 'Tarpinė suma',
        'tax_amount' => 'PVM suma',
        'shipping_amount' => 'Pristatymo kaina',
        'discount_amount' => 'Nuolaidos suma',
        'currency' => 'Valiuta',
        'payment_method' => 'Mokėjimo būdas',
        'billing_address' => 'Sąskaitos adresas',
        'shipping_address' => 'Pristatymo adresas',
        'notes' => 'Pastabos',
        'internal_notes' => 'Vidinės pastabos',
        'created_at' => 'Sukurta',
        'updated_at' => 'Atnaujinta',
        'shipped_at' => 'Išsiųsta',
        'delivered_at' => 'Pristatyta',
        'cancelled_at' => 'Atšaukta',
        'tracking_number' => 'Sekimo numeris',
        'tracking_url' => 'Sekimo nuoroda',
        'carrier' => 'Vežėjas',
        'service' => 'Paslaugos tipas',
        'estimated_delivery' => 'Numatomas pristatymas',
        'weight' => 'Svoris',
        'dimensions' => 'Matmenys',
        'items_count' => 'Prekių skaičius',
        'total_items' => 'Iš viso prekių',
    ],
    
    // Status
    'status' => [
        'pending' => 'Laukiantis',
        'confirmed' => 'Patvirtintas',
        'processing' => 'Apdorojamas',
        'shipped' => 'Išsiųstas',
        'delivered' => 'Pristatytas',
        'cancelled' => 'Atšauktas',
        'refunded' => 'Grąžintas',
        'completed' => 'Užbaigtas',
    ],
    
    // Payment Status
    'payment_status' => [
        'pending' => 'Laukiama apmokėjimo',
        'paid' => 'Apmokėtas',
        'failed' => 'Apmokėjimas nepavyko',
        'refunded' => 'Grąžintas',
        'partially_refunded' => 'Dalinai grąžintas',
        'cancelled' => 'Atšauktas',
    ],
    
    // Payment Methods
    'payment_methods' => [
        'credit_card' => 'Kredito kortelė',
        'paypal' => 'PayPal',
        'bank_transfer' => 'Banko pavedimas',
        'cash_on_delivery' => 'Atsiskaitymas pristatymo metu',
        'stripe' => 'Stripe',
        'mollie' => 'Mollie',
    ],
    
    // Shipping Carriers
    'shipping_carriers' => [
        'dpd' => 'DPD',
        'omniva' => 'Omniva',
        'lp_express' => 'LP Express',
        'ups' => 'UPS',
        'fedex' => 'FedEx',
        'dhl' => 'DHL',
    ],
    
    // Shipping Services
    'shipping_services' => [
        'standard' => 'Standartinis',
        'express' => 'Greitasis',
        'next_day' => 'Kitos dienos',
        'economy' => 'Ekonominis',
        'premium' => 'Premijinis',
    ],
    
    // Actions
    'actions' => [
        'create' => 'Sukurti užsakymą',
        'edit' => 'Redaguoti užsakymą',
        'view' => 'Peržiūrėti užsakymą',
        'delete' => 'Ištrinti užsakymą',
        'duplicate' => 'Dubliuoti užsakymą',
        'print_invoice' => 'Spausdinti sąskaitą faktūrą',
        'print_receipt' => 'Spausdinti kvitą',
        'generate_documents' => 'Generuoti dokumentus',
        'send_notification' => 'Siųsti pranešimą',
        'track_shipment' => 'Sekti siuntą',
        'refund_order' => 'Grąžinti užsakymą',
        'cancel_order' => 'Atšaukti užsakymą',
        'mark_as_paid' => 'Pažymėti kaip apmokėtą',
        'mark_as_shipped' => 'Pažymėti kaip išsiųstą',
        'mark_as_delivered' => 'Pažymėti kaip pristatytą',
        'export_orders' => 'Eksportuoti užsakymus',
    ],
    
    // Filters
    'filters' => [
        'status' => 'Būsena',
        'payment_status' => 'Mokėjimo būsena',
        'date_range' => 'Datos intervalas',
        'customer' => 'Klientas',
        'payment_method' => 'Mokėjimo būdas',
        'shipping_carrier' => 'Vežėjas',
        'total_range' => 'Sumos intervalas',
    ],
    
    // Notifications
    'notifications' => [
        'order_created' => 'Užsakymas sukurtas',
        'order_updated' => 'Užsakymas atnaujintas',
        'order_cancelled' => 'Užsakymas atšauktas',
        'order_shipped' => 'Užsakymas išsiųstas',
        'order_delivered' => 'Užsakymas pristatytas',
        'payment_received' => 'Mokėjimas gautas',
        'payment_failed' => 'Mokėjimas nepavyko',
        'refund_processed' => 'Grąžinimas apdorotas',
    ],
    
    // Widgets
    'widgets' => [
        'total_orders' => 'Iš viso užsakymų',
        'pending_orders' => 'Laukiantys užsakymai',
        'completed_orders' => 'Užbaigti užsakymai',
        'cancelled_orders' => 'Atšaukti užsakymai',
        'total_revenue' => 'Bendros pajamos',
        'average_order_value' => 'Vidutinė užsakymo vertė',
        'recent_orders' => 'Paskutiniai užsakymai',
        'orders_today' => 'Šiandienos užsakymai',
        'orders_this_month' => 'Šio mėnesio užsakymai',
    ],
    
    // Sections
    'sections' => [
        'order_details' => 'Užsakymo detalės',
        'order_items' => 'Užsakymo prekės',
        'order_history' => 'Užsakymo istorija',
        'order_timeline' => 'Užsakymo laiko juosta',
        'order_documents' => 'Užsakymo dokumentai',
        'order_shipping' => 'Užsakymo pristatymas',
        'order_payments' => 'Užsakymo mokėjimai',
        'customer_information' => 'Kliento informacija',
        'billing_information' => 'Sąskaitos informacija',
        'shipping_information' => 'Pristatymo informacija',
    ],
];

