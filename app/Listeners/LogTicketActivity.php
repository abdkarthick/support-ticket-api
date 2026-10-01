<?php

namespace App\Listeners;

use App\Events\TicketAssigned;
use App\Events\TicketCreated;
use App\Events\TicketReplied;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogTicketActivity
{
    public function handle(TicketCreated|TicketAssigned|TicketReplied $event): void
    {
        if ($event instanceof TicketCreated) {
            ActivityLog::create([
                'ticket_id' => $event->ticket->id,
                'user_id' => Auth::id() ?? $event->ticket->user_id,
                'action' => 'created',
                'new_value' => $event->ticket->subject,
                'new_data' => ['subject' => $event->ticket->subject],
            ]);
        }

        if ($event instanceof TicketAssigned) {
            ActivityLog::create([
                'ticket_id' => $event->ticket->id,
                'user_id' => Auth::id(),
                'action' => 'assigned',
                'new_value' => (string) $event->assignee->id,
                'new_data' => ['assigned_to' => $event->assignee->id],
            ]);
        }

        if ($event instanceof TicketReplied) {
            ActivityLog::create([
                'ticket_id' => $event->ticket->id,
                'user_id' => $event->reply->user_id,
                'action' => 'replied',
                'new_value' => (string) $event->reply->id,
                'new_data' => ['reply_id' => $event->reply->id],
            ]);
        }
    }
}
