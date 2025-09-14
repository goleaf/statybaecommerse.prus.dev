<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
/**
 * EmailCampaign
 * 
 * Eloquent model representing the EmailCampaign entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCampaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCampaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCampaign query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
class EmailCampaign extends Model
{
    //
}