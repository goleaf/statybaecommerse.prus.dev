<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsWithDocuments
{
    /**
     * Get the documents relationship.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get variables available for document generation.
     *
     * @return array<string, mixed>
     */
    public function getDocumentVariables(): array
    {
        $variables = [];
        $attributes = $this->getAttributes();
        
        foreach ($attributes as $key => $value) {
            if (! is_null($value) && is_scalar($value)) {
                $variables['$' . strtoupper($key)] = $value;
            }
        }

        return $variables;
    }
}
