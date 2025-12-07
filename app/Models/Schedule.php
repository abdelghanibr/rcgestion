<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'complex_activity_id',
        'day_of_week',
        'heure_debut',
        'heure_fin'
    ];

    public function complexActivity()
    {
        return $this->belongsTo(ComplexActivity::class);
    }
}
