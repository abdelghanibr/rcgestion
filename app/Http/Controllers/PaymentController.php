<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Paiement;

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
}
