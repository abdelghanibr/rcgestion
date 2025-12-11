<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'complex_activity_id',
        'age_category_id',
        'groupe',
        'day_of_week',
        'heure_debut',
        'heure_fin',
        'nbr',
        'sex',
    ];

    // علاقة مع النشاط المدمج
    public function complexActivity()
    {
        return $this->belongsTo(ComplexActivity::class);
    }

    // علاقة مع الفئة العمرية
    public function ageCategory()
    {
        return $this->belongsTo(AgeCategory::class);
    }
}
