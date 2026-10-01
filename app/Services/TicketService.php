<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class TicketService
{
    public function getFilteredTickets(Request $request): LengthAwarePaginator
    {
        $query = Ticket::with(['user', 'assignee'])->withCount('replies')->latest();

        if (! $request->user()->isAgent()) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->has('assigned_to_me') && $request->user()->isAgent()) {
            $query->where('assigned_to', $request->user()->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('subject', 'like', '%'.$request->search.'%');
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        return $query->paginate(10);
    }

    public function createTicket(array $data, User $user): Ticket
    {
        return Ticket::create([
            'user_id' => $user->id,
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? 'normal',
            'category_id' => $data['category_id'] ?? null,
            'status' => 'open',
        ]);
    }

    public function updateTicket(Ticket $ticket, array $data): Ticket
    {
        $ticket->update($data);

        if (in_array($ticket->status, ['resolved', 'closed']) && ! $ticket->resolved_at) {
            $ticket->update(['resolved_at' => now()]);
        }

        return $ticket->fresh();
    }

    public function assignTicket(Ticket $ticket, User $assignee): Ticket
    {
        if (! $assignee->isAgent()) {
            abort(422, 'Can assign only to agent or admin');
        }

        $ticket->update(['assigned_to' => $assignee->id]);

        return $ticket->fresh();
    }

    public function deleteTicket(Ticket $ticket): void
    {
        $ticket->delete();
    }
}
