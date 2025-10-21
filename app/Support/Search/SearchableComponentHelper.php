<?php

declare(strict_types=1);

namespace App\Support\Search;

use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;

final class SearchableComponentHelper
{
    private const META_KEY = 'searchable_input.selected';

    public static function remember(SearchableInput $component, SearchResult $result): void
    {
        $component->meta(self::META_KEY, $result->toArray());
    }

    public static function forget(SearchableInput $component): void
    {
        $component->meta(self::META_KEY, null);
    }

    public static function recall(SearchableInput $component): ?SearchResult
    {
        $stored = $component->getMeta(self::META_KEY);

        if (! is_array($stored) || ! isset($stored['value'])) {
            return null;
        }

        return SearchResult::fromArray([
            'value' => is_string($stored['value']) ? $stored['value'] : (string) $stored['value'],
            'label' => is_string($stored['label'] ?? null) ? $stored['label'] : (string) ($stored['value'] ?? ''),
            'data'  => is_array($stored['data'] ?? null) ? $stored['data'] : [],
        ]);
    }

    public static function apply(SearchableInput $component, SearchResult $result): void
    {
        $component
            ->state($result->value())
            ->options([
                $result->value() => $result->label(),
            ]);

        self::remember($component, $result);
    }
}
