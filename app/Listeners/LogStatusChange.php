<?php

namespace App\Listeners;

use App\Events\TicketStatusChanged;

class LogStatusChange
{
    public function handle(TicketStatusChanged $event): void
    {
        $event->ticket->activityLogs()->create([
            'user_id' => $event->actor->id,
            'action' => 'status_changed',
            'old_data' => ['status' => $event->oldStatus],
            'new_data' => ['status' => $event->ticket->status],
        ]);
    }
}
