<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * CampaignTranslation
 * 
 * Eloquent model representing the CampaignTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignTranslation query()
 * @mixin \Eloquent
 */
final class CampaignTranslation extends Model
{
    use HasFactory;
    /**
     * Handle newFactory functionality with proper error handling.
     */
    protected static function newFactory()
    {
        return \Database\Factories\CampaignTranslationFactory::new();
    }
    protected $table = 'campaign_translations';
    protected $fillable = ['campaign_id', 'locale', 'name', 'slug', 'description', 'subject', 'content', 'cta_text', 'banner_alt_text', 'meta_title', 'meta_description'];
    protected $casts = ['campaign_id' => 'integer'];
    public $timestamps = true;
    /**
     * Handle campaign functionality with proper error handling.
     * @return BelongsTo
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Campaign::class);
    }
}