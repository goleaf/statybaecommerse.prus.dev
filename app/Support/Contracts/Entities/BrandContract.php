<?php

declare(strict_types=1);

namespace App\Support\Contracts\Entities;

use App\Models\Brand;

final class BrandContract
{
    public static function fromModel(Brand $brand): array
    {
        return [
            'id' => (int) $brand->getKey(),
            'slug' => (string) $brand->slug,
            'name' => (string) ($brand->name ?? ''),
            'logo' => $brand->getLogoUrl(),
            'url' => route('localized.brands.show', ['locale' => app()->getLocale(), 'slug' => $brand->slug]),
        ];
    }
}
