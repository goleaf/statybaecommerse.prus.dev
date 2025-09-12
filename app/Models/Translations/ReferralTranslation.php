<?php declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReferralTranslation extends Model
{
    protected $table = 'referral_translations';
    
    protected $fillable = [
        'referral_id',
        'locale',
        'title',
        'description',
        'terms_conditions',
        'benefits_description',
        'how_it_works',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'referral_id' => 'integer',
        'seo_keywords' => 'array',
    ];

    public $timestamps = true;

    public function referral(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Referral::class);
    }
}



