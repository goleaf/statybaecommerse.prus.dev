<?php

declare(strict_types=1);

use App\Support\ErrorCode;

return [
    // @translators: Displayed when a requested page or record is missing (HTTP 404).
    ErrorCode::NotFound->value => 'Страница не найдена',

    // @translators: Shown when the system encounters an unexpected failure (HTTP 500).
    ErrorCode::ServerError->value => 'Ошибка сервера',

    // @translators: Used when form submission fails validation and users must review inputs.
    ErrorCode::ValidationFailed->value => 'Проверьте введённые данные',

    // @translators: Indicates the user needs to log in before accessing the requested content.
    ErrorCode::Unauthorized->value => 'Нет доступа',

    // @translators: Indicates the user is logged in but does not have permission for the action.
    ErrorCode::Forbidden->value => 'Доступ запрещён',

    // @translators: Displayed when an order number could not be located in the system.
    ErrorCode::OrderNotFound->value => 'Заказ :order не найден.',

    // @translators: Shown when there is not enough stock to fulfill a request for a SKU.
    ErrorCode::InventoryInsufficient->value => 'Недостаточно запасов для артикула :sku.',
];
