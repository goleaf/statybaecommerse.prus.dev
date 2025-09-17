<?php

declare(strict_types=1);

return [
    'fields' => [
        'code_editor' => [
            'actions' => [
                'copy_to_clipboard' => [
                    'label' => 'Kopijuoti į iškarpinę',
                ],
            ],
        ],
        'file_upload' => [
            'editor' => [
                'actions' => [
                    'cancel' => [
                        'label' => 'Atšaukti',
                    ],
                    'drag_crop' => [
                        'label' => 'Vilkimo režimas "apkarpyti"',
                    ],
                    'drag_move' => [
                        'label' => 'Vilkimo režimas "perkelti"',
                    ],
                    'flip_horizontal' => [
                        'label' => 'Apversti horizontaliai',
                    ],
                    'flip_vertical' => [
                        'label' => 'Apversti vertikaliai',
                    ],
                    'move_down' => [
                        'label' => 'Perkelti žemyn',
                    ],
                    'move_left' => [
                        'label' => 'Perkelti kairėn',
                    ],
                    'move_right' => [
                        'label' => 'Perkelti dešinėn',
                    ],
                    'move_up' => [
                        'label' => 'Perkelti aukštyn',
                    ],
                    'reset' => [
                        'label' => 'Atstatyti',
                    ],
                    'rotate_left' => [
                        'label' => 'Pasukti kairėn',
                    ],
                    'rotate_right' => [
                        'label' => 'Pasukti dešinėn',
                    ],
                    'set_aspect_ratio' => [
                        'label' => 'Nustatyti proporcijas :ratio',
                    ],
                    'save' => [
                        'label' => 'Išsaugoti',
                    ],
                    'zoom_100' => [
                        'label' => 'Priartinti iki 100%',
                    ],
                    'zoom_in' => [
                        'label' => 'Priartinti',
                    ],
                    'zoom_out' => [
                        'label' => 'Nutolinti',
                    ],
                ],
            ],
        ],
        'key_value' => [
            'actions' => [
                'add' => [
                    'label' => 'Pridėti eilutę',
                ],
                'delete' => [
                    'label' => 'Ištrinti eilutę',
                ],
                'reorder' => [
                    'label' => 'Pakeisti eilutės tvarką',
                ],
            ],
            'fields' => [
                'key' => [
                    'label' => 'Raktas',
                ],
                'value' => [
                    'label' => 'Reikšmė',
                ],
            ],
        ],
        'markdown_editor' => [
            'toolbar_buttons' => [
                'attach_files' => 'Pridėti failus',
                'blockquote' => 'Citata',
                'bold' => 'Paryškintas',
                'bullet_list' => 'Sąrašas su ženkleliais',
                'code_block' => 'Kodo blokas',
                'heading' => 'Antraštė',
                'italic' => 'Kursyvas',
                'link' => 'Nuoroda',
                'ordered_list' => 'Numeruotas sąrašas',
                'redo' => 'Pakartoti',
                'strike' => 'Perbrauktas',
                'table' => 'Lentelė',
                'undo' => 'Atšaukti',
            ],
        ],
        'repeater' => [
            'actions' => [
                'add' => [
                    'label' => 'Pridėti prie :label',
                ],
                'add_between' => [
                    'label' => 'Įterpti',
                ],
                'delete' => [
                    'label' => 'Ištrinti',
                ],
                'clone' => [
                    'label' => 'Klonuoti',
                ],
                'reorder' => [
                    'label' => 'Perkelti',
                ],
                'move_down' => [
                    'label' => 'Perkelti žemyn',
                ],
                'move_up' => [
                    'label' => 'Perkelti aukštyn',
                ],
                'collapse' => [
                    'label' => 'Suskleisti',
                ],
                'expand' => [
                    'label' => 'Išskleisti',
                ],
                'collapse_all' => [
                    'label' => 'Suskleisti visus',
                ],
                'expand_all' => [
                    'label' => 'Išskleisti visus',
                ],
            ],
        ],
        'rich_editor' => [
            'dialogs' => [
                'link' => [
                    'actions' => [
                        'link' => 'Nuoroda',
                        'unlink' => 'Pašalinti nuorodą',
                    ],
                    'label' => 'URL',
                    'placeholder' => 'Įveskite URL',
                ],
            ],
            'toolbar_buttons' => [
                'attach_files' => 'Pridėti failus',
                'blockquote' => 'Citata',
                'bold' => 'Paryškintas',
                'bullet_list' => 'Sąrašas su ženkleliais',
                'code_block' => 'Kodo blokas',
                'color' => 'Spalva',
                'heading' => 'Antraštė',
                'italic' => 'Kursyvas',
                'link' => 'Nuoroda',
                'ordered_list' => 'Numeruotas sąrašas',
                'redo' => 'Pakartoti',
                'remove_color' => 'Pašalinti spalvą',
                'strike' => 'Perbrauktas',
                'underline' => 'Pabrauktas',
                'undo' => 'Atšaukti',
            ],
        ],
        'select' => [
            'actions' => [
                'create_option' => [
                    'modal' => [
                        'heading' => 'Sukurti',
                        'actions' => [
                            'create' => [
                                'label' => 'Sukurti',
                            ],
                            'create_another' => [
                                'label' => 'Sukurti ir sukurti kitą',
                            ],
                        ],
                    ],
                ],
                'edit_option' => [
                    'modal' => [
                        'heading' => 'Redaguoti',
                        'actions' => [
                            'save' => [
                                'label' => 'Išsaugoti',
                            ],
                        ],
                    ],
                ],
            ],
            'boolean' => [
                'true' => 'Taip',
                'false' => 'Ne',
            ],
            'loading_message' => 'Kraunama...',
            'max_items_message' => 'Galima pasirinkti tik :count.',
            'no_search_results_message' => 'Nėra rezultatų, atitinkančių jūsų paiešką.',
            'placeholder' => 'Pasirinkite parinktį',
            'searching_message' => 'Ieškoma...',
            'search_prompt' => 'Pradėkite rašyti, kad ieškotumėte...',
        ],
        'tags_input' => [
            'placeholder' => 'Naujas žymos',
        ],
        'text_input' => [
            'actions' => [
                'hide_password' => [
                    'label' => 'Slėpti slaptažodį',
                ],
                'show_password' => [
                    'label' => 'Rodyti slaptažodį',
                ],
            ],
        ],
        'toggle_buttons' => [
            'boolean' => [
                'true' => 'Taip',
                'false' => 'Ne',
            ],
        ],
        'wizard' => [
            'actions' => [
                'previous_step' => [
                    'label' => 'Atgal',
                ],
                'next_step' => [
                    'label' => 'Toliau',
                ],
            ],
        ],
    ],
];
