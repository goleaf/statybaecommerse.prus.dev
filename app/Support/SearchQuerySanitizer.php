<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Class SearchQuerySanitizer
 *
 * Central helper responsible for normalising free-form search input before it is
 * used inside database queries. Consolidating the sanitisation logic in one
 * place keeps controllers and models lightweight while ensuring consistent
 * filtering rules across the storefront.
 */
final class SearchQuerySanitizer
{
    /**
     * Remove HTML tags, collapse whitespace, strip disallowed characters, and
     * cap the length of the supplied query so it is safe to feed into LIKE
     * clauses or analytics. The method intentionally keeps common punctuation
     * (dashes, quotes, dots) that shoppers frequently rely on when searching
     * for model numbers.
     */
    public static function sanitize(?string $query, int $maxLength = 120): string
    {
        if ($query === null) {
            return '';
        }

        // Strip HTML tags and control characters to neutralise any injected markup.
        $withoutTags = strip_tags($query);

        // Replace characters outside the safe allow-list with spaces to avoid
        // polluting the LIKE pattern with wildcards or SQL metacharacters.
        $normalized = preg_replace('/[^\p{L}\p{N}\s\-_\'"@#.,()]/u', ' ', $withoutTags) ?? '';

        // Collapse duplicated whitespace and trim the final string to keep the
        // query compact and predictable for downstream comparisons.
        $squeezed = preg_replace('/\s+/u', ' ', $normalized) ?? '';

        $trimmed = trim($squeezed);

        if ($trimmed === '') {
            return '';
        }

        // Limit the payload to a sensible length to protect against extremely
        // large requests impacting query performance.
        return mb_substr($trimmed, 0, $maxLength);
    }

    /**
     * Escape wildcard characters and wrap the search term inside % tokens so it
     * can be reused with Eloquent LIKE constraints without risking accidental
     * pattern injection.
     */
    public static function toLikePattern(string $term): string
    {
        $escaped = addcslashes($term, '%_');

        // Replace whitespace with wildcard characters so multi-word queries can
        // match even when additional content appears between the supplied terms.
        $withWildcards = preg_replace('/\s+/u', '%', $escaped) ?? $escaped;

        $trimmed = trim($withWildcards, '%');

        return "%{$trimmed}%";
    }
}
