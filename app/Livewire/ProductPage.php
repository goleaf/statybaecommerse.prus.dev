<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Livewire\Pages\SingleProduct;

/**
 * @deprecated The standalone ProductPage component has been merged into the
 *             SingleProduct Livewire page to avoid maintaining duplicate
 *             product detail templates. This class now simply extends the new
 *             canonical implementation so existing references continue to
 *             operate without modification.
 */
final class ProductPage extends SingleProduct {}
