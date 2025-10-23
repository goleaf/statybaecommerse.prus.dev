<?php

declare(strict_types=1);

return [
    'orders' => [
        // @translators: Rodoma, kai nurodytas užsakymo numeris neegzistuoja.
        'not_found' => 'Užsakymas :order nerastas.',
    ],
    'inventory' => [
        // @translators: Rodoma, kai nurodytam SKU nepakanka atsargų.
        'insufficient' => 'Atsargų SKU :sku nepakanka.',
    ],
    'http' => [
        // @translators: Rodoma, kai prašomas išteklius nerastas (HTTP 404).
        'not_found' => 'Prašomas išteklius nerastas.',
        // @translators: Rodoma, kai vartotojas neprisijungęs (HTTP 401).
        'unauthorized' => 'Norint pasiekti šį išteklių reikia prisijungti.',
        // @translators: Rodoma, kai vartotojui trūksta teisių (HTTP 403).
        'forbidden' => 'Neturite leidimo atlikti šį veiksmą.',
        // @translators: Rodoma, kai HTTP metodas neleidžiamas (HTTP 405).
        'method_not_allowed' => 'Šis HTTP metodas neleidžiamas.',
        // @translators: Rodoma, kai užklausa suformuota neteisingai (HTTP 400).
        'bad_request' => 'Užklausos nepavyko apdoroti dėl neteisingo formato.',
        // @translators: Rodoma, kai klientas apribojamas dėl per dažno naudojimo (HTTP 429).
        'too_many_requests' => 'Per daug užklausų. Bandykite dar kartą vėliau.',
    ],
    'validation' => [
        // @translators: Rodoma, kai pateikti duomenys neatitinka taisyklių.
        'failed' => 'Pateikti duomenys yra neteisingi.',
    ],
    'internal' => [
        // @translators: Rodoma, kai įvyksta nenumatyta serverio klaida (HTTP 500).
        'server_error' => 'Įvyko netikėta klaida.',
    ],
];
