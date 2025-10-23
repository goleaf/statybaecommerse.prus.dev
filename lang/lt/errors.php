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
];
