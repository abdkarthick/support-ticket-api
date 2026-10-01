<?php

namespace App\Listeners;

use App\Events\TicketAssigned;
use App\Notifications\TicketAssignedNotification;

class SendTicketAssignedNotification
{
    public function handle(TicketAssigned $event): void
    {
        $event->assignee->notify(new TicketAssignedNotification($event->ticket));
    }
}
