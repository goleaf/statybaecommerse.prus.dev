<?php

declare(strict_types=1);

use App\Support\ErrorCode;

return [
    // @translators: Displayed when a requested page or record is missing (HTTP 404).
    ErrorCode::NotFound->value => 'Puslapis nerastas',

    // @translators: Shown when the system encounters an unexpected failure (HTTP 500).
    ErrorCode::ServerError->value => 'Serverio klaida',

    // @translators: Used when form submission fails validation and users must review inputs.
    ErrorCode::ValidationFailed->value => 'Patikrinkite įvestus duomenis',

    // @translators: Indicates the user needs to log in before accessing the requested content.
    ErrorCode::Unauthorized->value => 'Neturite teisių',

    // @translators: Indicates the user is logged in but does not have permission for the action.
    ErrorCode::Forbidden->value => 'Prieiga uždrausta',

    // @translators: Displayed when an order number could not be located in the system.
    ErrorCode::OrderNotFound->value => 'Užsakymas :order nerastas.',

    // @translators: Rodoma, kai pasirinktos prekės SKU atsargų neužtenka užsakymui įvykdyti.
    ErrorCodes::INVENTORY_INSUFFICIENT => 'SKU :sku atsargų nepakanka.',
    // @translators: Rodoma, kai nepavyksta įkelti prisijungusio vartotojo profilio duomenų.
    ErrorCodes::PROFILE_UNAVAILABLE => 'Profilis nepasiekiamas',
    // @translators: Rodoma, kai atsiskaitymo procesas negali tęstis dėl tuščio krepšelio.
    ErrorCodes::CHECKOUT_CART_EMPTY => 'Krepšelis tuščias',

    'messages' => [
        // @translators: Bendrinė žinutė API atsakymams, kai įvyksta nenumatyta serverio klaida.
        'server_error' => 'Įvyko klaida. Bandykite dar kartą vėliau.',
        // @translators: API žinutė, kai nepavyksta sugeneruoti vartotojo profilio atsakymo.
        'profile_unavailable' => 'Nepavyko įkelti jūsų profilio. Atnaujinkite puslapį ir bandykite dar kartą.',
        // @translators: API žinutė, kai atsiskaitymo procesas sustabdomas dėl tuščio krepšelio.
        'checkout_empty' => 'Jūsų krepšelis tuščias. Pridėkite prekių prieš tęsdami apmokėjimą.',
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
