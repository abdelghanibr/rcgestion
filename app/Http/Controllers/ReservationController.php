<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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

    $activity = Activity::findOrFail($activity_id);

    $complexActivity = ComplexActivity::where('activity_id', $activity_id)
                    ->where('complex_id', $id)
                    ->firstOrFail();

    $pricingPlans = $this->eligiblePricingPlans($complexActivity->activity_id, $user);

    $schedules = Schedule::with('ageCategory')
        ->where('complex_activity_id', $complexActivity->id)
        ->get()
        ->map(function ($schedule) use ($pricingPlans) {
            $slots = is_array($schedule->time_slots) ? $schedule->time_slots : [];
            $sessionsCount = count($slots);

            $formattedSlots = collect($slots)->map(function ($slot) {
                $dayIndex = $slot['day_number'] ?? null;
                $dayLabel = $dayIndex !== null ? (self::DAY_LABELS[$dayIndex] ?? 'يوم غير معروف') : 'يوم غير معروف';

                $start = $slot['start'] ?? null;
                $end   = $slot['end'] ?? null;

                return trim($dayLabel . ' | ' . ($start ?? '?') . ' → ' . ($end ?? '?'));
            })->toArray();

            $schedule->sessions_count = $sessionsCount;
            $schedule->formatted_slots = $formattedSlots;

            if ($schedule->type_prix === 'pricing_plan') {
                $matchedPlan = $pricingPlans->first(function ($plan) use ($sessionsCount) {
                    return (int) $plan->sessions_per_week === (int) $sessionsCount;
                });

                $schedule->applied_plan = $matchedPlan;
                $schedule->calculated_price = $matchedPlan?->price;
            } else {
                $schedule->applied_plan = null;
                $schedule->calculated_price = $schedule->price;
            }

            return $schedule;
        });

    if ($schedules->isNotEmpty()) {
        $reservedCounts = Reservation::whereIn('schedule_id', $schedules->pluck('id'))
            ->selectRaw('schedule_id, SUM(qty_places) as reserved')
            ->groupBy('schedule_id')
            ->pluck('reserved', 'schedule_id');

        $schedules = $schedules->map(function ($schedule) use ($reservedCounts) {
            $reserved = (int) ($reservedCounts[$schedule->id] ?? 0);
            $schedule->reserved_places = $reserved;
            $schedule->available_places = $schedule->nbr ? max(0, $schedule->nbr - $reserved) : null;
            return $schedule;
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
        ->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1;

    // حساب السعر (مثال بسيط)
    $pricePerDay = $reservation->total_price /
                   max(1, $reservation->duration_hours);

    $newPrice = $days * $pricePerDay;

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

    $slots = is_array($schedule->time_slots) ? $schedule->time_slots : [];
    $sessionsPerWeek = count($slots);

    if ($schedule->nbr) {
        $reservedPlaces = Reservation::where('schedule_id', $schedule->id)->sum('qty_places');
        if ($reservedPlaces >= $schedule->nbr) {
            return back()->with('error', '⚠ هذا الجدول ممتلئ حالياً.');
        }
    }

    $pricingPlans = $this->eligiblePricingPlans($complexActivity->activity_id, $user);

    $appliedPlan = null;
    $totalPrice = null;

    if ($schedule->type_prix === 'pricing_plan') {
        $appliedPlan = $pricingPlans->first(function ($plan) use ($sessionsPerWeek) {
            return (int) $plan->sessions_per_week === (int) $sessionsPerWeek;
        });

        if (!$appliedPlan) {
            return back()->with('error', '⚠ لا توجد خطة تسعير مطابقة لهذا الجدول.');
        }

        $totalPrice = $appliedPlan->price;
    } else {
        if (!$schedule->price) {
            return back()->with('error', '⚠ لا يوجد سعر محدد لهذا الجدول.');
        }

        $totalPrice = $schedule->price;
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
        $reservations = Reservation::where('user_id', auth()->id())->get();
        return view('reservation.my_reservations', compact('reservations'));
    }

    private function eligiblePricingPlans($activityId, $user)
    {
        return PricingPlan::where('activity_id', $activityId)
            ->where('active', 1)
            ->whereDate('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_to')
                      ->orWhereDate('valid_to', '>=', now());
            })
            ->where(function ($query) use ($user) {
                if ($user->type === 'person') {
                    $query->where('type_client', 'person')
                          ->where('age_category_id', optional($user->person)->age_category_id);
                } else {
                    $query->where('type_client', 'club');
                }
            })
            ->get();
    }
}
