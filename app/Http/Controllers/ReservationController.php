<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\Complex;
use App\Models\Reservation;
use App\Models\Activity;
use App\Models\Season;

use App\Models\Person;
use App\Models\Dossier;
use App\Models\Club;

use App\Models\ComplexActivity;
use App\Models\Schedule;

use App\Models\PricingPlan;

class ReservationController extends Controller
{
    private const DAY_LABELS = [
        'الأحد',
        'الاثنين',
        'الثلاثاء',
        'الأربعاء',
        'الخميس',
        'الجمعة',
        'السبت',
    ];


public function index()
{
    $user = auth()->user();

    $query = Reservation::with([
        'complexActivity.complex',
        'complexActivity.activity',
        'season'
    ]);


    

    if ($user->type !='admin') {
        // 👤 User normal
        $query->where('user_id', $user->id);

        $reservations = $query->get();

        $activities = Activity::orderBy('title')->get();
        $seasons    = Season::orderBy('name')->get();
         


        return view('reservation.my_reservations', compact(
            'reservations',
            'activities',
            'seasons' 
           
        ));
    }

    // 🛡️ Admin
    $reservations = $query->get();
 $complexes    = complex::orderBy('nom')->get();
    $activities = Activity::orderBy('title')->get();
    $seasons    = Season::orderBy('name')->get();

    return view('admin.reservations.index', compact(
        'reservations',
        'activities',
        'seasons',
         'complexes'
    ));
}

     public function create()
    {
        return view('reservations.create');
    }
public function print(Reservation $reservation)
{
    $reservation->load([
        'user',
        'complexActivity.activity'
    ]);

    return view('reservation.print', compact('reservation'));
}


