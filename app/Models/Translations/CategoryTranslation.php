<?php

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    protected $table = 'category_translations';
    protected $guarded = [];
    public $timestamps = true;
}
