<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Paiement;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PaymentController extends Controller
{
    // صفحة الدفع
    public function paymentPage($id)
    {
        $reservation = Reservation::with('complex')->findOrFail($id);
        return view('reservation.payment', compact('reservation'));
    }

    // دفع نقدًا
    public function payCash($id)
    {
        $res = Reservation::findOrFail($id);

        Paiement::create([
            'reservation_id' => $res->id,
            'montant' => $res->montant,
            'methode' => 'Sur place',
            'statut' => 'Réussi',
        ]);

        $res->statut = 'Payé';
        $res->save();

        return redirect()->route('reservation.my_reservations')
                         ->with('success', 'تم دفع المبلغ نقدًا.');
    }

    // دفع إلكتروني (مستقبلاً Stripe أو PayPal)
    public function payOnline($id)
    {
        $res = Reservation::findOrFail($id);

        // محاكاة دفع إلكتروني
        Paiement::create([
            'reservation_id' => $res->id,
            'montant' => $res->montant,
            'methode' => 'En ligne',
            'statut' => 'Réussi',
            'transaction_id' => 'TX-' . rand(100000, 999999),
        ]);

        $res->statut = 'Payé';
        $res->save();

        return redirect()->route('reservation.my_reservations')
                         ->with('success', 'تم الدفع الإلكتروني بنجاح.');
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
}
