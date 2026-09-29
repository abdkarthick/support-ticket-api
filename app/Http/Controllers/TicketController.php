<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user'])->withCount('replies')->latest();

        // Role check: customer can see only own tickets
        if (!$request->user()->isAgent()) {
            $query->where('user_id', $request->user()->id);
        }

        // Filtering by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by subject
        if ($request->has('search')) {
            $query->where('subject', 'like', '%' . $request->search . '%');
        }

        // Filtering by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Pagination - 10 per page
        $tickets = $query->paginate(10);

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'status' => 'open',
        ]);

        return new TicketResource($ticket->load('user'));
    }

    public function show(Request $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);
        return new TicketResource($ticket->load(['user', 'replies.user']));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        Gate::authorize('update', $ticket);
        $ticket->update($request->validated());
        return new TicketResource($ticket->load('user'));
    }

    public function destroy(Request $request, Ticket $ticket)
    {
        Gate::authorize('delete', $ticket);
        $ticket->delete();
        return response()->json(['message' => 'Ticket deleted']);
    }
}