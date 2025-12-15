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
use App\Models\club;

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

    $reservations = Reservation::with([
            'complexActivity.complex',
            'complexActivity.activity',
            'season'
        ])
        ->where('user_id', $user->id)
       
        ->get();

    $activities = Activity::orderBy('title')->get();
    $seasons    = Season::orderBy('name')->get();

    return view('reservation.my_reservations', compact(
        'reservations',
        'activities',
        'seasons'
    ));
}
     public function create()
    {
        return view('reservations.create');
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

    $pricingPlans = $this->eligiblePricingPlans(
        $complexActivity->activity_id,
        $user,
        $ageCategoryId,
        $genderCode
    );// جلب خطط التسعير المؤهلة

    $scheduleQuery = Schedule::with('ageCategory')
        ->where('complex_activity_id', $complexActivity->id);// جلب الجداول الزمنية للمركب والنشاط المحدد

    if ($ageCategoryId) {// التحقق من فئة العمر
        $scheduleQuery->where(function ($query) use ($ageCategoryId) {// شرط للتحقق من فئة العمر
            $query->whereNull('age_category_id')// إذا لم تكن هناك فئة عمر محددة
                  ->orWhere('age_category_id', $ageCategoryId);// التحقق من فئة العمر
        });
    }

    if ($genderCode) {// التحقق من الجنس
        $scheduleQuery->where(function ($query) use ($genderCode) {// شرط للتحقق من الجنس
            $query->whereNull('sex')// إذا لم يكن هناك جنس محدد
                  ->orWhere('sex', 'X')// إذا كان الجنس غير محدد (X)
                  ->orWhere('sex', $genderCode);// التحقق من الجنس
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

    return view('reservation.form', compact(
        'complex',
        'complexActivity',
        'seasons',
        'activity',
        'schedules'
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

    if ($schedule->complex_activity_id !== $complexActivity->id) {
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
        'admin' => redirect()->route('admin.dashboard'),
        'club'  => redirect()->route('club.dashboard'),
        'company' => redirect()->route('entreprise.dashboard'),
        default => redirect()->route('person.dashboard'),
    };

    return $redirect->with('success', '✔ تم تسجيل الحجز بنجاح وسيتم مراجعته من الإدارة.');
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

        $monthsBetween = $start->diffInMonths($end);// حساب عدد الشهور بين البداية والنهاية

        if ($end->day >= $start->day || $monthsBetween === 0) {// إذا كان يوم النهاية أكبر أو يساوي يوم البداية
            $monthsBetween += 1;// زيادة عدد الشهور بمقدار 1
        }

        $months = max(1, $monthsBetween);// التأكد من أن عدد الشهور لا يقل عن 1
        $monthsCharged = min(12, $months);// تحديد عدد الشهور التي سيتم احتسابها (بحد أقصى 12)

        return $basePrice * $monthsCharged;// حساب السعر النهائي بضرب السعر الأساسي في عدد الشهور المحتسبة
    }

    private function calculatePlanPrice(PricingPlan $plan, Season $season, int $sessionsPerWeek): float // حساب سعر الخطة
    {
        $start = Carbon::parse($season->date_debut);    // تاريخ بداية الموسم
        $end = Carbon::parse($season->date_fin);// تاريخ نهاية الموسم
        $unit = strtolower($plan->duration_unit ?? $plan->pricing_type ?? 'season');// وحدة التسعير
        $durationValue = max(1, (int) ($plan->duration_value ?? 1));// قيمة المدة
        $basePrice = (float) $plan->price;// السعر الأساسي للخطة

        $days = $start->diffInDays($end) + 1;// عدد الأيام في الموسم
        $weeks = max(1, (int) ceil($days / 7));// حساب الأسابيع
        $months = max(1, (int) ceil($days / 30));// حساب الشهور

        return match ($unit) { // تحديد السعر بناءً على وحدة التسعير
            'month', 'monthly' => $basePrice, // ceil($months / $durationValue) * $basePrice,// السعر مضروب في عدد الشهور مقسوم على قيمة المدة
            'week', 'weekly' => ceil($weeks / $durationValue) * $basePrice,// السعر مضروب في عدد الأسابيع مقسوم على قيمة المدة
            'session' => $weeks * min(1, $plan->sessions_per_week ?? $sessionsPerWeek) * $basePrice,// السعر مضروب في عدد الأسابيع وعدد الحصص في الأسبوع
            'ticket' => $basePrice,// سعر التذكرة ثابت
            default => $basePrice,// السعر الأساسي للخطة
        };
    }

    public function destroy(Reservation $reservation)
{
    $reservation->delete();

    return redirect()->back()->with('success', 'تم حذف الحجز بنجاح');
}

}
