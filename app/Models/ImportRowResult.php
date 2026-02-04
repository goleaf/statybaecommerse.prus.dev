<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ImportRowResult extends Model
{
    protected $fillable = [
        'import_id',
        'row_number',
        'status',
        'action',
        'message',
        'error_message',
        'changed_fields',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'changed_fields' => 'array',
            'data'           => 'array',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}
