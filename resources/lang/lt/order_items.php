<?php declare(strict_types=1);

return [
    // Navigation
    'title' => 'Užsakymo prekės',
    'plural' => 'Užsakymo prekės',
    'single' => 'Užsakymo prekė',
    // Sections
    'basic_information' => 'Pagrindinė informacija',
    'pricing' => 'Kainodara',
    'additional_information' => 'Papildoma informacija',
    // Fields
    'order' => 'Užsakymas',
    'product' => 'Prekė',
    'product_variant' => 'Prekės variantas',
    'product_name' => 'Prekės pavadinimas',
    'product_sku' => 'Prekės SKU',
    'quantity' => 'Kiekis',
    'unit_price' => 'Vieneto kaina',
    'discount_amount' => 'Nuolaidos suma',
    'total' => 'Iš viso',
    'notes' => 'Pastabos',
    // Table columns
    'order_number' => 'Užsakymo numeris',
    'created_at' => 'Sukurta',
    'updated_at' => 'Atnaujinta',
    'created_from' => 'Sukurta nuo',
    'created_until' => 'Sukurta iki',
    // Actions
    'create' => 'Sukurti užsakymo prekę',
    'edit' => 'Redaguoti užsakymo prekę',
    'view' => 'Peržiūrėti užsakymo prekę',
    'delete' => 'Ištrinti užsakymo prekę',
    // Messages
    'created_successfully' => 'Užsakymo prekė sėkmingai sukurta',
    'updated_successfully' => 'Užsakymo prekė sėkmingai atnaujinta',
    'deleted_successfully' => 'Užsakymo prekė sėkmingai ištrinta',
    'bulk_deleted_successfully' => 'Pasirinktos užsakymo prekės sėkmingai ištrintos',
    // Validation
    'validation' => [
        'order_id_required' => 'Užsakymas yra privalomas',
        'product_id_required' => 'Prekė yra privaloma',
        'quantity_required' => 'Kiekis yra privalomas',
        'quantity_min' => 'Kiekis turi būti ne mažesnis nei 1',
        'unit_price_required' => 'Vieneto kaina yra privaloma',
        'unit_price_min' => 'Vieneto kaina turi būti ne mažesnė nei 0',
    ],
    // Widgets
    'widgets' => [
        'total_items' => 'Iš viso prekių',
        'total_value' => 'Bendra vertė',
        'average_item_value' => 'Vidutinė prekės vertė',
        'top_products' => 'Populiariausios prekės',
        'recent_items' => 'Paskutinės prekės',
    ],
];

