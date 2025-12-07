<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id','amount','methode','transaction_ref',
        'payment_status','paid_at'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
