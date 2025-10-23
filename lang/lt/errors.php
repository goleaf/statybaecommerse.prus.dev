<?php

declare(strict_types=1);

return [
    'error' => [
        // @translators: Rodoma, kai prašomas puslapis ar įrašas nerandamas (HTTP 404).
        'not_found' => 'Puslapis nerastas',

        // @translators: Rodoma, kai sistema susiduria su nenumatyta serverio klaida (HTTP 500).
        'server' => 'Serverio klaida',

        // @translators: Naudojama, kai įvesti duomenys neatitinka validacijos taisyklių.
        'validation' => 'Patikrinkite įvestus duomenis',

        // @translators: Rodoma, kai vartotojas turi prisijungti prie sistemos.
        'unauthorized' => 'Neturite teisių',

        // @translators: Rodoma, kai vartotojas prisijungęs, bet neturi reikiamų teisių veiksmui.
        'forbidden' => 'Prieiga uždrausta',
    ],

    'orders' => [
        // @translators: Rodoma, kai sistema neranda užsakymo pagal pateiktą numerį.
        'not_found' => 'Užsakymas :order nerastas.',
    ],

    'inventory' => [
        // @translators: Rodoma, kai pasirinktos prekės SKU atsargų neužtenka užsakymui įvykdyti.
        'insufficient' => 'SKU :sku atsargų nepakanka.',
    ],
];
