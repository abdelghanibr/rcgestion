<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Person;
use App\Models\Dossier;
use App\Models\club;
use App\Models\Reservation;

class DashboardController extends Controller
{
    

public function index()
{
    $user = Auth::user();

    // ✅ لا نحذف متغير club (كما طلبت)
    $club = \App\Models\Club::where('user_id', auth()->id())->first();

    // ✅ دالة صغيرة لحساب إحصائيات الحجوزات + الدفع (حسب user_id)
    $buildReservationStats = function ($userId) {

        $base = Reservation::where('user_id', $userId);

        $totalReservations = (clone $base)->count();

        // ✅ payment_status: عدّل القيم حسب enum الحقيقي عندك
        $paidReservations    = (clone $base)->where('payment_status', 'paid')->count();
        $pendingPayments     = (clone $base)->where('payment_status', 'pending')->count();
        $unpaidReservations  = (clone $base)->where('payment_status', 'unpaid')->count();

        // ✅ statut: إن أردت إحصاء حالة الحجز
        $approvedReservations = (clone $base)->where('statut', 'approved')->count();
        $pendingReservations  = (clone $base)->where('statut', 'pending')->count();
        $rejectedReservations = (clone $base)->where('statut', 'rejected')->count();

        return [
            'totalReservations'      => $totalReservations,
            'paidReservations'       => $paidReservations,
            'pendingPayments'        => $pendingPayments,
            'unpaidReservations'     => $unpaidReservations,
            'approvedReservations'   => $approvedReservations,
            'pendingReservations'    => $pendingReservations,
            'rejectedReservations'   => $rejectedReservations,
        ];
    };

    /* ---------------------------------
    | 📌 Dashboard النادي
    --------------------------------- */
    if ($user->type === 'club') {

        $clubOwner = $user->id;

        // ✅ dossier (كما هو)
        $dossier = Club::where('user_id', $user->id)->first();

        $playersCount = Person::where('user_id', $clubOwner)->where('education', 'لاعب')->count();
        $coachsCount  = Person::where('user_id', $clubOwner)->where('education', 'مدرب')->count();
        $managersCount= Person::where('user_id', $clubOwner)->where('education', 'مسير')->count();

        // ✅ NEW: stats reservations + payment
        $reservationStats = $buildReservationStats($clubOwner);

        return view('club.dashboard', [
            'user' => $user,
            'playersCount' => $playersCount,
            'coachsCount' => $coachsCount,
            'managersCount' => $managersCount,
            'dossier' => $dossier,
            'club' => $club,

            // ✅ تمرير الإحصائيات الجديدة
            'totalReservations'    => $reservationStats['totalReservations'],
            'paidReservations'     => $reservationStats['paidReservations'],
            'pendingPayments'      => $reservationStats['pendingPayments'],
            'unpaidReservations'   => $reservationStats['unpaidReservations'],
            'approvedReservations' => $reservationStats['approvedReservations'],
            'pendingReservations'  => $reservationStats['pendingReservations'],
            'rejectedReservations' => $reservationStats['rejectedReservations'],
        ]);
    }

    /* ---------------------------------
    | 📌 Dashboard المؤسسة
    --------------------------------- */
    if ($user->type === 'company' || $user->type === 'entreprise') {

        $enterpriseOwner = $user->id;

        // ✅ dossier (كما هو عندك)
        $dossier = Club::where('user_id', $user->id)->first();

        $playersCount = Person::where('user_id', $enterpriseOwner)->where('education', 'لاعب')->count();
        $coachsCount  = Person::where('user_id', $enterpriseOwner)->where('education', 'مدرب')->count();
        $managersCount= Person::where('user_id', $enterpriseOwner)->where('education', 'مسير')->count();

        // ✅ NEW: stats reservations + payment
        $reservationStats = $buildReservationStats($enterpriseOwner);

        return view('entreprise.dashboard', [
            'playersCount' => $playersCount,
            'coachsCount' => $coachsCount,
            'managersCount' => $managersCount,
            'dossier' => $dossier,
            'club' => $club, // ✅ لا نحذفه

            // ✅ إحصائيات الحجوزات
            'totalReservations'    => $reservationStats['totalReservations'],
            'paidReservations'     => $reservationStats['paidReservations'],
            'pendingPayments'      => $reservationStats['pendingPayments'],
            'unpaidReservations'   => $reservationStats['unpaidReservations'],
            'approvedReservations' => $reservationStats['approvedReservations'],
            'pendingReservations'  => $reservationStats['pendingReservations'],
            'rejectedReservations' => $reservationStats['rejectedReservations'],
        ]);
    }

    /* ---------------------------------
    | 📌 Dashboard الشخص
    --------------------------------- */
    $person = \App\Models\Person::where('user_id', $user->id)->first();

    if ($person) {
        $dossier = \App\Models\Dossier::where('owner_type', 'person')
                                      ->where('person_id', $person->id)
                                      ->first();
    } else {
        $dossier = null; // ✅ حتى لا يقع خطأ
    }

    // ✅ متغيرك القديم
    $registeredActivities = DB::table('reservations')
                              ->where('user_id', $user->id)
                              ->count();

    // ✅ NEW: stats reservations + payment
    $reservationStats = $buildReservationStats($user->id);

   return view('person.dashboard', [
        'user'                   => $user,
        'dossier'                => $dossier,
        'registeredActivities'   => $registeredActivities,

        // ✅ stats
        'totalReservations'      => $reservationStats['totalReservations'],
        'paidReservations'       => $reservationStats['paidReservations'],
        'pendingPayments'        => $reservationStats['pendingPayments'],
        'unpaidReservations'     => $reservationStats['unpaidReservations'],
        'approvedReservations'   => $reservationStats['approvedReservations'],
        'pendingReservations'    => $reservationStats['pendingReservations'],
        'rejectedReservations'   => $reservationStats['rejectedReservations'],
    ]);
}

protected function buildReservationStats($userId)
{
    return [
        'totalReservations'    => Reservation::where('user_id', $userId)->count(),

        'paidReservations'     => Reservation::where('user_id', $userId)
                                              ->where('payment_status', 'paid')
                                              ->count(),

        'pendingPayments'      => Reservation::where('user_id', $userId)
                                              ->where('payment_status', 'pending')
                                              ->count(),

        'unpaidReservations'   => Reservation::where('user_id', $userId)
                                              ->where('payment_status', 'unpaid')
                                              ->count(),

        'approvedReservations' => Reservation::where('user_id', $userId)
                                              ->where('statut', 'approved')
                                              ->count(),

        'pendingReservations'  => Reservation::where('user_id', $userId)
                                              ->where('statut', 'pending')
                                              ->count(),

        'rejectedReservations' => Reservation::where('user_id', $userId)
                                              ->where('statut', 'rejected')
                                              ->count(),
    ];
}

    public function dashboard()
{
    return view('admin.dashboard', [
        'dossiersCount' => Dossier::count(),
        'clubsCount' => Club::count(),
        'personsCount' => Person::count()
    ]);
}


public function dashboardStats()
{
    $user = Auth::user();

    $query = Reservation::query();

    /*
    |--------------------------------------------------------------------------
    | 🔐 تصفية حسب نوع المستخدم
    |--------------------------------------------------------------------------
    */
    if ($user->type === 'club' || $user->type === 'company' || $user->type === 'entreprise') {
        // الحجوزات المرتبطة بالمؤسسة
        $query->where('user_id', $user->id);
    }

    if ($user->type === 'person') {
        // حجوزات الشخص فقط
        $query->where('user_id', $user->id);
    }

    /*
    |--------------------------------------------------------------------------
    | 📊 الإحصائيات
    |--------------------------------------------------------------------------
    */
    $stats = [
        'total'      => (clone $query)->count(),
        'paid'       => (clone $query)->where('payment_status', 'paid')->count(),
        'pending'    => (clone $query)->where('payment_status', 'pending')->count(),
        'cancelled'  => (clone $query)->where('payment_status', 'cancelled')->count(),
    ];

    return view('dashboard.index', compact('stats'));
}
}
