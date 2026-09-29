<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreReplyRequest;
use App\Http\Resources\TicketReplyResource;
use App\Models\Ticket;
use Illuminate\Support\Facades\Gate;

class TicketReplyController extends Controller
{
    public function index(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);
        $replies = $ticket->replies()->with('user')->get();
        return TicketReplyResource::collection($replies);
    }

    public function store(StoreReplyRequest $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $reply = $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        return new TicketReplyResource($reply->load('user'));
    }
}