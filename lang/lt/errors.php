<?php

declare(strict_types=1);

use App\Support\ErrorCodes;

return [
    // @translators: Rodoma, kai prašomas puslapis ar įrašas nerandamas (HTTP 404).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::NOT_FOUND) => 'Puslapis nerastas.',

    // @translators: Rodoma, kai užklausa yra neteisinga arba trūksta duomenų (HTTP 400).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::BAD_REQUEST) => 'Užklausa negali būti įvykdyta.',

    // @translators: Rodoma, kai naudojamas netinkamas HTTP metodas (HTTP 405).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::METHOD_NOT_ALLOWED) => 'Šis veiksmas neleidžiamas.',

    // @translators: Rodoma, kai sistema susiduria su nenumatyta serverio klaida (HTTP 500).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::SERVER_ERROR) => 'Įvyko klaida. Bandykite dar kartą vėliau.',

    // @translators: Naudojama, kai įvesti duomenys neatitinka validacijos taisyklių.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::VALIDATION_FAILED) => 'Patikrinkite pateiktus duomenis.',

    // @translators: Rodoma, kai vartotojas turi prisijungti prie sistemos.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::UNAUTHORIZED) => 'Prisijunkite, kad tęstumėte.',

    // @translators: Rodoma, kai vartotojas prisijungęs, bet neturi reikiamų teisių veiksmui.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::FORBIDDEN) => 'Neturite leidimo atlikti šio veiksmo.',

    // @translators: Rodoma, kai viršijamas leistinas užklausų skaičius (HTTP 429).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::TOO_MANY_REQUESTS) => 'Per daug bandymų. Bandykite vėliau.',

    // @translators: Srities klaida, kai nerandamas užsakymas.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::ORDER_NOT_FOUND) => 'Užsakymas :order nerastas.',

    // @translators: Srities klaida, kai nepakanka SKU atsargų.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::INVENTORY_INSUFFICIENT) => 'SKU :sku atsargų nepakanka.',
];
