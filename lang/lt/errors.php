<?php

declare(strict_types=1);

use App\Support\ErrorCodes;

return [
    // @translators: Rodoma, kai prašomas puslapis ar įrašas nerandamas (HTTP 404).
    ErrorCodes::NOT_FOUND => 'Puslapis nerastas',

    // @translators: Rodoma, kai sistema susiduria su nenumatyta serverio klaida (HTTP 500).
    ErrorCodes::SERVER_ERROR => 'Serverio klaida',

    // @translators: Naudojama, kai įvesti duomenys neatitinka validacijos taisyklių.
    ErrorCodes::VALIDATION_FAILED => 'Patikrinkite įvestus duomenis',

    // @translators: Rodoma, kai vartotojas turi prisijungti prie sistemos.
    ErrorCodes::UNAUTHORIZED => 'Neturite teisių',

    // @translators: Rodoma, kai vartotojas prisijungęs, bet neturi reikiamų teisių veiksmui.
    ErrorCodes::FORBIDDEN => 'Prieiga uždrausta',

    // @translators: Rodoma, kai sistema neranda užsakymo pagal pateiktą numerį.
    ErrorCodes::ORDER_NOT_FOUND => 'Užsakymas :order nerastas.',

    // @translators: Rodoma, kai pasirinktos prekės SKU atsargų neužtenka užsakymui įvykdyti.
    ErrorCodes::INVENTORY_INSUFFICIENT => 'SKU :sku atsargų nepakanka.',

    'messages' => [
        // @translators: Bendrinė žinutė API atsakymams, kai įvyksta nenumatyta serverio klaida.
        'server_error' => 'Įvyko klaida. Bandykite dar kartą vėliau.',
    ],

    'pages' => [
        'unexpected' => [
            // @translators: Antraštė, rodoma bendrame klaidų puslapyje, kai įvyksta nenumatyta klaida.
            'title' => 'Įvyko nenumatyta klaida',
            // @translators: Aprašymas, rodoma bendrame klaidų puslapyje, kai įvyksta nenumatyta klaida.
            'description' => 'Mūsų komanda jau gavo pranešimą ir tiria problemą. Jei tai kartojasi, pasidalykite sekimo ID su palaikymo komanda.',
            // @translators: Pagrindinio veiksmo mygtuko tekstas bendrame klaidų puslapyje.
            'primary' => 'Grįžti į pradžią',
            // @translators: Antrojo veiksmo mygtuko tekstas bendrame klaidų puslapyje.
            'secondary' => 'Susisiekti su palaikymu',
        ],
    ],
];
