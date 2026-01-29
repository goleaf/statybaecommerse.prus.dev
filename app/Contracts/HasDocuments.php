<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasDocuments
{
    /**
     * Get the documents relationship.
     */
    public function documents(): MorphMany;

    /**
     * Get variables available for document generation.
     *
     * @return array<string, mixed>
     */
    public function getDocumentVariables(): array;
}
