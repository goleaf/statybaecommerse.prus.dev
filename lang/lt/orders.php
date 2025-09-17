<?php

return [
    // Navigation
    'navigation_label' => 'Užsakymai',
    'navigation_group' => 'E-komercija',
    'model_label' => 'Užsakymas',
    'plural_model_label' => 'Užsakymai',

    // Form sections
    'basic_information' => 'Pagrindinė informacija',
    'financial_information' => 'Finansinė informacija',
    'addresses' => 'Adresai',
    'additional_information' => 'Papildoma informacija',
    'item_information' => 'Prekės informacija',
    'shipping_details' => 'Siuntimo detalės',
    'document_information' => 'Dokumento informacija',

    // Fields
    'number' => 'Numeris',
    'customer' => 'Klientas',
    'status' => 'Būsena',
    'payment_status' => 'Mokėjimo būsena',
    'payment_method' => 'Mokėjimo būdas',
    'payment_reference' => 'Mokėjimo nuoroda',
    'subtotal' => 'Tarpinė suma',
    'tax_amount' => 'Mokesčių suma',
    'shipping_amount' => 'Siuntimo suma',
    'discount_amount' => 'Nuolaidos suma',
    'total' => 'Bendra suma',
    'currency' => 'Valiuta',
    'billing_address' => 'Atsiskaitymo adresas',
    'shipping_address' => 'Siuntimo adresas',
    'notes' => 'Pastabos',
    'channel' => 'Kanalas',
    'zone' => 'Zona',
    'partner' => 'Partneris',
    'shipped_at' => 'Išsiųsta',
    'delivered_at' => 'Pristatyta',
    'created_at' => 'Sukurta',
    'created_from' => 'Sukurta nuo',
    'created_until' => 'Sukurta iki',

    // Statuses
    'statuses' => [
        'pending' => 'Laukiantis',
        'processing' => 'Apdorojamas',
        'confirmed' => 'Patvirtintas',
        'shipped' => 'Išsiųstas',
        'delivered' => 'Pristatytas',
        'completed' => 'Užbaigtas',
        'cancelled' => 'Atšauktas',
    ],

    // Payment statuses
    'payment_statuses' => [
        'pending' => 'Laukiantis',
        'paid' => 'Apmokėtas',
        'failed' => 'Nepavyko',
        'refunded' => 'Grąžintas',
        'partially_refunded' => 'Dalinai grąžintas',
    ],

    // Order items
    'order_items' => 'Užsakymo prekės',
    'order_item' => 'Užsakymo prekė',
    'product' => 'Prekė',
    'product_name' => 'Prekės pavadinimas',
    'product_variant' => 'Prekės variantas',
    'sku' => 'SKU',
    'quantity' => 'Kiekis',
    'unit_price' => 'Vieneto kaina',
    'price' => 'Kaina',
    'items_count' => 'Prekių skaičius',

    // Shipping
    'shipping_information' => 'Siuntimo informacija',
    'shipping' => 'Siuntimas',
    'carrier_name' => 'Vežėjas',
    'service' => 'Paslauga',
    'tracking_number' => 'Sekimo numeris',
    'tracking_url' => 'Sekimo nuoroda',
    'estimated_delivery' => 'Numatomas pristatymas',
    'shipping_cost' => 'Siuntimo kaina',
    'weight' => 'Svoris',
    'dimensions' => 'Matmenys',
    'dimension_type' => 'Matmens tipas',
    'dimension_value' => 'Matmens reikšmė',
    'add_dimension' => 'Pridėti matmenį',
    'metadata' => 'Metaduomenys',
    'metadata_key' => 'Metaduomenų raktas',
    'metadata_value' => 'Metaduomenų reikšmė',
    'add_metadata' => 'Pridėti metaduomenis',
    'shipping_status' => 'Siuntimo būsena',

    // Documents
    'documents' => 'Dokumentai',
    'document' => 'Dokumentas',
    'document_name' => 'Dokumento pavadinimas',
    'document_type' => 'Dokumento tipas',
    'document_file' => 'Dokumento failas',
    'file_size' => 'Failo dydis',
    'mime_type' => 'MIME tipas',
    'download' => 'Atsisiųsti',
    'description' => 'Aprašymas',

    // Document types
    'document_types' => [
        'invoice' => 'Sąskaita faktūra',
        'receipt' => 'Kvitas',
        'shipping_label' => 'Siuntimo etiketė',
        'return_label' => 'Grąžinimo etiketė',
        'warranty' => 'Garantija',
        'manual' => 'Instrukcija',
        'other' => 'Kita',
    ],

    // Address fields
    'address_field' => 'Lauko pavadinimas',
    'address_value' => 'Lauko reikšmė',
    'add_address_field' => 'Pridėti adreso lauką',

    // Actions
    'actions' => [
        'view' => 'Peržiūrėti',
        'edit' => 'Redaguoti',
        'delete' => 'Ištrinti',
        'mark_shipped' => 'Pažymėti kaip išsiųstą',
        'mark_delivered' => 'Pažymėti kaip pristatytą',
        'cancel' => 'Atšaukti',
        'bulk_mark_shipped' => 'Masinis išsiuntimas',
    ],

    // Tabs
    'tabs' => [
        'all' => 'Visi',
        'pending' => 'Laukiantys',
        'processing' => 'Apdorojami',
        'shipped' => 'Išsiųsti',
        'delivered' => 'Pristatyti',
        'completed' => 'Užbaigti',
        'cancelled' => 'Atšaukti',
    ],

    // Stats
    'stats' => [
        'total_orders' => 'Iš viso užsakymų',
        'pending_orders' => 'Laukiantys užsakymai',
        'processing_orders' => 'Apdorojami užsakymai',
        'shipped_orders' => 'Išsiųsti užsakymai',
        'delivered_orders' => 'Pristatyti užsakymai',
        'completed_orders' => 'Užbaigti užsakymai',
        'cancelled_orders' => 'Atšaukti užsakymai',
        'total_revenue' => 'Bendros pajamos',
        'average_order_value' => 'Vidutinė užsakymo vertė',
        'today_orders' => 'Šiandienos užsakymai',
        'this_week_orders' => 'Šios savaitės užsakymai',
        'this_month_orders' => 'Šio mėnesio užsakymai',
        'all_time' => 'Viso laiko',
        'need_attention' => 'Reikia dėmesio',
        'in_progress' => 'Vykdoma',
        'in_transit' => 'Kelyje',
        'completed_deliveries' => 'Pristatymai',
        'fully_completed' => 'Pilnai užbaigti',
        'cancelled' => 'Atšaukti',
        'lifetime_revenue' => 'Viso laiko pajamos',
        'per_order' => 'Už užsakymą',
        'today' => 'Šiandien',
        'this_week' => 'Šią savaitę',
        'this_month' => 'Šį mėnesį',
    ],

    // Charts
    'charts' => [
        'orders_over_time' => 'Užsakymai laikui bėgant',
        'orders_count' => 'Užsakymų skaičius',
        'revenue' => 'Pajamos',
    ],

    // Widgets
    'widgets' => [
        'recent_orders' => 'Paskutiniai užsakymai',
    ],

    // Filters
    'is_paid' => 'Apmokėtas',

    // Frontend specific
    'my_orders' => 'Mano užsakymai',
    'manage_your_orders' => 'Tvarkykite savo užsakymus',
    'search' => 'Ieškoti',
    'search_placeholder' => 'Ieškoti pagal numerį ar pastabas...',
    'all_statuses' => 'Visos būsenos',
    'filter' => 'Filtruoti',
    'order' => 'Užsakymas',
    'placed_on' => 'Pateiktas',
    'items' => 'Prekės',
    'and_more_items' => 'ir dar :count prekių',
    'view_details' => 'Peržiūrėti detales',
    'confirm_cancel' => 'Ar tikrai norite atšaukti šį užsakymą?',
    'cancel_order' => 'Atšaukti užsakymą',
    'no_orders' => 'Nėra užsakymų',
    'no_orders_description' => 'Jūs dar neturite užsakymų. Pradėkite apsipirkinėti!',
    'start_shopping' => 'Pradėti apsipirkinėti',
    'order_details' => 'Užsakymo detales #:number',
    'order_summary' => 'Užsakymo suvestinė',
    'track_package' => 'Sekti siuntą',
    'back_to_orders' => 'Grįžti prie užsakymų',
    'actions' => 'Veiksmai',

    // Messages
    'messages' => [
        'created_successfully' => 'Užsakymas sėkmingai sukurtas',
        'creation_failed' => 'Nepavyko sukurti užsakymo',
        'updated_successfully' => 'Užsakymas sėkmingai atnaujintas',
        'update_failed' => 'Nepavyko atnaujinti užsakymo',
        'deleted_successfully' => 'Užsakymas sėkmingai ištrintas',
        'cannot_edit' => 'Negalite redaguoti šio užsakymo',
        'cannot_delete' => 'Negalite ištrinti šio užsakymo',
        'cannot_cancel' => 'Negalite atšaukti šio užsakymo',
        'cancelled_successfully' => 'Užsakymas sėkmingai atšauktas',
    ],
];
