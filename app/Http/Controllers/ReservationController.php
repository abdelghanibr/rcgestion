<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complex;
use App\Models\Reservation;
use App\Models\Dossier;

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

    // 3) نموذج الحجز
   public function form($id)
{
    $complex = Complex::findOrFail($id);

    // نبحث عن أي دوسيي للمستخدم
    $dossier = Dossier::where('user_id', auth()->id())->first();

    // 1️⃣ لا يوجد أي دوسيي
    if (!$dossier) {
        return view('errors.error-dossier', [
            'message' => 'عذراً، لا يمكنك الحجز لأنه لا يوجد لديك ملف مُسجل. يرجى إنشاء ملف أولاً.'
        ]);
    }

    // 2️⃣ يوجد دوسيي لكنه غير Validé
    if ($dossier->etat !== 'Validé') {
        return view('errors.error-dossier', [
            'message' => 'تم العثور على ملفك، ولكن لم تتم المصادقة عليه بعد. يرجى انتظار الموافقة من الإدارة قبل إجراء أي حجز.'
        ]);
    }

    // 3️⃣ كل شيء جيد
    return view('reservations.form', compact('complex'));
}

    // 4) تنفيذ الحجز
    public function store(Request $request)
    {
        $request->validate([
            'complex_id' => 'required',
            'date_reservation' => 'required|date',
            'heure_debut' => 'required',
            'heure_fin' => 'required',
        ]);

        $complex = Complex::find($request->complex_id);

        $reservation = Reservation::create([
            'user_id' => auth()->id(),
            'complex_id' => $complex->id,
            'date_reservation' => $request->date_reservation,
            'heure_debut' => $request->heure_debut,
            'heure_fin' => $request->heure_fin,
            'montant' => $complex->prix,
            'statut' => 'En attente',
        ]);

        return redirect()->route('reservation.payment', $reservation->id);
    }

    // 8) حجوزات المستخدم
    public function myReservations()
    {
        $reservations = Reservation::where('user_id', auth()->id())->get();
        return view('reservation.my_reservations', compact('reservations'));
    }
}
