<?php

declare(strict_types=1);

namespace App\Enums\Api;

/**
 * ProductSort enumerates allowed sorting strategies for the public product catalogue API.
 */
enum ProductSort: string
{
    case NAME_ASC = 'name';
    case NAME_DESC = '-name';
    case PRICE_ASC = 'price';
    case PRICE_DESC = '-price';
    case NEWEST = 'newest';
}
