<?php

namespace App\Listeners;

use App\Events\TicketReplied;
use App\Notifications\TicketRepliedNotification;

class SendTicketRepliedNotification
{
    public function handle(TicketReplied $event): void
    {
        $ticket = $event->ticket;
        $replyAuthor = $event->reply->user;

        $usersToNotify = collect();

        // Notify ticket creator (if not author)
        if ($ticket->user_id !== $replyAuthor->id) {
            $usersToNotify->push($ticket->user);
        }

        // Notify assignee (if not author)
        if ($ticket->assigned_to && $ticket->assigned_to !== $replyAuthor->id) {
            $usersToNotify->push($ticket->assignee);
        }

        $usersToNotify->unique('id')->each(function ($user) use ($ticket, $event) {
            $user->notify(new TicketRepliedNotification($ticket, $event->reply));
        });
    }
}
