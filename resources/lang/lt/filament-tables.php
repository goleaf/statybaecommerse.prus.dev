<?php

declare(strict_types=1);

return [
    'columns' => [
        'text' => [
            'actions' => [
                'collapse_list' => 'Rodyti :count mažiau',
                'expand_list' => 'Rodyti :count daugiau',
            ],
            'more_list_items' => 'ir :count daugiau',
        ],
    ],
    'fields' => [
        'bulk_select_page' => [
            'label' => 'Pažymėti/atžymėti visus elementus masiniam veiksmui.',
        ],
        'bulk_select_record' => [
            'label' => 'Pažymėti/atžymėti elementą :key masiniam veiksmui.',
        ],
        'bulk_select_group' => [
            'label' => 'Pažymėti/atžymėti grupę :title masiniam veiksmui.',
        ],
        'search' => [
            'label' => 'Ieškoti',
            'placeholder' => 'Ieškoti',
            'indicator' => 'Ieškoti',
        ],
    ],
    'summary' => [
        'heading' => 'Santrauka',
        'subheadings' => [
            'all' => 'Visi :label',
            'group' => ':group santrauka',
            'page' => 'Šis puslapis',
        ],
        'summarizers' => [
            'average' => [
                'label' => 'Vidurkis',
            ],
            'count' => [
                'label' => 'Skaičius',
            ],
            'sum' => [
                'label' => 'Suma',
            ],
        ],
    ],
    'actions' => [
        'disable_reordering' => [
            'label' => 'Baigti rikiavimą',
        ],
        'enable_reordering' => [
            'label' => 'Rikiuoti įrašus',
        ],
        'filter' => [
            'label' => 'Filtruoti',
        ],
        'group' => [
            'label' => 'Grupuoti',
        ],
        'open_bulk_actions' => [
            'label' => 'Atidaryti veiksmus',
        ],
        'toggle_columns' => [
            'label' => 'Perjungti stulpelius',
        ],
    ],
    'empty' => [
        'heading' => 'Nėra :model',
        'description' => 'Sukurkite :model, kad pradėtumėte.',
    ],
    'filters' => [
        'actions' => [
            'remove' => [
                'label' => 'Pašalinti filtrą',
            ],
            'remove_all' => [
                'label' => 'Pašalinti visus filtrus',
                'tooltip' => 'Pašalinti visus filtrus',
            ],
            'reset' => [
                'label' => 'Atstatyti',
            ],
        ],
        'heading' => 'Filtrai',
        'indicator' => 'Aktyvūs filtrai',
        'multi_select' => [
            'placeholder' => 'Visi',
        ],
        'select' => [
            'placeholder' => 'Visi',
        ],
        'trashed' => [
            'label' => 'Ištrinti įrašai',
            'only_trashed' => 'Tik ištrinti įrašai',
            'with_trashed' => 'Su ištrintais įrašais',
            'without_trashed' => 'Be ištrintų įrašų',
        ],
    ],
    'grouping' => [
        'fields' => [
            'group' => [
                'label' => 'Grupuoti pagal',
                'placeholder' => 'Grupuoti pagal',
            ],
            'direction' => [
                'label' => 'Grupavimo kryptis',
                'options' => [
                    'asc' => 'Didėjančiai',
                    'desc' => 'Mažėjančiai',
                ],
            ],
        ],
    ],
    'reorder_indicator' => 'Vilkite ir paleiskite įrašus į tvarką.',
    'selection_indicator' => [
        'selected_count' => '1 įrašas pažymėtas|:count įrašai pažymėti',
        'actions' => [
            'select_all' => [
                'label' => 'Pažymėti visus :count',
            ],
            'deselect_all' => [
                'label' => 'Atžymėti visus',
            ],
        ],
    ],
    'sorting' => [
        'fields' => [
            'column' => [
                'label' => 'Rikiuoti pagal',
            ],
            'direction' => [
                'label' => 'Rikiavimo kryptis',
                'options' => [
                    'asc' => 'Didėjančiai',
                    'desc' => 'Mažėjančiai',
                ],
            ],
        ],
    ],
];
