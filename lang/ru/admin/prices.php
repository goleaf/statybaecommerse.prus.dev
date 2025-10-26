<?php

declare(strict_types=1);

return [
    'navigation' => [
        'label' => 'Цены',
    ],

    'model' => [
        'singular' => 'Цена',
        'plural'   => 'Цены',
    ],

    'sections' => [
        'basic_information' => 'Основная информация',
        'pricing'           => 'Ценообразование',
        'validity'          => 'Период действия',
        'metadata'          => 'Метаданные',
    ],

    'fields' => [
        'priceable'      => 'Связанный объект',
        'priceable_type' => 'Тип объекта',
        'priceable_name' => 'Название',
        'currency'       => 'Валюта',
        'type'           => 'Тип цены',
        'amount'         => 'Сумма',
        'compare_amount' => 'Сумма сравнения',
        'cost_amount'    => 'Себестоимость',
        'is_enabled'     => 'Активно',
        'starts_at'      => 'Начало',
        'ends_at'        => 'Окончание',
        'metadata'       => 'Метаданные',
        'created_at'     => 'Создано',
        'updated_at'     => 'Обновлено',
    ],

    'filters' => [
        'priceable_type' => 'Тип объекта',
        'currency'       => 'Валюта',
        'type'           => 'Тип цены',
        'is_enabled'     => 'Статус активности',
        'active'         => 'Активные цены',
    ],

    'priceable_types' => [
        'product' => 'Продукт',
        'variant' => 'Вариант',
    ],

    'types' => [
        'retail'    => 'Розничная',
        'wholesale' => 'Оптовая',
        'special'   => 'Специальная',
        'sale'      => 'Распродажа',
    ],
];
