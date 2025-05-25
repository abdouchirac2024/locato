<?php

namespace App\Events;

use App\Models\Visite;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Visite $visite;

    public function __construct(Visite $visite)
    {
        $this->visite = $visite;
    }
}
