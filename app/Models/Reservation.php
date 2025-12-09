<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    // الحقول التي يسمح بملؤها
    protected $fillable = [
        'user_id',
        'user_type',
        'season_id',
        'complex_activity_id',
        'start_date',
        'end_date',
        'time_slots',       // JSON يحتوي الأيام والساعات
        'duration_hours',   // مجموع الساعات في الموسم
        'total_price',      // السعر الكلي
        'status',  
        'pricing_plan_id'   , 'schedule_id' ,  'statut' ,'payment_status','qty_places' ,'end_date' ,'start_time' , 'end_time' ,// Pending / Confirmed / Rejected


        
    ];

    // لقراءة JSON تلقائيًا كمصفوفة
    protected $casts = [
        'time_slots' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /* 🔗 العلاقـات */

    // صاحب الحجز (قد يكون شخص / نادي / مؤسسة)
    public function user() {
        return $this->belongsTo(User::class);
    }

    // الموسم
    public function season() {
        return $this->belongsTo(Season::class);
    }

    // النشاط داخل المركب
    public function complexActivity() {
        return $this->belongsTo(ComplexActivity::class);
    }

    public function pricingPlan()
{
    return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
}

}
