<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Livewire\Pages\SingleProduct;

/**
 * @deprecated The standalone ProductPage component has been merged into the
 *             SingleProduct Livewire page to avoid maintaining duplicate
 *             product detail templates. This file now aliases the canonical
 *             implementation so existing references keep working.
 */
class_alias(SingleProduct::class, __NAMESPACE__ . '\\ProductPage');
