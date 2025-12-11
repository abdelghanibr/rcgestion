<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complex extends Model
{
    protected $fillable = [
        'nom',
        'type',
        'adresse',
        'phone',
        'capacite_mi',
     'capacite_ma',
        
    ];

    /**
     * علاقة المركب مع الأنشطة عبر الجدول الوسيط complex_activity
     */
    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'complex_activity')
                    ->withPivot(['capacity', 'season_id'])
                    ->withTimestamps();
    }

    /**
     * علاقة المركب مع الجدول الوسيط ComplexActivity
     */
    public function complexActivities()
    {
        return $this->hasMany(ComplexActivity::class);
    }

    /**
     * علاقته مع الحجوزات عبر النشاط والساعات
     */
    public function reservations()
    {
        return $this->hasManyThrough(
            Reservation::class,
            ComplexActivity::class,
            'complex_id',           // complex_activity.complex_id
            'complex_activity_id',  // reservations.complex_activity_id
            'id',                   // complexes.id
            'id'                    // complex_activity.id
        );
    }
}
