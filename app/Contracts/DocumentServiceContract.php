<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Document;
use App\Models\DocumentTemplate;
use Illuminate\Database\Eloquent\Model;

/**
 * @phpstan-type DocumentVariables array<string, mixed>
 */
interface DocumentServiceContract
{
    /**
     * @param DocumentVariables $variables
     */
    public function generateDocument(DocumentTemplate $template, Model $relatedModel, array $variables = [], ?string $title = null, bool $sendNotification = false): Document;

    public function generatePdf(Document $document): string;
}
