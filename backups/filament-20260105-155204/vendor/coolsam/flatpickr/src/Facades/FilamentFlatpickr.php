<?php

namespace Coolsam\Flatpickr\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see FilamentFlatpickr
 */
class FilamentFlatpickr extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Coolsam\Flatpickr\FilamentFlatpickr::class;
    }
}
