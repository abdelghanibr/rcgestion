<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


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

public function getAlertExpiredAttribute()
{
    return $this->payment_status === 'paid'
        && $this->date_fin
        && now()->gt(Carbon::parse($this->date_fin)->addDays(5));
}
public function getEtatLabelAttribute()
{
    if ($this->payment_status === 'paid' && $this->end_date) {
        $days = now()->diffInDays(Carbon::parse($this->end_date), false);

        if ($days <= 5 && $days >= 0) {
            return [
                'label' => "⏳ ينتهي خلال $days أيام",
                'class' => 'bg-warning text-dark'
            ];
        }

        return [
            'label' => 'مدفوع',
            'class' => 'bg-success'
        ];
    }

    if ($this->payment_status === 'pending') {
        return [
            'label' => 'قيد الانتظار',
            'class' => 'bg-secondary'
        ];
    }

    if ($this->payment_status === 'failed') {
        return [
            'label' => 'فشل الدفع',
            'class' => 'bg-danger'
        ];
    }

    return [
        'label' => 'غير معروف',
        'class' => 'bg-light text-dark'
    ];
}
    /* 🔗 العلاقـات */

    // صاحب الحجز (قد يكون شخص / نادي / مؤسسة)
    public function user() {
        return $this->belongsTo(User::class);
    }
public function person()
    {
        return $this->belongsTo(Person::class);
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
public function complex()
{
    return $this->belongsTo(Complex::class);
}

public function activity()
{
    return $this->belongsTo(Activity::class);
}

public function schedule()
{
    return $this->belongsTo(Schedule::class);
}
public function getDayName($dayNumber)
{
    return match ($dayNumber) {
        0 => 'الأحد',
        2 => 'الإثنين',
        3 => 'الثلاثاء',
        4 => 'الأربعاء',
        5 => 'الخميس',
        6 => 'الجمعة',
        7 => 'السبت',
      
        default => 'غير معروف',
    };
}
}
