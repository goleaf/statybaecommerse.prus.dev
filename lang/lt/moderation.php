<?php

declare(strict_types=1);

return [
    'states' => [
        'draft'     => 'Juodraštis',
        'review'    => 'Peržiūroje',
        'published' => 'Paskelbta',
    ],
    'actions' => [
        'submit_for_review' => 'Pateikti peržiūrai',
        'approve'           => 'Patvirtinti ir paskelbti',
        'return_to_draft'   => 'Grąžinti į juodraštį',
        'request_changes'   => 'Prašyti pakeitimų',
    ],
    'messages' => [
        'submitted'         => 'Turinys pateiktas peržiūrai.',
        'approved'          => 'Turinys patvirtintas ir paskelbtas.',
        'returned'          => 'Turinys grąžintas į juodraštį.',
        'changes_requested' => 'Paprašyta papildomų pakeitimų.',
    ],
];
