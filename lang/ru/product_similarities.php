<?php

declare(strict_types=1);

return [
    'title'  => 'Сходства товаров',
    'plural' => 'Сходства товаров',
    'single' => 'Сходство товара',

    'product'          => 'Основной товар',
    'similar_product'  => 'Похожий товар',
    'algorithm_type'   => 'Тип алгоритма',
    'similarity_score' => 'Уровень сходства',
    'similarity_data'  => 'Данные расчёта',

    'filters' => [
        'product'         => 'Товар',
        'similar_product' => 'Похожий товар',
        'algorithm_type'  => 'Тип алгоритма',
        'min_score'       => 'Минимальный уровень',
        'max_score'       => 'Максимальный уровень',
    ],

    'algorithms' => [
        'cosine_similarity'  => 'Косинусное сходство',
        'jaccard_similarity' => 'Сходство Жаккара',
    ],
];
