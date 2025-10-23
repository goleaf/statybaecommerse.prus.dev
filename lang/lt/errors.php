<?php

declare(strict_types=1);

use App\Support\ErrorCode;

return [
    'titles' => [
        // @translators: Rodoma, kai prašomas puslapis ar įrašas nerandamas (HTTP 404).
        ErrorCodes::key(ErrorCodes::NOT_FOUND) => 'Puslapis nerastas',

        // @translators: Rodoma, kai sistema susiduria su nenumatyta serverio klaida (HTTP 500).
        ErrorCodes::key(ErrorCodes::SERVER_ERROR) => 'Serverio klaida',

        // @translators: Naudojama, kai įvesti duomenys neatitinka validacijos taisyklių.
        ErrorCodes::key(ErrorCodes::VALIDATION_FAILED) => 'Patikrinkite įvestus duomenis',

        // @translators: Rodoma, kai vartotojas turi prisijungti prie sistemos.
        ErrorCodes::key(ErrorCodes::UNAUTHORIZED) => 'Neturite teisių',

        // @translators: Rodoma, kai vartotojas prisijungęs, bet neturi reikiamų teisių veiksmui.
        ErrorCodes::key(ErrorCodes::FORBIDDEN) => 'Prieiga uždrausta',

        // @translators: Rodoma, kai sistema neranda užsakymo pagal pateiktą numerį.
        ErrorCodes::key(ErrorCodes::ORDER_NOT_FOUND) => 'Užsakymas :order nerastas.',

        // @translators: Rodoma, kai pasirinktos prekės SKU atsargų neužtenka užsakymui įvykdyti.
        ErrorCodes::key(ErrorCodes::INVENTORY_INSUFFICIENT) => 'SKU :sku atsargų nepakanka.',
        // @translators: Rodoma, kai nepavyksta įkelti prisijungusio vartotojo profilio duomenų.
        ErrorCodes::key(ErrorCodes::PROFILE_UNAVAILABLE) => 'Profilis nepasiekiamas',
        // @translators: Rodoma, kai atsiskaitymo procesas negali tęstis dėl tuščio krepšelio.
        ErrorCodes::key(ErrorCodes::CHECKOUT_CART_EMPTY) => 'Krepšelis tuščias',
    ],

    'messages' => [
        // @translators: Bendrinė žinutė API atsakymams, kai įvyksta nenumatyta serverio klaida.
        ErrorCodes::key(ErrorCodes::SERVER_ERROR) => 'Įvyko klaida. Bandykite dar kartą vėliau.',
        // @translators: API žinutė, kai validacija nepavyksta ir nėra konkrečios žinutės.
        ErrorCodes::key(ErrorCodes::VALIDATION_FAILED) => 'Prašome patikrinti įvestus duomenis prieš tęsdami.',
        // @translators: API žinutė, kai vartotojui reikia prisijungti.
        ErrorCodes::key(ErrorCodes::UNAUTHORIZED) => 'Turite prisijungti, kad galėtumėte tęsti.',
        // @translators: API žinutė, kai vartotojas neturi reikiamų teisių veiksmui.
        ErrorCodes::key(ErrorCodes::FORBIDDEN) => 'Neturite pakankamų teisių šiam veiksmui atlikti.',
        // @translators: API žinutė, kai prašomas išteklius nerandamas.
        ErrorCodes::key(ErrorCodes::NOT_FOUND) => 'Nepavyko rasti prašomo ištekliaus.',
        // @translators: API žinutė, kai nepavyksta sugeneruoti vartotojo profilio atsakymo.
        ErrorCodes::key(ErrorCodes::PROFILE_UNAVAILABLE) => 'Nepavyko įkelti jūsų profilio. Atnaujinkite puslapį ir bandykite dar kartą.',
        // @translators: API žinutė, kai atsiskaitymo procesas sustabdomas dėl tuščio krepšelio.
        ErrorCodes::key(ErrorCodes::CHECKOUT_CART_EMPTY) => 'Jūsų krepšelis tuščias. Pridėkite prekių prieš tęsdami apmokėjimą.',
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
