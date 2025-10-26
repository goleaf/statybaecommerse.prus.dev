<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

/**
 * DiscountConditionOperatorCollection
 *
 * Resource collection responsible for normalising the available operator list
 * into a predictable API payload.
 */
final class DiscountConditionOperatorCollection extends ResourceCollection
{
    /** @var string|null */
    public static $wrap = 'operators';

    /**
     * Create a new resource collection instance.
     *
     * @param Collection<int, array{key: string, label: string}> $collection
     */
    public function __construct(Collection $collection)
    {
        // Ensure parents receive the value object from the controller.
        parent::__construct($collection);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int, array<string, string>>
     */
    public function toArray(Request $request): array
    {
        // Emit the key/label pairs so the client can render dropdowns easily.
        /** @var array<int, array<string, string>> $operators */
        $operators = $this->collection->values()->all();

        return $operators;
    }
}
