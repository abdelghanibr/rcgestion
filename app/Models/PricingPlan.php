<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    protected $fillable = [
        'activity_id',
        'age_category_id',
        'sexe',
        'type_client',
        'pricing_type',
        'duration_unit',
        'duration_value',
        'sessions_per_week',
        'price',
        'active',
        'valid_from',
        'valid_to'
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
