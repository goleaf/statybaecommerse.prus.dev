<?php

declare(strict_types=1);

return [
    'link_search' => [
        'placeholder' => 'Ищите товары, категории, коллекции или вставьте ссылку',
        'static'      => [
            'collections' => [
                'description' => 'Подборки товаров от нашей команды.',
                'label'       => 'Подборки',
            ],
            'contact' => [
                'description' => 'Способы связи со службой поддержки.',
                'label'       => 'Страница контактов',
            ],
            'home' => [
                'description' => 'Основная страница витрины.',
                'label'       => 'Главная страница',
            ],
            'posts' => [
                'description' => 'Последние статьи нашей команды.',
                'label'       => 'Блог',
            ],
            'products' => [
                'description' => 'Просматривайте весь каталог.',
                'label'       => 'Все товары',
            ],
        ],
        'types' => [
            'category'   => 'Категория',
            'collection' => 'Коллекция',
            'post'       => 'Запись блога',
            'product'    => 'Товар',
            'static'     => 'Статичная страница',
        ],
    ],
];
