<?php

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class AttributeValueTranslation extends Model
{
    protected $table = 'attribute_value_translations';

    protected $guarded = [];

    public $timestamps = true;
}
