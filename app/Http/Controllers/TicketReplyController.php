<?php

namespace App\Http\Controllers;

use App\Events\TicketReplied;
use App\Http\Requests\StoreReplyRequest;
use App\Http\Resources\TicketReplyResource;
use App\Models\Ticket;
use Illuminate\Support\Facades\Gate;

class TicketReplyController extends Controller
{
    public function index(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);
        $replies = $ticket->replies()->with('user')->latest()->get();

        return TicketReplyResource::collection($replies);
    }

    public function store(StoreReplyRequest $request, Ticket $ticket)
    {
        Gate::authorize('reply', $ticket);

        $reply = $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        // Auto change status to in_progress when agent replies
        if ($request->user()->isAgent() && $ticket->status === 'open') {
            $ticket->status = 'in_progress';
            $ticket->save();
        }

        // Fire Event - will handle activity log + notifications
        event(new TicketReplied($ticket, $reply));

        return new TicketReplyResource($reply->load('user'));
    }
}
