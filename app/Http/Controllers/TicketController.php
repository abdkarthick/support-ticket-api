<?php

namespace App\Http\Controllers;

use App\Events\TicketAssigned;
use App\Events\TicketCreated;
use App\Events\TicketStatusChanged;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SlaCalculatorService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function __construct(
        private SlaCalculatorService $slaService,
        private TicketService $ticketService
    ) {}

    public function index(Request $request)
    {
        $tickets = $this->ticketService->getFilteredTickets($request);

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->ticketService->createTicket($request->validated(), $request->user());

        $this->slaService->setDeadline($ticket);

        event(new TicketCreated($ticket));

        return new TicketResource($ticket->load(['user', 'assignee']));
    }

    public function show(Request $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $this->slaService->checkAndMarkBreach($ticket);

        return new TicketResource($ticket->load(['user', 'assignee', 'replies.user', 'activityLogs.user']));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        $oldStatus = $ticket->status;
        $ticket = $this->ticketService->updateTicket($ticket, $request->validated());

        if ($oldStatus !== $ticket->status) {
            event(new TicketStatusChanged($ticket, $oldStatus, $request->user()));
        }

        return new TicketResource($ticket->load(['user', 'assignee']));
    }

    public function assign(Request $request, Ticket $ticket)
    {
        Gate::authorize('assign', $ticket);

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $assignee = User::findOrFail($request->assigned_to);

        $ticket = $this->ticketService->assignTicket($ticket, $assignee);

        event(new TicketAssigned($ticket, $assignee));

        return new TicketResource($ticket->load(['user', 'assignee']));
    }

    public function destroy(Request $request, Ticket $ticket)
    {
        Gate::authorize('delete', $ticket);

        $this->ticketService->deleteTicket($ticket);

        return response()->json(['message' => 'Ticket deleted']);
    }
}
