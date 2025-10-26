<?php

declare(strict_types=1);

return [
    'navigation_label'   => 'История товара',
    'plural_model_label' => 'История товаров',
    'model_label'        => 'История товара',
    'actions'            => [
        'created'          => 'Создано',
        'updated'          => 'Обновлено',
        'deleted'          => 'Удалено',
        'restored'         => 'Восстановлено',
        'price_changed'    => 'Цена изменена',
        'stock_updated'    => 'Запасы обновлены',
        'stock_changed'    => 'Запасы обновлены',
        'status_changed'   => 'Статус изменён',
        'category_changed' => 'Категория изменена',
        'image_changed'    => 'Изображение изменено',
        'custom'           => 'Пользовательское действие',
    ],
    'fields' => [
        'action'         => 'Действие',
        'field_name'     => 'Поле',
        'old_value'      => 'Старое значение',
        'new_value'      => 'Новое значение',
        'price'          => 'Цена',
        'sale_price'     => 'Цена со скидкой',
        'stock_quantity' => 'Количество на складе',
        'status'         => 'Статус',
        'is_visible'     => 'Видимость',
        'description'    => 'Описание',
        'name'           => 'Название',
        'category'       => 'Категория',
        'image'          => 'Изображение',
        'metadata'       => 'Метаданные',
    ],
    'summaries' => [
        'created' => 'Создано поле :field',
        'deleted' => 'Удалено поле :field',
        'updated' => 'Поле :field изменено с :from на :to',
    ],
    'impact' => [
        'high'   => 'Изменение с высоким влиянием',
        'medium' => 'Изменение со средним влиянием',
        'low'    => 'Изменение с низким влиянием',
    ],
];