    /**
     * تجديد حجز (نفس النشاط و الخطة)
     */
    public function renew($id)
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('reservations.renew', compact('reservation'));
    }



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
    $complex = Complex::findOrFail($id);
    $user = Auth::user();
    $activity_id = session('activity_id');

    if (!$activity_id) {
        return redirect()->route('reservation.select_type')
            ->with('error', '⚠ يرجى اختيار النشاط قبل المتابعة.');
    }

    $activity = Activity::findOrFail($activity_id);// جلب النشاط المحدد

    $complexActivity = ComplexActivity::where('activity_id', $activity_id)// جلب المركب والنشاط المحدد
                    ->where('complex_id', $id)
                    ->firstOrFail();
 
    $person = $user->type === 'person'
        ? Person::where('user_id', $user->id)->with('ageCategory')->first()
        : null;

    $ageCategoryId = $person?->age_category_id;// جلب فئة العمر
    $genderCode = $this->normalizeGender($person?->gender);// تطبيع قيمة الجنس

    $seasons = Season::all();
    $selectedSeasonId = request()->input('season_id');
    $schedules = collect();
    $pricingPlans = collect();

    // تحميل الجداول فقط إذا تم اختيار موسم
    if ($selectedSeasonId) {
        $pricingPlans = $this->eligiblePricingPlans(
            $complexActivity->activity_id,
            $user,
            $ageCategoryId,
            $genderCode
        );// جلب خطط التسعير المؤهلة

        $scheduleQuery = Schedule::with('ageCategory')
            ->where('complex_activity_id', $complexActivity->id);// جلب الجداول الزمنية للمركب والنشاط المحدد

        // تصفية الجداول حسب نوع المستخدم
        if ($user->type === 'person') {
            // للأشخاص: عرض الجداول العامة فقط (غير مخصصة لمستخدم معين)
            $scheduleQuery->whereNull('user_id');
            
        // عرض الجداول التي تطابق فئة العمر أو الجداول المتاحة لجميع الأعمار
        if ($ageCategoryId) {// التحقق من فئة العمر
            $scheduleQuery->where(function ($query) use ($ageCategoryId) {// شرط للتحقق من فئة العمر
                $query->whereNull('age_category_id')// الجداول المتاحة لجميع الأعمار
                      ->orWhere('age_category_id', $ageCategoryId);// أو الجداول الخاصة بفئة عمر المستخدم
            });
        } else {
            // إذا لم تكن هناك فئة عمر للمستخدم، عرض الجداول المتاحة لجميع الأعمار فقط
            $scheduleQuery->whereNull('age_category_id');
        }

        // عرض الجداول التي تطابق الجنس أو الجداول المتاحة لجميع الجنسين
        if ($genderCode) {// التحقق من الجنس
            $scheduleQuery->where(function ($query) use ($genderCode) {// شرط للتحقق من الجنس
                $query->whereNull('sex')// الجداول المتاحة لجميع الجنسين
                      ->orWhere('sex', 'X')// الجداول المختلطة
                      ->orWhere('sex', $genderCode);// أو الجداول الخاصة بجنس المستخدم
            });
        } else {
            // إذا لم يكن هناك جنس محدد، عرض الجداول المتاحة لجميع الجنسين فقط
            $scheduleQuery->where(function ($query) {
                $query->whereNull('sex')->orWhere('sex', 'X');
            });
        }
    } elseif (in_array($user->type, ['club', 'company'])) {
        // للأندية والشركات: عرض الجداول المخصصة لهم أو الجداول ذات السعر الثابت
        $scheduleQuery->where(function ($query) use ($user) {
            $query->where('user_id', $user->id) // الجداول المخصصة للمستخدم
                  ->orWhere(function ($q) {
                      $q->where('type_prix', 'fix') // أو الجداول بسعر ثابت
                        ->whereNull('user_id') // غير مخصصة لمستخدم آخر
                        ->whereNull('age_category_id') // متاحة لجميع الأعمار
                        ->where(function ($sq) {
                            $sq->whereNull('sex')->orWhere('sex', 'X'); // متاحة لجميع الجنسين
                        });
                  });
        });
    }
        
        $schedules = $scheduleQuery
            ->get()
            ->map(function ($schedule) use ($pricingPlans) {
                return $this->decorateScheduleForDisplay($schedule, $pricingPlans);
            });// تزيين الجداول الزمنية للعرض


        if ($schedules->isNotEmpty()) {// التحقق من وجود جداول زمنية
            $reservedCounts = Reservation::whereIn('schedule_id', $schedules->pluck('id'))// جلب الحجوزات للجداول الزمنية المحددة
                ->selectRaw('schedule_id, SUM(qty_places) as reserved')// جلب عدد الأماكن المحجوزة
                ->groupBy('schedule_id')// تجميع النتائج حسب معرف الجدول الزمني
                ->pluck('reserved', 'schedule_id');// جلب عدد الأماكن المحجوزة لكل جدول زمني

            $schedules = $schedules->map(function ($schedule) use ($reservedCounts) {// تحديث الجداول الزمنية بعدد الأماكن المحجوزة والمتاحة
                $reserved = (int) ($reservedCounts[$schedule->id] ?? 0);// جلب عدد الأماكن المحجوزة للجدول الزمني الحالي
                $schedule->reserved_places = $reserved;// تعيين عدد الأماكن المحجوزة
                $schedule->available_places = $schedule->nbr ? max(0, $schedule->nbr - $reserved) : null;
                return $schedule;// تعيين عدد الأماكن المتاحة
            });
        }
    }
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

    return view('reservation.form', compact(
        'complex',
        'complexActivity',
        'seasons',
        'activity',
        'schedules',
        'selectedSeasonId'
    ));

}

public function renewStore(Request $request, Reservation $reservation)
{
    $request->validate([
        'start_date' => 'required|date|after_or_equal:today',
        'end_date'   => 'required|date|after:start_date',
    ]);

    // حساب عدد الأيام
    $days = \Carbon\Carbon::parse($request->start_date)
        ->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;// إضافة يوم للبداية

    // حساب السعر (مثال بسيط)
    $pricePerDay = $reservation->total_price /
                   max(1, $reservation->duration_hours);// تجنب القسمة على صفر

    $newPrice = $days*$pricePerDay;

    // إنشاء حجز جديد (clone)
    $newReservation = $reservation->replicate([
        'status',
        'payment_status'
    ]);

    $newReservation->start_date = $request->start_date;
    $newReservation->end_date   = $request->end_date;
    $newReservation->total_price = round($newPrice);
    $newReservation->status = 'pending';
    $newReservation->payment_status =
        $request->pay_now ? 'paid' : 'unpaid';

    $newReservation->save();

    return redirect()
        ->route('reservations.index')
        ->with('success', '✅ تم تجديد الحجز بنجاح');
}


    // 4) تنفيذ الحجز
