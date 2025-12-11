<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Complex;


use App\Models\Person;
use App\Models\Dossier;
use App\Models\club;
use App\Models\Reservation;
use App\Models\ComplexActivity;
use App\Models\Schedule;
use App\Models\Season;
use Illuminate\Support\Facades\DB;
use App\Models\PricingPlan;

class ReservationController extends Controller
{
    // 1) اختيار نوع المركب
    public function selectType()
    {
        $types = Complex::select('type')->distinct()->get()->pluck('type');
        return view('reservation.select_type', compact('types'));
    }

    // 2) قائمة المركبات حسب النوع
    public function listByType($type)
    {
        $complexes = Complex::where('type', $type)->get();
        return view('reservation.list_complex', compact('complexes', 'type'));
    }
public function availability($complexActivityId)
{
    $capacity = ComplexActivity::findOrFail($complexActivityId)->capacite ? : 1;

    // جلب كل المواعيد المحددة لهذا المركب (schedule)
    $schedules = Schedule::where('complex_activity_id', $complexActivityId)->get();

    $calendarData = [];

    // أنشئ الأسبوع الحالي من اليوم
    $startOfWeek = now()->startOfWeek(); // الأحد
    $endOfWeek = now()->endOfWeek(); // السبت

    for ($day = $startOfWeek; $day <= $endOfWeek; $day->addDay()) {

        $date = $day->format('Y-m-d');

        foreach ($schedules as $s) {

            $reserved = Reservation::where('schedule_id', $s->id)
                ->where('start_date', $date)
                ->sum('qty_places');

            $percent = ($reserved / $capacity) * 100;

            if ($percent >= 100) {
                $color = "#d32f2f";
                $label = "ممتلئ";
            } elseif ($percent >= 50) {
                $color = "#ffa000";
                $label = "متاح بعدد قليل";
            } else {
                $color = "#4caf50";
                $label = "متاح";
            }

            // بناء الحدث
            $calendarData[] = [
                'date' => $date,
                'start' => $s->heure_debut,
                'end' => $s->heure_fin,
                'color' => $reserved > 0 ? $color : '#4caf50', 
                'label' => $reserved > 0 ? $label : 'متاح',
            ];
        }
    }

    return view('reservations.availability', compact('calendarData'));
}


