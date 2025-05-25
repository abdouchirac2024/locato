<?php

namespace App\Events;

use App\Models\Payement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentMade
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payement $payment;

    public function __construct(Payement $payment)
    {
        $this->payment = $payment;
    }
}
