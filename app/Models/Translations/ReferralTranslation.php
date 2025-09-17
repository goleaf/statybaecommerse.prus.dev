<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * ReferralTranslation
 * 
 * Eloquent model representing the ReferralTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralTranslation query()
 * @mixin \Eloquent
 */
final class ReferralTranslation extends Model
{
    use HasFactory;
    protected $table = 'referral_translations';
    protected $fillable = ['referral_id', 'locale', 'title', 'description', 'terms_conditions', 'benefits_description', 'how_it_works', 'seo_title', 'seo_description', 'seo_keywords'];
    protected $casts = ['referral_id' => 'integer', 'seo_keywords' => 'array'];
    public $timestamps = true;
    /**
     * Handle referral functionality with proper error handling.
     * @return BelongsTo
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Referral::class);
    }
}