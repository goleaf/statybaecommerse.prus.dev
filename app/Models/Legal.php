<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Legal extends Model
{
    use HasTranslations;

    protected $table = 'legals';

    protected $guarded = [];

    protected string $translationModel = \App\Models\Translations\LegalTranslation::class;
}
