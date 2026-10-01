<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class TicketStatusChanged
{
    use Dispatchable;

    public function __construct(public Ticket $ticket, public string $oldStatus, public User $actor) {}
}