public function store(Request $request)
{
    $request->validate([
        'complex_activity_id' => 'required|exists:complex_activity,id',
        'season_id' => 'required|exists:seasons,id',
        'schedule_id' => 'required|exists:schedules,id',
    ], [
        'complex_activity_id.required' => '⚠ يرجى اختيار مركب ونشاط صحيح.',
        'season_id.required' => '⚠ يرجى اختيار الموسم الرياضي.',
        'schedule_id.required' => '⚠ يرجى اختيار جدول زمني للانضمام إليه.',
    ]);

    $user = Auth::user();

    $complexActivity = ComplexActivity::findOrFail($request->complex_activity_id);
    $schedule = Schedule::findOrFail($request->schedule_id);

    if ( (int)$schedule->complex_activity_id !== (int)$complexActivity->id) {
        return back()->with('error', '⚠ الجدول المختار لا ينتمي إلى هذا النشاط.');
    }

    $season = Season::findOrFail($request->season_id);

    $person = $user->type === 'person'
        ? Person::where('user_id', $user->id)->first()
        : null;

    $ageCategoryId = $person?->age_category_id;
    $genderCode = $this->normalizeGender($person?->gender);

    if (!$this->scheduleMatchesUserProfile($schedule, $ageCategoryId, $genderCode)) {
        return back()->with('error', '⚠ هذا الجدول غير متاح لبياناتك الشخصية.');
    }

    $slots = $this->decodeScheduleSlots($schedule);
    $sessionsPerWeek = count($slots);

    // التحقق من عدم وجود تعارض في المواعيد مع حجوزات المستخدم الحالية
    $conflict = $this->checkScheduleConflict($user->id, $schedule, $season);
    if ($conflict) {
        return back()->with('error', '⚠ يوجد تعارض في المواعيد مع حجز آخر لديك: ' . $conflict);
    }

    if ($schedule->nbr) {
        $reservedPlaces = Reservation::where('schedule_id', $schedule->id)->sum('qty_places');
        if ($reservedPlaces >= $schedule->nbr) {
            return back()->with('error', '⚠ هذا الجدول ممتلئ حالياً.');
        }
    }

    $pricingPlans = $this->eligiblePricingPlans(// جلب خطط التسعير المؤهلة
        $complexActivity->activity_id,
        $user,
        $ageCategoryId,
        $genderCode
    );

    $appliedPlan = null;
    $totalPrice = null;

    if ($schedule->type_prix === 'pricing_plan') {// إذا كانت خطة التسعير
        $appliedPlan = $this->matchPlanForSchedule($schedule, $pricingPlans, $sessionsPerWeek);// مطابقة خطة التسعير للجدول

        if (!$appliedPlan) {// إذا لم تكن هناك خطة مطابقة
            return back()->with('error', '⚠ لا توجد خطة تسعير مطابقة لهذا الجدول.');
        }

        $totalPrice = $this->calculateSchedulePrice($schedule, $season, $appliedPlan, $sessionsPerWeek);// حساب سعر الجدول
    } else {
        if (!$schedule->price) {// إذا لم يكن هناك سعر محدد
            return back()->with('error', '⚠ لا يوجد سعر محدد لهذا الجدول.');
        }

        $totalPrice = $this->calculateFixedSchedulePrice($schedule, $season);// حساب السعر الثابت للجدول
    }

    Reservation::create([
        'user_id' => $user->id,
        'complex_activity_id' => $complexActivity->id,
        'season_id' => $request->season_id,
        'schedule_id' => $schedule->id,
        'pricing_plan_id' => $appliedPlan?->id,
        'start_date' => $season->date_debut,
        'end_date' => $season->date_fin,
        'time_slots' => $slots,
        'duration_hours' => $sessionsPerWeek,
        'qty_places' => 1,
        'total_price' => $totalPrice,
        'statut' => 'en_attente',
        'payment_status' => 'pending'
    ]);

    $redirect = match ($user->type) {
        'admin' => redirect()->route('reservation.my-reservations') ,
        'club'  => redirect()->route('reservation.my-reservations') ,
        'company' => redirect()->route('reservation.my-reservations') ,
        default => redirect()->route('reservation.my-reservations')    //route('person.dashboard'),//route('reservation.my-reservations') 
    };

    return $redirect->with('success', '✔ تم تسجيل الحجز بنجاح وسيتم مراجعته من الإدارة.');
}
 public function pay(Reservation $reservation)
    {
        // 🔐 تأكد أن الحجز يخص المستخدم الحالي
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بالدفع لهذا الحجز');
        }

        // ✅ إذا كان مدفوعًا بالفعل
        if ($reservation->payment_status === 'paid') {
            return back()->with('info', 'ℹ️ هذا الحجز مدفوع بالفعل');
        }

        // 🟡 pending أو 🔴 failed → نسمح بالدفع
        return view('payments.pay', [
            'reservation' => $reservation
        ]);
    }

    // 8) حجوزات المستخدم
    public function myReservations()
    {
        $reservations = Reservation::where('user_id', auth()->id())->get();// جلب حجوزات المستخدم الحالي
        return view('reservation.my_reservations', compact('reservations'));// عرض صفحة الحجوزات مع تمرير الحجوزات
    }

    private function eligiblePricingPlans($activityId, $user, ?int $ageCategoryId = null, ?string $gender = null)// جلب خطط التسعير المؤهلة بناءً على معايير المستخدم
    {
        $typeClient = match ($user->type) {// تحديد نوع العميل بناءً على نوع المستخدم
            'club' => 'club',// إذا كان المستخدم نادي
            'company' => 'company',// إذا كان المستخدم شركة
            default => 'person',// إذا كان المستخدم فرد
        };

        return PricingPlan::where('activity_id', $activityId) // جلب خطط التسعير للنشاط المحدد
            ->where('active', 1)// التحقق من أن خطة التسعير نشطة
            ->where('type_client', $typeClient)// التحقق من نوع العميل
            ->whereDate('valid_from', '<=', now())// التحقق من تاريخ صلاحية خطة التسعير
            ->where(function ($query) {// شرط للتحقق من تاريخ انتهاء صلاحية خطة التسعير
                $query->whereNull('valid_to')// إذا لم يكن هناك تاريخ انتهاء
                      ->orWhereDate('valid_to', '>=', now());// التحقق من صلاحية خطة التسعير
            }) 
            ->when($ageCategoryId, function ($query) use ($ageCategoryId) {// التحقق من فئة العمر
                $query->where(function ($q) use ($ageCategoryId) {// شرط للتحقق من فئة العمر
                    $q->whereNull('age_category_id') // إذا لم تكن هناك فئة عمر محددة  
                      ->orWhere('age_category_id', $ageCategoryId);// التحقق من فئة العمر
                });// التحقق من فئة العمر
            })
            ->when($gender, function ($query) use ($gender) {// التحقق من الجنس
                $query->where(function ($q) use ($gender) {// شرط للتحقق من الجنس
                    $q->whereNull('sexe')// إذا لم يكن هناك جنس محدد
                      ->orWhere('sexe', $gender);// التحقق من الجنس
                });// التحقق من الجنس
            })
            ->get();// جلب خطط التسعير المؤهلة بناءً على المعايير المحددة
  //  dd()
    
        }

    private function normalizeGender(?string $value): ?string// تطبيع قيمة الجنس
    {
        if (!$value) {// إذا لم تكن هناك قيمة
            return null;// إرجاع null
        }

        $value = strtolower(trim($value));// تحويل القيمة إلى حروف صغيرة وإزالة الفراغات

        return match (true) {// استخدام تعبير match للتحقق من القيم المختلفة
            in_array($value, ['h', 'homme', 'male', 'm']) => 'H',// إذا كانت القيمة تشير إلى ذكر
            in_array($value, ['f', 'femme', 'female']) => 'F',// إذا كانت القيمة تشير إلى أنثى
            default => null,// إذا لم تتطابق مع أي من القيم المعروفة، إرجاع null
        };
    }

    private function scheduleMatchesUserProfile(Schedule $schedule, ?int $ageCategoryId, ?string $gender): bool // التحقق من تطابق الجدول مع ملف المستخدم
    {
        if ($ageCategoryId && $schedule->age_category_id && (int) $schedule->age_category_id !== (int) $ageCategoryId) {// التحقق من فئة العمر
            return false;// إذا لم تتطابق، إرجاع false
        }

        if ($gender && $schedule->sex && $schedule->sex !== 'X' && strtoupper($schedule->sex) !== $gender) {// التحقق من الجنس
            return false;// إذا لم يتطابق، إرجاع false
        }

        return true;// إذا تطابقت كل الشروط، إرجاع true
    }

    private function decodeScheduleSlots(Schedule $schedule): array// فك تشفير الفتحات الزمنية للجدول
    {
        $slots = $schedule->time_slots;// الحصول على الفتحات الزمنية من الجدول

        if (is_string($slots)) {// إذا كانت الفتحات الزمنية عبارة عن سلسلة نصية
            $decoded = json_decode($slots, true);// فك تشفير السلسلة النصية إلى مصفوفة
            if (json_last_error() === JSON_ERROR_NONE) {// التحقق من عدم وجود أخطاء في فك التشفير
                return $decoded ?: [];// إرجاع المصفوفة المفككة أو مصفوفة فارغة إذا كانت null
            }
            return [];// إرجاع مصفوفة فارغة في حالة وجود خطأ في فك التشفير
        }

        return $slots ?: [];//  إرجاع الفتحات الزمنية كما هي أو مصفوفة فارغة إذا كانت null
    }

    private function decorateScheduleForDisplay(Schedule $schedule, Collection $pricingPlans): Schedule // تزيين الجدول للعرض
    {
        $slots = $this->decodeScheduleSlots($schedule);// فك تشفير الفتحات الزمنية للجدول
        $sessionsCount = count($slots);// حساب عدد الجلسات في الأسبوع

        $schedule->sessions_count = $sessionsCount;// تعيين عدد الجلسات في الجدول
        $schedule->formatted_slots = collect($slots)->map(function ($slot) {// تنسيق الفتحات الزمنية للعرض
            $dayIndex = $slot['day_number'] ?? null;// الحصول على رقم اليوم
            $dayLabel = $dayIndex !== null ? (self::DAY_LABELS[$dayIndex] ?? 'يوم غير معروف') : 'يوم غير معروف';// تعيين تسمية اليوم

            $start = $slot['start'] ?? null;// الحصول على وقت البدء
            $end   = $slot['end'] ?? null;// الحصول على وقت الانتهاء

            return trim($dayLabel . ' | ' . ($start ?? '?') . ' → ' . ($end ?? '?'));// 
        })->toArray();// تحويل المجموعة إلى مصفوفة

        if ($schedule->type_prix === 'pricing_plan') {// إذا كانت خطة التسعير
            $matchedPlan = $this->matchPlanForSchedule($schedule, $pricingPlans, $sessionsCount);// مطابقة خطة التسعير للجدول
            $schedule->applied_plan = $matchedPlan;// تعيين خطة التسعير المطبقة
            $schedule->calculated_price = $matchedPlan?->price;// تعيين السعر المحسوب بناءً على خطة التسعير
            $schedule->pricing_note = $matchedPlan ? $this->pricingUnitLabel($matchedPlan) : null;// تعيين ملاحظة التسعير بناءً على خطة التسعير
        } else {
            $schedule->applied_plan = null;// لا توجد خطة تسعير مطبقة
            $schedule->calculated_price = $schedule->price;// تعيين السعر الثابت للجدول
            $schedule->pricing_note = 'سعر ثابت';// 
        }

        return $schedule;
    }

    private function matchPlanForSchedule(Schedule $schedule , Collection $pricingPlans, int $sessionsPerWeek)// مطابقة خطة التسعير للجدول
    {
        return $pricingPlans->first(function ($plan) use ($schedule, $sessionsPerWeek) {// البحث عن أول خطة مطابقة
            if ((int) $plan->sessions_per_week !== $sessionsPerWeek) {// التحقق من عدد الحصص في الأسبوع
                return false;// إذا لم يتطابق، إرجاع false
            }

            if ($schedule->age_category_id && $plan->age_category_id && (int) $plan->age_category_id !== (int) $schedule->age_category_id) {// التحقق من فئة العمر
                return false;// إذا لم تتطابق، إرجاع false
            }

            $scheduleSex = $schedule->sex ? strtoupper($schedule->sex) : 'X';// الحصول على جنس الجدول
            if ($plan->sexe && $scheduleSex !== 'X' && strtoupper($plan->sexe) !== $scheduleSex) {// التحقق من الجنس
                return false;// إذا لم يتطابق، إرجاع false
            }

            return true;// إذا تطابقت كل الشروط، إرجاع true
        });
    }

    private function pricingUnitLabel(?PricingPlan $plan): ?string// تسمية وحدة التسعير
    {
        if (!$plan) {// إذا لم تكن هناك خطة
            return null;
        }

        $unit = strtolower($plan->duration_unit ?? $plan->pricing_type ?? '');// وحدة التسعير

        return match ($unit) {// تحديد التسمية بناءً على وحدة التسعير
            'monthly', 'month' => 'السعر لكل شهر',// السعر لكل شهر
            'weekly', 'week' => 'السعر لكل أسبوع',// السعر لكل أسبوع
            'session' => 'السعر لكل حصة',// السعر لكل حصة
            'ticket' => 'سعر التذكرة',// سعر التذكرة
            default => 'سعر الخطة',//   
        };
    }

    private function calculateSchedulePrice(Schedule $schedule, Season $season, PricingPlan $plan, int $sessionsPerWeek): float// حساب سعر الجدول
    {
        if ($schedule->type_prix !== 'pricing_plan') {// إذا لم تكن خطة التسعير
            return $this->calculateFixedSchedulePrice($schedule, $season);// حساب السعر الثابت للجدول
        }

        return $this->calculatePlanPrice($plan, $season, $sessionsPerWeek);// حساب سعر الخطة بناءً على الموسم وعدد الحصص في الأسبوع
    }

    private function calculateFixedSchedulePrice(Schedule $schedule, Season $season): float// حساب السعر الثابت للجدول
    {
        $basePrice = (float) $schedule->price;// السعر الأساسي للجدول
        $start = Carbon::parse($season->date_debut);// تاريخ بداية الموسم
        $end = Carbon::parse($season->date_fin);// تاريخ نهاية الموسم
        $today = Carbon::today();// تاريخ اليوم

        // إذا بدأ الاشتراك في منتصف الشهر، احسب السعر التناسبي
        $proratedFirstMonth = $this->calculateProratedFirstMonth($today, $basePrice);
        
        $monthsBetween = $start->diffInMonths($end);// حساب عدد الشهور بين البداية والنهاية

        if ($end->day >= $start->day || $monthsBetween === 0) {// إذا كان يوم النهاية أكبر أو يساوي يوم البداية
            $monthsBetween += 1;// زيادة عدد الشهور بمقدار 1
        }

        $months = max(1, $monthsBetween);// التأكد من أن عدد الشهور لا يقل عن 1
        $monthsCharged = min(12, $months);// تحديد عدد الشهور التي سيتم احتسابها (بحد أقصى 12)

        // إذا كان هناك سعر تناسبي للشهر الأول، اطرح شهر كامل واضف السعر التناسبي
        if ($proratedFirstMonth < $basePrice && $monthsCharged > 0) {
            return ($basePrice * ($monthsCharged - 1)) + $proratedFirstMonth;
        }

        return $basePrice * $monthsCharged;// حساب السعر النهائي بضرب السعر الأساسي في عدد الشهور المحتسبة
    }

    private function calculatePlanPrice(PricingPlan $plan, Season $season, int $sessionsPerWeek): float // حساب سعر الخطة
    {
        $start = Carbon::parse($season->date_debut);    // تاريخ بداية الموسم
        $end = Carbon::parse($season->date_fin);// تاريخ نهاية الموسم
        $today = Carbon::today();// تاريخ اليوم
        $unit = strtolower($plan->duration_unit ?? $plan->pricing_type ?? 'season');// وحدة التسعير
        $durationValue = max(1, (int) ($plan->duration_value ?? 1));// قيمة المدة
        $basePrice = (float) $plan->price;// السعر الأساسي للخطة

        $days = $start->diffInDays($end) + 1;// عدد الأيام في الموسم
        $weeks = max(1, (int) ceil($days / 7));// حساب الأسابيع
        $months = max(1, (int) ceil($days / 30));// حساب الشهور

        // حساب السعر التناسبي للشهر الأول إذا بدأ الاشتراك في منتصف الشهر
        $proratedFirstMonth = $this->calculateProratedFirstMonth($today, $basePrice);
        $hasProratedMonth = $proratedFirstMonth < $basePrice;

        $totalPrice = match ($unit) { // تحديد السعر بناءً على وحدة التسعير
            'month', 'monthly' => $basePrice, // ceil($months / $durationValue) * $basePrice,// السعر مضروب في عدد الشهور مقسوم على قيمة المدة
            'week', 'weekly' => ceil($weeks / $durationValue) * $basePrice,// السعر مضروب في عدد الأسابيع مقسوم على قيمة المدة
            'session' => $weeks * min(1, $plan->sessions_per_week ?? $sessionsPerWeek) * $basePrice,// السعر مضروب في عدد الأسابيع وعدد الحصص في الأسبوع
            'ticket' => $basePrice,// سعر التذكرة ثابت
            default => $basePrice,// السعر الأساسي للخطة
        };

        // إذا كان النوع شهري وبدأ الاشتراك في منتصف الشهر، استخدم السعر التناسبي
        if (in_array($unit, ['month', 'monthly']) && $hasProratedMonth) {
            return $proratedFirstMonth;
        }

        return $totalPrice;
    }

    /**
     * حساب السعر التناسبي للشهر الأول إذا بدأ الاشتراك بعد اليوم الأول من الشهر
     * Calculate prorated price if subscription starts mid-month
     */
    private function calculateProratedFirstMonth(Carbon $startDate, float $monthlyPrice): float
    {
        $dayOfMonth = $startDate->day;// اليوم من الشهر (1-31)
        
        // إذا بدأ الاشتراك في اليوم الأول، لا حاجة للتقسيم التناسبي
        if ($dayOfMonth === 1) {
            return $monthlyPrice;
        }

        $daysInMonth = $startDate->daysInMonth;// عدد الأيام في الشهر الحالي
        $remainingDays = $daysInMonth - $dayOfMonth + 1;// الأيام المتبقية في الشهر (بما فيها يوم البداية)
        
        // حساب السعر التناسبي بناءً على الأيام المتبقية
        $proratedPrice = ($monthlyPrice / $daysInMonth) * $remainingDays;
        
        return round($proratedPrice, 2);// تقريب السعر لرقمين عشريين
    }

    /**
     * التحقق من وجود تعارض في المواعيد مع حجوزات المستخدم الحالية
     * Check if new schedule conflicts with user's existing reservations
     * 
     * @param int $userId
     * @param Schedule $newSchedule
     * @param Season $newSeason
     * @return string|null رسالة التعارض أو null إذا لم يكن هناك تعارض
     */
    private function checkScheduleConflict(int $userId, Schedule $newSchedule, Season $newSeason): ?string
    {
        // جلب جميع الحجوزات النشطة للمستخدم
        $existingReservations = Reservation::where('user_id', $userId)
            ->whereIn('statut', ['en_attente', 'validé', 'confirmé'])
            ->with('schedule')
            ->get();

        // فك تشفير الفترات الزمنية للجدول الجديد
        $newSlots = $this->decodeScheduleSlots($newSchedule);
        
        // تحويل تاريخ بداية ونهاية الموسم الجديد
        $newStart = Carbon::parse($newSeason->date_debut);
        $newEnd = Carbon::parse($newSeason->date_fin);

        foreach ($existingReservations as $reservation) {
            // التحقق من تداخل الفترات الزمنية للموسم
            $existingStart = Carbon::parse($reservation->start_date);
            $existingEnd = Carbon::parse($reservation->end_date);

            // إذا لم يكن هناك تداخل في التواريخ، تخطي هذا الحجز
            if ($newEnd->lt($existingStart) || $newStart->gt($existingEnd)) {
                continue;
            }

            // فك تشفير الفترات الزمنية للحجز الموجود
            $existingSlots = is_array($reservation->time_slots) 
                ? $reservation->time_slots 
                : json_decode($reservation->time_slots, true) ?? [];

            // التحقق من تعارض الأوقات في نفس اليوم
            foreach ($newSlots as $newSlot) {
                foreach ($existingSlots as $existingSlot) {
                    // التحقق من نفس اليوم
                    $newDay = $newSlot['day_number'] ?? null;
                    $existingDay = $existingSlot['day_number'] ?? null;
                    
                    if ($newDay === null || $existingDay === null) {
                        continue;
                    }
                    
                    if ($newDay === $existingDay) {
                        // الحصول على أوقات البداية والنهاية
                        $newStart = $newSlot['start'] ?? $newSlot['start_time'] ?? null;
                        $newEnd = $newSlot['end'] ?? $newSlot['end_time'] ?? null;
                        $existingStart = $existingSlot['start'] ?? $existingSlot['start_time'] ?? null;
                        $existingEnd = $existingSlot['end'] ?? $existingSlot['end_time'] ?? null;
                        
                        if (!$newStart || !$newEnd || !$existingStart || !$existingEnd) {
                            continue;
                        }
                        
                        // التحقق من تداخل الأوقات
                        if ($this->timesOverlap($newStart, $newEnd, $existingStart, $existingEnd)) {
                            $dayName = $this->getDayNameInArabic($newDay);
                            $conflictSchedule = $reservation->schedule;
                            return "يوم {$dayName} ({$newStart}-{$newEnd}) يتعارض مع حجزك في {$conflictSchedule->groupe}";
                        }
                    }
                }
            }
        }

        return null; // لا يوجد تعارض
    }

    /**
     * التحقق من تداخل فترتين زمنيتين
     * Check if two time ranges overlap
     */
    private function timesOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        $s1 = strtotime($start1);
        $e1 = strtotime($end1);
        $s2 = strtotime($start2);
        $e2 = strtotime($end2);

        // التداخل يحدث إذا:
        // بداية الفترة الأولى قبل نهاية الفترة الثانية
        // ونهاية الفترة الأولى بعد بداية الفترة الثانية
        return ($s1 < $e2) && ($e1 > $s2);
    }

    /**
     * الحصول على اسم اليوم بالعربية من رقم اليوم
     * Get Arabic day name from day number (0 = Sunday)
     */
    private function getDayNameInArabic(int $dayNumber): string
    {
        return match($dayNumber) {
            0 => 'الأحد',
            1 => 'الإثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
            default => 'غير معروف'
        };
    }

    public function destroy(Reservation $reservation)
{
    $reservation->delete();

    return redirect()->back()->with('success', 'تم حذف الحجز بنجاح');
}

}
