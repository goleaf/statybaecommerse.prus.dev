<?php

declare(strict_types=1);

use App\Support\ErrorCodes;

return [
    // @translators: Сообщение при отсутствии страницы или записи (HTTP 404).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::NOT_FOUND) => 'Страница не найдена.',

    // @translators: Сообщение при некорректной или неполной HTTP-запросе (HTTP 400).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::BAD_REQUEST) => 'Запрос не может быть обработан.',

    // @translators: Сообщение при использовании неподдерживаемого HTTP-метода (HTTP 405).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::METHOD_NOT_ALLOWED) => 'Метод не поддерживается.',

    // @translators: Показывается при непредвиденной ошибке сервера (HTTP 500).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::SERVER_ERROR) => 'Произошла непредвиденная ошибка. Попробуйте позже.',

    // @translators: Используется, когда введённые данные не проходят проверку.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::VALIDATION_FAILED) => 'Проверьте введённые данные.',

    // @translators: Указывает, что пользователь должен войти в систему.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::UNAUTHORIZED) => 'Войдите в систему, чтобы продолжить.',

    // @translators: Указывает, что у вошедшего пользователя нет прав для действия.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::FORBIDDEN) => 'У вас нет прав для этого действия.',

    // @translators: Показывается, когда превышен лимит запросов (HTTP 429).
    ErrorCodes::normalizedTranslationKey(ErrorCodes::TOO_MANY_REQUESTS) => 'Слишком много запросов. Попробуйте позже.',

    // @translators: Ошибка домена, когда заказ не найден.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::ORDER_NOT_FOUND) => 'Заказ :order не найден.',

    // @translators: Ошибка домена, когда недостаточно товара.
    ErrorCodes::normalizedTranslationKey(ErrorCodes::INVENTORY_INSUFFICIENT) => 'Недостаточно товара для SKU :sku.',
];
