<?php

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    protected $table = 'product_translations';
    protected $guarded = [];
    public $timestamps = true;
}
