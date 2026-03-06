<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers\Concerns;

trait ResolvesOwnerPageRedirect
{
    /**
     * @param class-string $ownerResourceClass
     */
    protected function resolveOwnerPageRedirectUrl(
        string $ownerResourceClass,
        string $ownerPage = 'view',
        ?string $relationManagerClass = null
    ): string {
        foreach ([request()->fullUrl(), request()->headers->get('referer')] as $candidateUrl) {
            if ($this->isSafeOwnerPageRedirectCandidate($candidateUrl)) {
                return (string) $candidateUrl;
            }
        }

        return $this->buildOwnerRelationUrl(
            ownerResourceClass: $ownerResourceClass,
            ownerPage: $ownerPage,
            relationManagerClass: $relationManagerClass,
        );
    }

    protected function isSafeOwnerPageRedirectCandidate(mixed $candidateUrl): bool
    {
        if (! is_string($candidateUrl) || trim($candidateUrl) === '') {
            return false;
        }

        $requestHost = request()->getHost();
        $candidateHost = parse_url($candidateUrl, PHP_URL_HOST);

        if (is_string($candidateHost) && $candidateHost !== '' && strcasecmp($candidateHost, $requestHost) !== 0) {
            return false;
        }

        $path = parse_url($candidateUrl, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return false;
        }

        return ! str_ends_with(strtolower($path), '/livewire/update');
    }

    /**
     * @param class-string $ownerResourceClass
     */
    protected function buildOwnerRelationUrl(
        string $ownerResourceClass,
        string $ownerPage = 'view',
        ?string $relationManagerClass = null
    ): string {
        $parameters = [
            'record' => $this->getOwnerRecord(),
        ];

        $relationTabKey = $this->resolveRelationTabKey(
            ownerResourceClass: $ownerResourceClass,
            relationManagerClass: $relationManagerClass,
        );

        if ($relationTabKey !== null) {
            $parameters['relation'] = $relationTabKey;
        }

        return $ownerResourceClass::getUrl($ownerPage, $parameters);
    }

    /**
     * @param class-string $ownerResourceClass
     */
    protected function resolveRelationTabKey(string $ownerResourceClass, ?string $relationManagerClass = null): ?string
    {
        $relationManagerClass ??= static::class;

        $relationKey = array_search($relationManagerClass, $ownerResourceClass::getRelations(), true);

        if ($relationKey === false) {
            return null;
        }

        return (string) $relationKey;
    }
}