    // 3) نموذج الحجز
  public function form($id)
{
    //dd($id);
    $complex = Complex::findOrFail($id);// id de complex 
    $user = Auth::user(); //user actuel
    $activity_id = session('activity_id');// id de l'activité sélectionnée
    $activity = \App\Models\Activity::find($activity_id);//tous les info de l'activité

    $complexActivity = ComplexActivity::where('activity_id', $activity_id) //id de complex_activity
                    ->where('complex_id', $id)
                    ->firstOrFail();

     $age = Person :: where( 'user_id' , $user->id) -> first();              

                   
$pricingPlans = PricingPlan::where('activity_id', $complexActivity->activity_id) // اختيار الخطط حسب النشاط
    ->where(function($q) use ($user) {

        // إذا كان المستخدم "شخص"
        if ($user->type == 'person') {

            $q->where('type_client', 'person')                 // نوع الزبون شخص
              ->where('age_category_id', optional($user->age)->age_category_id); // الفئة العمرية

        } else {

            // إذا كان نادي / مؤسسة
            $q->where('type_client', 'club');

        }
    })
   

    ->where('active', 1)//plan actif
    ->whereDate('valid_from', '<=', now())// date de validité
    //->orWhereNull('valid_to')
    ->get();



   // $schedules = Schedule::where('complex_activity_id',$complexActivity->id)->get();
 $schedules = Schedule::select(
                'id',
                'heure_debut',
                'heure_fin',
                'complex_activity_id',
                DB::raw("CASE day_of_week
                    WHEN 'Dim' THEN 0
                    WHEN 'Lun' THEN 1
                    WHEN 'Mar' THEN 2
                    WHEN 'Mer' THEN 3
                    WHEN 'Jeu' THEN 4
                    WHEN 'Ven' THEN 5
                    WHEN 'Sam' THEN 6
                END AS day_number")
            )
            ->where('complex_activity_id', $complexActivity->id)
            ->get();


//dd($schedules);

    $seasons   = Season::all();
    $dossier = Club::where('user_id', $user->id)->first();
    // تحقق من الدوسيي
    if ($user->type === 'company' || $user->type === 'club') {
      
        
        if (!$dossier) {
            return view('errors.error-dossier', [
                'message' => 'عذراً، لا يمكنك الحجز لأنه لا يوجد لديك ملف مُسجل. يرجى إنشاء ملف أولاً.'
            ]);
        }

        if ($dossier->etat !== 'approved') {
            return view('errors.error-dossier', [
                'message' => 'تم العثور على ملفك، ولكن لم تتم المصادقة عليه بعد. يرجى انتظار الموافقة من الإدارة قبل إجراء أي حجز.'
            ]);
        }

    } else {

        $person = \App\Models\Person::where('user_id', $user->id)->first();
        
        if ($person) {
            $dossier = \App\Models\Dossier::where('owner_type', 'person')
                                          ->where('person_id', $person->id)
                                          ->first();
        }

        if (!$dossier || $dossier->etat !== 'approved') {
            return view('errors.error-dossier', [
                'message' => '⚠️ ملفك غير مكتمل أو قيد الموافقة. يرجى إكماله أولاً.'
            ]);
        }
    }

    // 🔥 إضافة السعة والجدولة الديناميكية دون تغيير أي شيء آخر
    $capacity = $complexActivity->capacite ? : 50;

  //   $capacity = ComplexActivity::findOrFail($complexActivityId)->capacite ? : 1;
    $calendarData = [];

    $startOfWeek = now()->startOfWeek(); 
    $endOfWeek = now()->endOfWeek();
// 📌 حجوزات المستخدم نفسه لعرضها في التقويم باللون الأزرق
    $userReservations = Reservation::where('user_id', $user->id)
    ->where('complex_activity_id', $complexActivity->id)
   // ->whereBetween('start_date', [ $seasons ->date_debut, $seasons->date_fin])
    ->get() 
    
    ->map(function($r) {
        $events = [];

        // 👈 استخراج كل الخانات (الساعات) من JSON
        $timeSlots= json_decode($r->time_slots, true);

        if (!$timeSlots) return [];

        foreach ($timeSlots as $slot) {
            $events[] = [
                'title' => 'محجوز مسبقاً ✔',
                'start' => $slot['start'],
                'end'   => $slot['end'],
                'backgroundColor' => '#0d6efd', // 🔷 أزرق
                'borderColor' => '#084298',
                'display' => 'block',
                'editable' => false,
                'user_event' => true
            ];
        }

        return $events;
    })
    ->flatten(1);
//dd( $userReservations);
    /*for ($day = $startOfWeek; $day <= $endOfWeek; $day->addDay()) {

        $date = $day->format('Y-m-d');

       // foreach ($schedules as $s) {

            $reserved = Reservation::where('start_date', $date)
               // ->where('start_date', $date)
                ->sum('qty_places');

            $percent = ($reserved / $capacity) * 100;
  
            if ($percent >= 100) {
                $color = "#d32f2f";
                $label = "ممتلئ";
            } elseif ($percent >= 50) {
              //  dd($percent );
                $color = "#ffa000";
                $label = "متاح بعدد قليل";
            } else {
                $color = "#4caf50";
                $label = "متاح";
               // dd($percent );
            }

            $calendarData[] = [
                //'schedule_id' => $s->id,
               // 'day_of_week' => $s->day_of_week,
                'date' => $date,
               // 'start' => $s->heure_debut,
                //'end' => $s->heure_fin,
               // 'color' => $color,
                'label' => $label,
            ];
//dd( $calendarData);
           
      //  }
    }*/
 
    // 🔥 تمرير البيانات الجديدة للواجهة دون حذف ما كان موجوداً
  return view('reservation.form', compact(
    'complex',
    'complexActivity',
    'pricingPlans',
    'seasons',
    'activity',
    'schedules' ,'userReservations'
));

}



    // 4) تنفيذ الحجز
public function store(Request $request)
{
    $request->validate([
        'complex_activity_id' => 'required|exists:complex_activity,id',
        'season_id' => 'required|exists:seasons,id',
        'selected_slots' => 'required',
    ],[
        'complex_activity_id.required' => '⚠ يرجى اختيار مركب ونشاط صحيح.',
        'season_id.required' => '⚠ يرجى اختيار الموسم الرياضي.',
        'selected_slots.required' => '⚠ يرجى اختيار يوم ووقت واحد على الأقل.',
    ]);

    $user = Auth::user();
    $slots = json_decode($request->selected_slots, true);

    if (!$slots || !is_array($slots) || count($slots) == 0) {
        return back()->with('error', '⚠ يرجى اختيار يوم ووقت واحد على الأقل.');
    }

    // ❗ استخراج أصغر وأكبر تاريخ من التحديد
    $dates = array_column($slots, 'date');
    $start_date = min($dates);
   // $end_date   = max($dates);
   $season = Season::findOrFail($request->season_id);
   $end_date = $season->date_fin;
    // ⚠ التحقق من عدم وجود تضارب مع مستخدم آخر
    $conflict = Reservation::where('complex_activity_id', $request->complex_activity_id)
        ->where(function ($q) use ($start_date, $end_date) {
            $q->whereBetween('start_date', [$start_date, $end_date])
              ->orWhereBetween('end_date', [$start_date, $end_date]);
        })
        ->exists();

    if ($conflict) {
        return back()->with('error', '⚠ أحد الأوقات المختارة محجوز مسبقاً! 🚫');
    }

    // حساب الساعات المختارة
    $duration_hours = count($slots);

    Reservation::create([
        'user_id' => $user->id,
       // 'user_type' => $user->type_client,
        'complex_activity_id' => $request->complex_activity_id,
        'season_id' => $request->season_id,

        'start_date' => $start_date,
        'end_date' => $end_date,

        'time_slots' => json_encode($slots),
        'duration_hours' => $duration_hours,

        'qty_places' => 1,
        'total_price' => 0,

        'statut' => 'en_attente',
        'payment_status' => 'pending'
    ]);

    return match ($user->type) {
        'admin' => redirect()->route('admin.dashboard'),
        'club'  => redirect()->route('club.dashboard'),
        'company' => redirect()->route('entreprise.dashboard'),
        default => redirect()->route('person.dashboard'),
    }; with('success', '✔ تم تسجيل الحجز بنجاح وسيتم مراجعته من الإدارة.');
}


    // 8) حجوزات المستخدم
    public function myReservations()
    {
        $reservations = Reservation::where('user_id', auth()->id())->get();
        return view('reservation.my_reservations', compact('reservations'));
    }
}
