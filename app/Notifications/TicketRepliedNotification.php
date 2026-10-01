<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class TicketRepliedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket, public TicketReply $reply) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'ticket_id' => $this->ticket->id,
            'reply_id' => $this->reply->id,
            'subject' => $this->ticket->subject,
            'message' => "New reply on Ticket #{$this->ticket->id} by {$this->reply->user->name}",
            'type' => 'replied',
        ]);
    }
}
