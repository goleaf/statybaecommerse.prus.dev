<?php

declare(strict_types=1);

return [
    'notifications' => [
        'completed' => [
            'subject' => 'Jūsų eksportas „:name“ paruoštas',
            'intro' => 'Jūsų prašytas eksportas sėkmingai sugeneruotas.',
            'format' => 'Formatas: :format',
            'action' => 'Atsisiųsti eksportą',
            'expires' => 'Nuoroda nustos galioti po :minutes min.',
        ],
        'failed' => [
            'subject' => 'Eksportas „:name“ nepavyko',
            'intro' => 'Nepavyko sugeneruoti jūsų prašyto eksporto.',
            'reason' => 'Priežastis: :reason',
            'support' => 'Bandykite dar kartą arba susisiekite su pagalba, jei problema kartojasi.',
        ],
    ],
    'filament' => [
        'bulk_action' => [
            'label' => 'Eksportuoti pasirinktus',
            'modal_heading' => 'Eksportuoti pasirinktus :label',
            'modal_description' => 'Pasirinkite formatą ir stulpelius, kuriuos norite eksportuoti.',
            'success' => 'Eksportas įtrauktas į eilę. Apie parengtį informuosime el. paštu.',
            'success_body' => 'Kai tik eksportas bus sugeneruotas, gausite atsisiuntimo nuorodą.',
            'format_label' => 'Formatas',
            'columns_label' => 'Stulpeliai',
            'columns_help' => 'Pasirinkite, kurie stulpeliai bus įtraukti į failą.',
        ],
    ],
];
